<?php namespace VaahCms\Modules\Appointment\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Faker\Factory;
use WebReinvent\VaahCms\Libraries\VaahMail;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use Carbon\Carbon;

class Appointment extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_appointments';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'doctor_id',
        'patient_id',
        'appointment_date',
        'appointment_time',
        'status',
        'reason_for_visit',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    //-------------------------------------------------
    protected $fill_except = [

    ];

    //-------------------------------------------------
    protected $appends = [
    ];

    //-------------------------------------------------
    protected function serializeDate(DateTimeInterface $date)
    {
        $date_time_format = config('settings.global.datetime_format');
        return $date->format($date_time_format);
    }

    //-------------------------------------------------
    public static function getUnFillableColumns()
    {
        return [
            'uuid',
            'created_by',
            'updated_by',
            'deleted_by',
        ];
    }
    //-------------------------------------------------
    public static function getFillableColumns()
    {
        $model = new self();
        $except = $model->fill_except;
        $fillable_columns = $model->getFillable();
        $fillable_columns = array_diff(
            $fillable_columns, $except
        );
        return $fillable_columns;
    }
    //-------------------------------------------------
    public static function getEmptyItem()
    {
        $model = new self();
        $fillable = $model->getFillable();
        $empty_item = [];
        foreach ($fillable as $column)
        {
            $empty_item[$column] = null;
        }

        $empty_item['is_active'] = 1;

        return $empty_item;
    }

    //-------------------------------------------------

    public function createdByUser()
    {
        return $this->belongsTo(User::class,
            'created_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function updatedByUser()
    {
        return $this->belongsTo(User::class,
            'updated_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function deletedByUser()
    {
        return $this->belongsTo(User::class,
            'deleted_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()
            ->getColumnListing($this->getTable());
    }

    //-------------------------------------------------
    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }


    //-------------------------------------------------
    public function scopeBetweenDates($query, $from, $to)
    {

        if ($from) {
            $from = \Carbon::parse($from)
                ->startOfDay()
                ->toDateTimeString();
        }

        if ($to) {
            $to = \Carbon::parse($to)
                ->endOfDay()
                ->toDateTimeString();
        }

        $query->whereBetween('updated_at', [$from, $to]);
    }

    //-------------------------------------------------

    // function to convert UTC to IST
    public static function convertToIST($inputs)
    {
        // Convert 'appointment_date' from UTC to Asia/Kolkata
        $appointmentDate = Carbon::parse($inputs['appointment_date'])
            ->setTimezone('Asia/Kolkata')
            ->format('M. d Y');

        // Convert 'appointment_time' from UTC to Asia/Kolkata
        $appointmentTime = Carbon::parse($inputs['appointment_time'])
            ->setTimezone('Asia/Kolkata')
            ->format('h:i A');

        // Concatenate both values
        return $appointmentDate . ', ' . $appointmentTime;
    }



    //-------------------------------------------------
    public static function createItem($request)
    {

        $inputs = $request->all();

        $inputs['appointment_date']= Carbon::parse($inputs['appointment_date'])->toDateString();  // Extract date part
        // Extract hour and minute part, ignoring seconds
        $inputs['appointment_time'] = Carbon::parse($inputs['appointment_time'])->format('H:i:00');  // Format as HH:MM
        $inputs['status'] = "confirmed";

        //------------------------------------------------------------
        // Compare if there is an existing booking at same time and date

            $inputAppointmentDate = Carbon::parse($inputs['appointment_date'])->toDateString();  // Extract date part
            $inputAppointmentTime = Carbon::parse($inputs['appointment_time'])->toTimeString();  // Extract time part

            // Fetch doctor's name with doctor_id
            $doctor = Doctor::find($inputs['doctor_id']);

            $existingAppointment = self::where('appointment_date', $inputAppointmentDate)
                ->where('appointment_time', $inputAppointmentTime)
                ->where('doctor_id', $inputs['doctor_id'])
                ->first();

            if ($existingAppointment) {
                $response['messages'][] = trans('Requested time Slot is not available with '.$doctor->name.'! Choose any other slot.');
                return $response;
            }
        //------------------------------------------------------------

        $item = new self();
        $item->fill($inputs);
        $item->save();


        //----------------------------------------------------------------
        //Calling Email to Notify Booking confirm
        $subject = 'Appointment Confirmed';
        $doctor = Doctor::find($inputs['doctor_id']);
        $patient = Patient::find($inputs['patient_id']);
        // Convert UTC data and time to IST
        $row_date_time = [
            'appointment_date' => $inputs['appointment_date'], // Example UTC input for date
            'appointment_time' =>  $inputs['appointment_time'], // Example UTC input for time
        ];

        $formatted_date_time = self::convertToIST($row_date_time);

        $email_content_for_patient = sprintf(
            "Dear %s,\n\nYour appointment with Dr. %s has been successfully booked.\nThe details of your appointment are as follows:\n\nAppointment Date & Time: %s\n\nPlease make sure to arrive 10 minutes before the scheduled time.\n\nRegards,\nWebreinvent Technologies",
            $patient->name,
            $doctor->name,
            $formatted_date_time
        );

        $email_content_for_doctor = sprintf(
            "Dear Dr. %s,\n\nYou have a new appointment scheduled with %s.\nThe details are as follows:\n\nAppointment Date & Time: %s\n\nPlease be on time for the appointment.\n\nRegards,\nWebreinvent Technologies",
            $doctor->name,
            $patient->name,
            $formatted_date_time
        );

        $doctor_email = $doctor->email;
        $patient_email = $patient->email;

        self::appointmentMail($email_content_for_patient,$email_content_for_doctor,$subject,$doctor_email,$patient_email);
        //-----------------------------------------------------------------

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;

    }



    //-------------------------------------------------
    public function scopeGetSorted($query, $filter)
    {

        if(!isset($filter['sort']))
        {
            return $query->orderBy('id', 'desc');
        }

        $sort = $filter['sort'];


        $direction = Str::contains($sort, ':');

        if(!$direction)
        {
            return $query->orderBy($sort, 'asc');
        }

        $sort = explode(':', $sort);

        return $query->orderBy($sort[0], $sort[1]);
    }
    //-------------------------------------------------
    public function scopeIsActiveFilter($query, $filter)
    {

        if(!isset($filter['is_active'])
            || is_null($filter['is_active'])
            || $filter['is_active'] === 'null'
        )
        {
            return $query;
        }
        $is_active = $filter['is_active'];

        if($is_active === 'true' || $is_active === true)
        {
            return $query->where('is_active', 1);
        } else{
            return $query->where(function ($q){
                $q->whereNull('is_active')
                    ->orWhere('is_active', 0);
            });
        }

    }
    //-------------------------------------------------
    public function scopeTrashedFilter($query, $filter)
    {

        if(!isset($filter['trashed']))
        {
            return $query;
        }
        $trashed = $filter['trashed'];

        if($trashed === 'include')
        {
            return $query->withTrashed();
        } else if($trashed === 'only'){
            return $query->onlyTrashed();
        }

    }
    //-------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if(!isset($filter['q']))
        {
            return $query;
        }
        $search_array = explode(' ',$filter['q']);
        foreach ($search_array as $search_item){
            $query->where(function ($q1) use ($search_item) {
                $q1->where('name', 'LIKE', '%' . $search_item . '%')
                    ->orWhere('slug', 'LIKE', '%' . $search_item . '%')
                    ->orWhere('id', 'LIKE', $search_item . '%');
            });
        }

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter);
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->with(['doctor', 'patient']);
        $rows = config('vaahcms.per_page');

        if($request->has('rows'))
        {
            $rows = $request->rows;
        }

        $list = $list->paginate($rows);

        $response['success'] = true;
        $response['data'] = $list;

        return $response;


    }

    //-------------------------------------------------
    public static function updateList($request)
    {

        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
        );

        $messages = array(
            'type.required' => trans("vaahcms-general.action_type_is_required"),
        );


        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        if(isset($inputs['items']))
        {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();
        }

        $items = self::whereIn('id', $items_id);

        switch ($inputs['type']) {
            case 'deactivate':
                $items->withTrashed()->where(['is_active' => 1])
                    ->update(['is_active' => null]);
                break;
            case 'activate':
                $items->withTrashed()->where(function ($q){
                    $q->where('is_active', 0)->orWhereNull('is_active');
                })->update(['is_active' => 1]);
                break;
            case 'trash':
                self::whereIn('id', $items_id)
                    ->get()->each->delete();
                break;
            case 'restore':
                self::whereIn('id', $items_id)->onlyTrashed()
                    ->get()->each->restore();
                break;
        }

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }

    //-------------------------------------------------
    public static function deleteList($request): array
    {
        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
            'items' => 'required',
        );

        $messages = array(
            'type.required' => trans("vaahcms-general.action_type_is_required"),
            'items.required' => trans("vaahcms-general.select_items"),
        );

        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        self::whereIn('id', $items_id)->forceDelete();

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }
    //-------------------------------------------------
     public static function listAction($request, $type): array
    {

        $list = self::query();

        if($request->has('filter')){
            $list->getSorted($request->filter);
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
        }

        switch ($type) {
            case 'activate-all':
                $list->withTrashed()->where(function ($q){
                    $q->where('is_active', 0)->orWhereNull('is_active');
                })->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                $list->withTrashed()->where(['is_active' => 1])
                    ->update(['is_active' => null]);
                break;
            case 'trash-all':
                $list->get()->each->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->get()
                    ->each->restore();
                break;
            case 'delete-all':
                $list->forceDelete();
                break;
            case 'create-100-records':
            case 'create-1000-records':
            case 'create-5000-records':
            case 'create-10000-records':

            if(!config('appointment.is_dev')){
                $response['success'] = false;
                $response['errors'][] = 'User is not in the development environment.';

                return $response;
            }

            preg_match('/-(.*?)-/', $type, $matches);

            if(count($matches) !== 2){
                break;
            }

            self::seedSampleItems($matches[1]);
            break;
        }

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }
    //-------------------------------------------------
    public static function getItem($id)
    {

        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }
        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }
    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $inputs = $request->all();

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);

        // Set the status to 'confirmed'
        $item->status = 'confirmed';

        $item->save();

        //----------------------------------------------------------------
        //Calling Email to Notify Booking confirm
        $subject = 'Appointment Rescheduled';
        $doctor = Doctor::find($inputs['doctor_id']);
        $patient = Patient::find($inputs['patient_id']);
        // Convert UTC data and time to IST
        $row_date_time = [
            'appointment_date' => $inputs['appointment_date'], // Example UTC input for date
            'appointment_time' =>  $inputs['appointment_time'], // Example UTC input for time
        ];

        $formatted_date_time = self::convertToIST($row_date_time);

        $email_content_for_patient = sprintf(
            "Dear %s,\n\nYour appointment with Dr. %s has been rescheduled.\nThe details of your appointment are as follows:\n\nAppointment Date & Time: %s\n\nPlease make sure to arrive 10 minutes before the scheduled time.\n\nRegards,\nWebreinvent Technologies",
            $patient->name,
            $doctor->name,
            $formatted_date_time
        );

        $email_content_for_doctor = sprintf(
            "Dear Dr. %s,\n\nYour Appointment has been with rescheduled %s.\nThe details are as follows:\n\nAppointment Date & Time: %s\n\nPlease be on time for the appointment.\n\nRegards,\nWebreinvent Technologies",
            $doctor->name,
            $patient->name,
            $formatted_date_time
        );

        $doctor_email = $doctor->email;
        $patient_email = $patient->email;

        self::appointmentMail($email_content_for_patient,$email_content_for_doctor,$subject,$doctor_email,$patient_email);
        //-----------------------------------------------------------------

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }
        $item->forceDelete();

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans("vaahcms-general.record_has_been_deleted");

        return $response;
    }
    //-------------------------------------------------
    public static function itemAction($request, $id, $type): array
    {

        switch($type)
        {
            case 'activate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => 1]);
                break;
            case 'deactivate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => null]);
                break;
            case 'trash':
                self::find($id)
                    ->delete();
                break;
            case 'cancel':
                self::find($id)
                    ->update(['status'=> 'cancelled']);

                // Fetch the data for the given $id
                $inputs = self::find($id);

                //----------------------------------------------------------------
                //Calling Email to Notify Booking confirm
                $subject = 'Appointment Cancelled';
                $doctor = Doctor::find($inputs['doctor_id']);
                $patient = Patient::find($inputs['patient_id']);
                // Convert UTC data and time to IST
                $row_date_time = [
                    'appointment_date' => $inputs['appointment_date'], // Example UTC input for date
                    'appointment_time' =>  $inputs['appointment_time'], // Example UTC input for time
                ];

                $formatted_date_time = self::convertToIST($row_date_time);

                // Email content for patient
                $email_content_for_patient = sprintf(
                    "Dear %s,\n\nWe want to inform you that you have successfully cancelled your appointment with Dr. %s, originally scheduled for %s.\n\nIf you wish to reschedule, please feel free to visit our website or contact our support team for assistance.\n\nThank you for your understanding.\n\nBest regards,\nWebreinvent Technologies",
                    $patient->name,
                    $doctor->name,
                    $formatted_date_time
                );

                // Email content for doctor
                $email_content_for_doctor = sprintf(
                    "Dear Dr. %s,\n\nWe would like to inform you that your appointment with patient %s has been cancelled. The appointment was originally scheduled for %s.\n\nPlease check your schedule for any necessary adjustments.\n\nThank you for your understanding.\n\nBest regards,\nWebreinvent Technologies",
                    $doctor->name,
                    $patient->name,
                    $formatted_date_time
                );

                $doctor_email = $doctor->email;
                $patient_email = $patient->email;

                self::appointmentMail($email_content_for_patient,$email_content_for_doctor,$subject,$doctor_email,$patient_email);
                //-----------------------------------------------------------------

                break;

            case 'req_to_reschedule':
                self::find($id)
                    ->update(['status'=> 'pending']);

                // Fetch the data for the given $id
                $inputs = self::find($id);

                //----------------------------------------------------------------
                //Calling Email to Notify Booking confirm
                $subject = 'Appointment Slot Cancelled';
                $doctor = Doctor::find($inputs['doctor_id']);
                $patient = Patient::find($inputs['patient_id']);
                // Convert UTC data and time to IST
                $row_date_time = [
                    'appointment_date' => $inputs['appointment_date'], // Example UTC input for date
                    'appointment_time' =>  $inputs['appointment_time'], // Example UTC input for time
                ];

                $formatted_date_time = self::convertToIST($row_date_time);

                $email_content_for_patient = sprintf(
                    "Dear %s,\n\nWe would like to inform you that due to unforeseen circumstances, your appointment slot with Dr. %s has been cancelled. We kindly request you to reschedule your appointment at the next available slot.\n\nYou can easily rebook by visiting our website or contacting our support team.\n\nThank you for your understanding.\n\nBest regards,\nWebreinvent Technologies",
                    $patient->name,
                    $doctor->name
                );


                $patient_email = $patient->email;

                $email_content_for_doctor = "";
                $doctor_email = "";

                self::appointmentMail($email_content_for_patient,$email_content_for_doctor,$subject,$doctor_email,$patient_email);
                //-----------------------------------------------------------------

                break;

            case 'restore':
                self::where('id', $id)
                    ->onlyTrashed()
                    ->first()->restore();
                break;
        }

        return self::getItem($id);
    }
    //-------------------------------------------------

    public static function validation($inputs)
    {

        $rules = array(
//            'name' => 'required|max:150',
//            'slug' => 'required|max:150',
        );

        $validator = \Validator::make($inputs, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $response['success'] = false;
            $response['errors'] = $messages->all();
            return $response;
        }

        $response['success'] = true;
        return $response;

    }

    //-------------------------------------------------
    public static function getActiveItems()
    {
        $item = self::where('is_active', 1)
            ->withTrashed()
            ->first();
        return $item;
    }

    //-------------------------------------------------
    //-------------------------------------------------
    public static function seedSampleItems($records=100)
    {

        $i = 0;

        while($i < $records)
        {
            $inputs = self::fillItem(false);

            $item =  new self();
            $item->fill($inputs);
            $item->save();

            $i++;

        }

    }


    //-------------------------------------------------
    public static function fillItem($is_response_return = true)
    {
        $request = new Request([
            'model_namespace' => self::class,
            'except' => self::getUnFillableColumns()
        ]);
        $fillable = VaahSeeder::fill($request);
        if(!$fillable['success']){
            return $fillable;
        }
        $inputs = $fillable['data']['fill'];

        $faker = Factory::create();

        /*
         * You can override the filled variables below this line.
         * You should also return relationship from here
         */

        if(!$is_response_return){
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }



    //  Relation with Doctor
        public function doctor()
        {
            return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
        }

    //  Relation with Patient
        public function patient()
        {
            return $this->belongsTo(Patient::class, 'patient_id', 'id');
        }

    // Single Function for all kind of emails for Doctor and Patient
    public static function appointmentMail($email_content_for_patient,$email_content_for_doctor,$subject,$doctor_email,$patient_email)
    {
        if ($email_content_for_patient !== "")
        {
            VaahMail::dispatchGenericMail($subject, $email_content_for_patient, $patient_email);
        }
        if ($email_content_for_doctor !== "")
        {
            VaahMail::dispatchGenericMail($subject, $email_content_for_doctor, $doctor_email);
        }
    }


    //-------------------------------------------------
    //-------------------------------------------------
    //-------------------------------------------------


}
