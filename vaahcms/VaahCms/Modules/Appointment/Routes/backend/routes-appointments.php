<?php

use VaahCms\Modules\Appointment\Http\Controllers\Backend\AppointmentsController;

Route::group(
    [
        'prefix' => 'backend/appointment/appointments',
        
        'middleware' => ['web', 'has.backend.access'],
        
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [AppointmentsController::class, 'getAssets'])
        ->name('vh.backend.appointment.appointments.assets');
    /**
     * Get List
     */
    Route::get('/', [AppointmentsController::class, 'getList'])
        ->name('vh.backend.appointment.appointments.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [AppointmentsController::class, 'updateList'])
        ->name('vh.backend.appointment.appointments.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [AppointmentsController::class, 'deleteList'])
        ->name('vh.backend.appointment.appointments.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [AppointmentsController::class, 'fillItem'])
        ->name('vh.backend.appointment.appointments.fill');

    /**
     * Create Item
     */
    Route::post('/', [AppointmentsController::class, 'createItem'])
        ->name('vh.backend.appointment.appointments.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [AppointmentsController::class, 'getItem'])
        ->name('vh.backend.appointment.appointments.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [AppointmentsController::class, 'updateItem'])
        ->name('vh.backend.appointment.appointments.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [AppointmentsController::class, 'deleteItem'])
        ->name('vh.backend.appointment.appointments.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [AppointmentsController::class, 'listAction'])
        ->name('vh.backend.appointment.appointments.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [AppointmentsController::class, 'itemAction'])
        ->name('vh.backend.appointment.appointments.item.action');

    //---------------------------------------------------------

});
