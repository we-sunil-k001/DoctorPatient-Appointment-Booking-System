<?php

use VaahCms\Modules\Appointment\Http\Controllers\Backend\doctorsController;

Route::group(
    [
        'prefix' => 'backend/appointment/doctors',

        'middleware' => ['web', 'has.backend.access'],

],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [doctorsController::class, 'getAssets'])
        ->name('vh.backend.appointment.doctors.assets');
    /**
     * Get Filter parameters
     */
    Route::get('/filter', [doctorsController::class, 'getDoctorFilterParameter']);

    /**
    /**
     * Get List
     */
    Route::get('/', [doctorsController::class, 'getList'])
        ->name('vh.backend.appointment.doctors.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [doctorsController::class, 'updateList'])
        ->name('vh.backend.appointment.doctors.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [doctorsController::class, 'deleteList'])
        ->name('vh.backend.appointment.doctors.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [doctorsController::class, 'fillItem'])
        ->name('vh.backend.appointment.doctors.fill');

    /**
     * Create Item
     */
    Route::post('/', [doctorsController::class, 'createItem'])
        ->name('vh.backend.appointment.doctors.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [doctorsController::class, 'getItem'])
        ->name('vh.backend.appointment.doctors.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [doctorsController::class, 'updateItem'])
        ->name('vh.backend.appointment.doctors.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [doctorsController::class, 'deleteItem'])
        ->name('vh.backend.appointment.doctors.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [doctorsController::class, 'listAction'])
        ->name('vh.backend.appointment.doctors.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [doctorsController::class, 'itemAction'])
        ->name('vh.backend.appointment.doctors.item.action');

    //---------------------------------------------------------

});
