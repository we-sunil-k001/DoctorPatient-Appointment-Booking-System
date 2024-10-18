<?php
namespace App\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use VaahCms\Modules\Appointment\Models\Appointment;
use VaahCms\Modules\Appointment\Models\Doctor;
use Carbon\Carbon;
use VaahCms\Modules\Appointment\Models\Patient;
class ExportAppointments implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            "Patient's Name",
            "Patient's Email",
            "Doctor's Name",
            "Doctor's Email",
            'Appointment Date',
            'Appointment Time',
            'Medical Concern',

        ];
    }
    public function collection()
    {
        return Appointment::whereNull('deleted_at') // Exclude trashed records
        ->get()
            ->map(function ($appointment) {
                $doctor = Doctor::find($appointment['doctor_id']);
                $patient = Patient::find($appointment['patient_id']);

                return [
                    'Patient Name' =>  $patient['name'] ?? 'N/A',
                    'Patient Email' =>  $patient['email'] ?? 'N/A',
                    'Doctor Name' => $doctor['name'] ?? 'N/A',
                    'Doctor Email' => $doctor['email'] ?? 'N/A',
                    'Appointment Date' => Carbon::parse(self::convertDateUTCtoIST($appointment->appointment_date))->format('Y-m-d'),
                    'Appointment Time' => Carbon::parse(self::convertUTCtoIST12Hrs($appointment->appointment_time))->format('h:i A'),
                    'Reason for Visit' => $appointment->reason_for_visit,

                ];
            });
    }

    //-------------------------------------------------
    public static function convertDateUTCtoIST($utc_date_time)
    {
        // Assuming $utcDateTime is in 'Y-m-d H:i:s' format
        return Carbon::parse($utc_date_time, 'UTC')
            ->setTimezone('Asia/Kolkata')
            ->addDay()    // Add one day
            ->format('Y-m-d');
    }

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

}
