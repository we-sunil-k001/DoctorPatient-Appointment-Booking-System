<?php
use VaahCms\Modules\Appointment\Http\Controllers\Backend\AppointmentsController;
/*
 * API url will be: <base-url>/public/api/appointment/appointments
 */
Route::group(
    [
        'prefix' => 'appointment/appointments',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', [AppointmentsController::class, 'getAssets'])
        ->name('vh.backend.appointment.api.appointments.assets');
    /**
     * Get List
     */
    Route::get('/', [AppointmentsController::class, 'getList'])
        ->name('vh.backend.appointment.api.appointments.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [AppointmentsController::class, 'updateList'])
        ->name('vh.backend.appointment.api.appointments.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [AppointmentsController::class, 'deleteList'])
        ->name('vh.backend.appointment.api.appointments.list.delete');


    /**
     * Create Item
     */
    Route::post('/', [AppointmentsController::class, 'createItem'])
        ->name('vh.backend.appointment.api.appointments.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [AppointmentsController::class, 'getItem'])
        ->name('vh.backend.appointment.api.appointments.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [AppointmentsController::class, 'updateItem'])
        ->name('vh.backend.appointment.api.appointments.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [AppointmentsController::class, 'deleteItem'])
        ->name('vh.backend.appointment.api.appointments.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [AppointmentsController::class, 'listAction'])
        ->name('vh.backend.appointment.api.appointments.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [AppointmentsController::class, 'itemAction'])
        ->name('vh.backend.appointment.api.appointments.item.action');



});
