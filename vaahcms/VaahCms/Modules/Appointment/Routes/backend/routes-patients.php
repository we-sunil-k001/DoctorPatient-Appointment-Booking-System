<?php

use VaahCms\Modules\Appointment\Http\Controllers\Backend\PatientsController;

Route::group(
    [
        'prefix' => 'backend/appointment/patients',
        
        'middleware' => ['web', 'has.backend.access'],
        
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [PatientsController::class, 'getAssets'])
        ->name('vh.backend.appointment.patients.assets');
    /**
     * Get List
     */
    Route::get('/', [PatientsController::class, 'getList'])
        ->name('vh.backend.appointment.patients.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [PatientsController::class, 'updateList'])
        ->name('vh.backend.appointment.patients.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [PatientsController::class, 'deleteList'])
        ->name('vh.backend.appointment.patients.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [PatientsController::class, 'fillItem'])
        ->name('vh.backend.appointment.patients.fill');

    /**
     * Create Item
     */
    Route::post('/', [PatientsController::class, 'createItem'])
        ->name('vh.backend.appointment.patients.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [PatientsController::class, 'getItem'])
        ->name('vh.backend.appointment.patients.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [PatientsController::class, 'updateItem'])
        ->name('vh.backend.appointment.patients.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [PatientsController::class, 'deleteItem'])
        ->name('vh.backend.appointment.patients.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [PatientsController::class, 'listAction'])
        ->name('vh.backend.appointment.patients.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [PatientsController::class, 'itemAction'])
        ->name('vh.backend.appointment.patients.item.action');

    //---------------------------------------------------------

});
