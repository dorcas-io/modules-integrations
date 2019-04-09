<?php

Route::group(['namespace' => 'Dorcas\ModulesIntegrations\Http\Controllers', 'middleware' => ['web']], function() {
    Route::get('sales', 'ModulesIntegrationsController@index')->name('sales');
});


Route::group(['middleware' => ['auth'], 'namespace' => 'Integrations'], function () {
    Route::get('/integrations', 'Integrations@index')->name('integrations');
    Route::get('/integrations/install', 'Install@index')->name('integrations.install');
});

?>