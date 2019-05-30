<?php

Route::group(['namespace' => 'Dorcas\ModulesIntegrations\Http\Controllers', 'middleware' => ['web', 'auth'], 'prefix' => 'mit'], function() {
    Route::get('integrations-main', 'ModulesIntegrationsController@index')->name('integrations-main');
    Route::delete('integrations/{id}', 'ModulesIntegrationsController@uninstall');
    Route::post('integrations', 'ModulesIntegrationsController@install');
    Route::get('integrations-oauth-callback/{integration}', 'ModulesIntegrationsController@oauth_callback');
    //Route::get('webhook', 'ModulesIntegrationsController@webhooks');
});


/*Route::group(['middleware' => ['auth'], 'namespace' => 'Integrations'], function () {
    Route::get('/integrations', 'Integrations@index')->name('integrations');
    Route::get('/integrations/install', 'Install@index')->name('integrations.install');
});
    Route::post('/integrations', 'Integrations\Integrations@install');
    Route::delete('/integrations/{id}', 'Integrations\Integrations@uninstall');
    Route::put('/integrations/{id}', 'Integrations\Integrations@update');
*/
Route::group(['namespace' => 'Dorcas\ModulesIntegrations\Http\Controllers', 'middleware' => ['web'], 'prefix' => 'mit'], function() {
    Route::get('webhook', 'ModulesIntegrationsController@webhooks');
});

?>