<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhAppointments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_appointments')) {
            Schema::create('vh_appointments', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();

                // Foreign keys for doctor and patient
                $table->unsignedBigInteger('doctor_id'); // Ensure this is unsignedBigInteger
                $table->unsignedBigInteger('patient_id'); // Ensure this is also unsignedBigInteger

                // Appointment details
                $table->date('appointment_date'); // Date of the appointment
                $table->time('appointment_time'); // Time of the appointment
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending'); // Status of the appointment
                $table->text('status_change_reason')->nullable();
                $table->text('reason_for_visit')->nullable();

                $table->boolean('is_active')->nullable()->index();


                //----common fields
                $table->text('meta')->nullable();
                $table->bigInteger('created_by')->nullable()->index();
                $table->bigInteger('updated_by')->nullable()->index();
                $table->bigInteger('deleted_by')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['created_at', 'updated_at', 'deleted_at']);
                //----/common fields

            });
        }

    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists('vh_appointments');
    }
}
