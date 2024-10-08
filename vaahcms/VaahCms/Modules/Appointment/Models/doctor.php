<?php namespace VaahCms\Modules\Appointment\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Faker\Factory;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use Carbon\Carbon;
use WebReinvent\VaahCms\Libraries\VaahMail;


class doctor extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_doctors';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'email',
        'phone_number',
        'specialization',
        'qualification',
        'working_hours_end',
        'working_hours_start',
        'experience',
        'gender',
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
    public static function createItem($request)
    {

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // Validate working hours
        $validatedTime = $request->validate([
            'working_hours_start' => 'required',
            'working_hours_end' => [
                'required',
                'after:working_hours_start', // This ensures that working_hours_end is after working_hours_start
            ],
        ], [
            'working_hours_end.after' => 'The working hours end must be after the start time.',
        ]);


        // Extract hour and minute part, ignoring seconds
        $inputs['working_hours_start'] = Carbon::parse($inputs['working_hours_start'])->format('H:i:00');  // Format as HH:MM
        $inputs['working_hours_end'] = Carbon::parse($inputs['working_hours_end'])->format('H:i:00');  // Format as HH:MM


        // check if email exist
        $item = self::where('email', $inputs['email'])->withTrashed()->first();

        if ($item) {
            $error_message = "There is an existing Doctor with this email!".($item->deleted_at?' in trash.':'.');
            $response['success'] = false;
            $response['errors'][] = $error_message;
            return $response;

        }

        // check if phone number exist
        $item = self::where('phone_number', $inputs['phone_number'])->withTrashed()->first();

        if ($item) {
            $error_message = "There is an existing Doctor with this Phone number!".($item->deleted_at?' in trash.':'.');
            $response['success'] = false;
            $response['errors'][] = $error_message;
            return $response;
        }

        $item = new self();
        $item->fill($inputs);
        $item->save();

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
                    ->orWhere('email', 'LIKE', '%' . $search_item . '%')
                    ->orWhere('phone_number', 'LIKE', $search_item . '%')
                    ->orWhere('specialization', 'LIKE', $search_item . '%')
                ;
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

        // Select specific columns from the database
        $list = $list->select('id','name', 'email', 'phone_number', 'specialization','working_hours_start','working_hours_end',
        'updated_at','is_active')
        ->withTrashed();


        $rows = config('vaahcms.per_page');

        if($request->has('rows'))
        {
            $rows = $request->rows;
        }

        $list = $list->paginate($rows);

        // Loop through the list and convert working hours to IST
        foreach ($list as $item) {
            $item->working_hours_start = self::convertUTCtoIST12Hrs($item->working_hours_start);
            $item->working_hours_end = self::convertUTCtoIST12Hrs($item->working_hours_end);
        }

        $response['success'] = true;
        $response['data'] = $list;

        return $response;

    }

    //-------------------------------------------------
    // Helper function to convert time from UTC to IST and return in 12-hour format
    public static function convertUTCtoIST12Hrs($time)
    {
        if (!$time) {
            return null;
        }

        // Create a Carbon instance in UTC timezone
        $utc_time = Carbon::createFromTimeString($time, 'UTC');

        // Convert to Asia/Kolkata timezone
        $ist_time = $utc_time->setTimezone('Asia/Kolkata');

        // Return the formatted time in 'h:i A' (12-hour format with AM/PM)
        return $ist_time->format('h:i A');
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

            foreach ($items_id as $item_id) {

                    $appointments = Appointment::where('doctor_id', $item_id)->get();

                    foreach ($appointments as $appointment) {

                        //update status with "Pending"-----------------------------------
                        Appointment::where('id', $appointment->id)
                            ->update(['status' => 'cancelled']);

                        //----------------------------------------------------------------
                        //Calling Email to Notify Booking confirm
                        $subject = 'Appointment Cancelled';
                        $doctor = Doctor::find($appointment->doctor_id);
                        $patient = Patient::find($appointment->patient_id);
                        // Convert UTC data and time to IST
                        $row_date_time = [
                            'appointment_date' => $appointment->appointment_date, // Example UTC input for date
                            'appointment_time' => $appointment->appointment_time, // Example UTC input for time
                        ];

                        $formatted_date_time = self::convertToISTEmailFormat($row_date_time);

                        $email_content_for_patient = sprintf(
                            "Dear %s,<br><br>We would like to inform you that due to unforeseen circumstances, your appointment with Dr. %s on %s has been cancelled.<br>
                            You can easily schedule a new appointment by clicking the button below:<br><br>
                            <a href='%s' style='display:inline-block; padding:10px 15px; font-size:14px; color:#fff; background-color:#3b82f6; text-decoration:none; border-radius:5px;'>
                                Schedule New Appointment
                            </a><br><br>
                            Thank you for your understanding.<br><br>Best regards,<br>Webreinvent Technologies",
                            $patient->name,
                            $doctor->name,
                            $formatted_date_time,
                            vh_get_assets_base_url().'/backend/appointments#/appointments' // URL for the button
                        );


                        $patient_email = $patient->email;

                        $email_content_for_doctor = "";
                        $doctor_email = "";

                        self::appointmentMail($email_content_for_patient, $email_content_for_doctor, $subject, $doctor_email, $patient_email);
                        //-----------------------------------------------------------------
                    }
            }

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

        // Convert working hours from UTC to IST
        $item->working_hours_start = self::convertUTCtoIST12Hrs($item->working_hours_start);
        $item->working_hours_end = self::convertUTCtoIST12Hrs($item->working_hours_end);

        $response['success'] = true;
        $response['data'] = $item;


        return $response;

    }



    //-------------------------------------------------

    // function to convert UTC to IST in M. d Y - h:i A (Alphabatically written)
    public static function convertToISTEmailFormat($inputs)
    {
        // Convert 'appointment_date' from UTC to Asia/Kolkata
        $appointmentDate = Carbon::parse($inputs['appointment_date'])
            ->setTimezone('Asia/Kolkata')
            ->addDay()
            ->format('M. d Y');

        // Convert 'appointment_time' from UTC to Asia/Kolkata
        $appointmentTime = Carbon::parse($inputs['appointment_time'])
            ->setTimezone('Asia/Kolkata')
            ->format('h:i A');

        // Concatenate both values
        return $appointmentDate . ', ' . $appointmentTime;
    }




    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // Validate working hours
        $validatedTime = $request->validate([
            'working_hours_start' => 'required',
            'working_hours_end' => [
                'required',
                'after:working_hours_start', // This ensures that working_hours_end is after working_hours_start
            ],
        ], [
            'working_hours_end.after' => 'The working hours end must be after the start time.',
        ]);

        // Extract hour and minute part, ignoring seconds
        $inputs['working_hours_start'] = Carbon::parse($inputs['working_hours_start'])->format('H:i:00');  // Format as HH:MM
        $inputs['working_hours_end'] = Carbon::parse($inputs['working_hours_end'])->format('H:i:00');  // Format as HH:MM

        //-----------------------------------------------------------------
        // Compare new working hours with existing---------

            //Fetch existing working hours and convert in IST
            $item = self::where('id', $id)->withTrashed()->first();
            $existing_working_hours_start = Carbon::parse($item->working_hours_start)->setTimezone('Asia/Kolkata')->format('H:i:00');
            $existing_working_hours_end = Carbon::parse($item->working_hours_end)->setTimezone('Asia/Kolkata')->format('H:i:00');

            //New Working hours in IST
            $new_working_hours_start = Carbon::parse($inputs['working_hours_start'])->setTimezone('Asia/Kolkata')->format('H:i:00');
            $new_working_hours_end = Carbon::parse($inputs['working_hours_end'])->setTimezone('Asia/Kolkata')->format('H:i:00');

            // Check if the working hours have changed
            $working_hours_changed = (
                $new_working_hours_start !== $existing_working_hours_start ||
                $new_working_hours_end !== $existing_working_hours_end
            );

            if ($working_hours_changed) {

                // Fetch appointments that are outside the new working hours
                $appointments = Appointment::where('doctor_id', $id)->get();

                foreach ($appointments as $appointment) {

                    $appointment_time = Carbon::parse($appointment->appointment_time)->setTimezone('Asia/Kolkata')->format('H:i:00');

                    if ($appointment_time < $new_working_hours_start || $appointment_time > $new_working_hours_end){

                        //update status with "Pending"-----------------------------------
                        Appointment::where('id', $appointment->id)
                            ->update(['status' => 'pending']);

                        //----------------------------------------------------------------
                        //Calling Email to Notify Booking confirm
                        $subject = 'Appointment Slot Cancelled';
                        $doctor = Doctor::find($appointment->doctor_id);
                        $patient = Patient::find($appointment->patient_id);
                        // Convert UTC data and time to IST
                        $row_date_time = [
                            'appointment_date' => $appointment->appointment_date, // Example UTC input for date
                            'appointment_time' =>  $appointment->appointment_time, // Example UTC input for time
                        ];

                        $formatted_date_time = self::convertToISTEmailFormat($row_date_time);

                        $email_content_for_patient = sprintf(
                            "Dear %s,<br><br>We would like to inform you that due to unforeseen circumstances, your appointment slot with Dr. %s on %s has been cancelled. We kindly request you to reschedule your appointment at the next available slot.<br><br>
                            You can easily rebook by clicking the button below or by visiting:<br><br>
                            <a href='%s' style='display:inline-block; padding:10px 15px; font-size:14px; color:#fff; background-color:#3b82f6; text-decoration:none; border-radius:5px;'>
                                Reschedule Your Appointment
                            </a><br><br>
                            Thank you for your understanding.<br><br>Best regards,<br>Webreinvent Technologies",
                            $patient->name,
                            $doctor->name,
                            $formatted_date_time,
                            vh_get_assets_base_url().'/backend/appointments#/appointments' // URL for the button
                        );

                        $patient_email = $patient->email;

                        $email_content_for_doctor = "";
                        $doctor_email = "";

                        self::appointmentMail($email_content_for_patient,$email_content_for_doctor,$subject,$doctor_email,$patient_email);
                        //-----------------------------------------------------------------

                    }

                }

            }

        // check if name exist
        $item = self::where('id', '!=', $id)
            ->withTrashed()
            ->where('name', $inputs['name'])->first();

         if ($item) {
             $error_message = "This name is already exist".($item->deleted_at?' in trash.':'.');
             $response['success'] = false;
             $response['errors'][] = $error_message;
             return $response;
         }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->save();


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

                $appointments = Appointment::where('doctor_id', $id)->get();

                    foreach ($appointments as $appointment) {

                        //update status with "Pending"-----------------------------------
                            Appointment::where('id', $appointment->id)
                                ->update(['status' => 'cancelled']);

                            //----------------------------------------------------------------
                            //Calling Email to Notify Booking confirm
                            $subject = 'Appointment Cancelled';
                            $doctor = Doctor::find($appointment->doctor_id);
                            $patient = Patient::find($appointment->patient_id);
                            // Convert UTC data and time to IST
                            $row_date_time = [
                                'appointment_date' => $appointment->appointment_date, // Example UTC input for date
                                'appointment_time' =>  $appointment->appointment_time, // Example UTC input for time
                            ];

                            $formatted_date_time = self::convertToISTEmailFormat($row_date_time);

                        $email_content_for_patient = sprintf(
                            "Dear %s,<br><br>We would like to inform you that due to unforeseen circumstances, your appointment with Dr. %s on %s has been cancelled.<br><br>
                            You can easily schedule a new appointment by clicking the button below:<br><br>
                            <a href='%s' style='display:inline-block; padding:10px 15px; font-size:14px; color:#fff; background-color:#3b82f6; text-decoration:none; border-radius:5px;'>
                                Schedule New Appointment
                            </a><br><br>
                            Thank you for your understanding.<br><br>Best regards,<br>Webreinvent Technologies",
                            $patient->name,
                            $doctor->name,
                            $formatted_date_time,
                            vh_get_assets_base_url().'/backend/appointments#/appointments' // URL for the button
                        );

                        $patient_email = $patient->email;

                            $email_content_for_doctor = "";
                            $doctor_email = "";

                            self::appointmentMail($email_content_for_patient,$email_content_for_doctor,$subject,$doctor_email,$patient_email);
                            //-----------------------------------------------------------------
                    }
                self::find($id)
                    ->delete();
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
            'name' => 'required|max:150',
            'email' => 'required|email',
            'phone_number' => 'required|digits:10',
            'specialization' => 'required|max:100',
            'working_hours_start' => 'required',
            'working_hours_end' => 'required'

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

    //-------------------------------------------------
    //-------------------------------------------------
    //-------------------------------------------------


    // Relation with Appointments
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'id');
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



}
