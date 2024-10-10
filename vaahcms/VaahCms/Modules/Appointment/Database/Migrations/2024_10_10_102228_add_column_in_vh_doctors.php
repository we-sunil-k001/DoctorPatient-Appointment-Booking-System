<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInVhDoctors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_doctors', function (Blueprint $table) {
            // Add new columns
            $table->integer('no_of_slot')->nullable()->after('working_hours_end'); // Adjust the 'after' to place it correctly
            $table->integer('charges')->nullable()->after('working_hours_end');

            // If there's a specific index needed, you can add it like this:
            // $table->index(['no_of_slot', 'charges']);
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists('add_column_in_vh_doctors');
    }
}
