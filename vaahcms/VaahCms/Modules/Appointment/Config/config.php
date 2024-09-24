<?php

return [
    "name"=> "Appointment",
    "title"=> "Appointment",
    "slug"=> "appointment",
    "thumbnail"=> "https://img.site/p/300/160",
    "is_dev" => env('MODULE_APPOINTMENT_ENV')?true:false,
    "excerpt"=> "DoctorPatient-Appointment-Booking-System",
    "description"=> "DoctorPatient-Appointment-Booking-System",
    "download_link"=> "",
    "author_name"=> "vaah",
    "author_website"=> "https://vaah.dev",
    "version"=> "0.0.1",
    "is_migratable"=> true,
    "is_sample_data_available"=> true,
    "db_table_prefix"=> "vh_appointment_",
    "providers"=> [
        "\\VaahCms\\Modules\\Appointment\\Providers\\AppointmentServiceProvider"
    ],
    "aside-menu-order"=> null
];
