<?php

use Evavel\Enums\Context;

$routes_application = apply_filters('evavel_routes_application', []);

// Used for passing config parameters when using Vite devtools (npm run dev)
//if (EVAVEL_USE_VITE_DEVTOOLS){
//    $routes[] = evavel_route_get('/devtools/config', 'DevToolsController@config');
//}

/**
 * List of routes to be registered in WP
 */
return array_merge($routes_application, [

	evavel_route_get('/application/config', 'ApplicationController@handle' ),

	// Application Settings simple
	evavel_route_get('/application/settings', 'SettingsController@handle'),
	evavel_route_post('/application/settings', 'SettingsController@store', Context::UPDATE),

	// User settings
	evavel_route_get('/user/settings', 'UserSettingsController@handle'),
	evavel_route_post('/user/settings', 'UserSettingsController@store', Context::UPDATE),


	// APP SETTINGS complex
	//---------------------------------------

	evavel_route_get('/app/heartbeat', 'AppHeartBeatController@handle'),

	// Get the main data of the setting
	evavel_route_get('/app/settings/:settingName/config', 'AppSettingsController@config'),
	evavel_route_get('/app/settings/:settingName/items', 'AppSettingsController@items'),

	// Preview email template
	evavel_route_get('/app/settings/:settingName/:settingId/preview/:lang/:attribute', 'AppSettingsController@preview', \Evavel\Enums\Context::DETAIL),

	// Used for get a list of widget forms
	evavel_route_get('/app/settings/:settingName/list', 'AppSettingsController@listing'),

	// Create new item
	evavel_route_get('/app/settings/:settingName/new', 'AppSettingsController@create', \Evavel\Enums\Context::CREATE),

	// Ordering
	evavel_route_post('/app/settings/:settingName/ordering', 'AppSettingsController@ordering', \Evavel\Enums\Context::UPDATE),

	// Delete one item
	evavel_route_post('/app/settings/:settingName/:settingId/delete', 'AppSettingsController@delete', \Evavel\Enums\Context::DELETE),

	// Duplicate
	evavel_route_post('/app/settings/:settingName/:settingId/duplicate', 'AppSettingsController@duplicate', \Evavel\Enums\Context::UPDATE),

	// Get one item
	evavel_route_get('/app/settings/:settingName/:settingId', 'AppSettingsController@get', \Evavel\Enums\Context::DETAIL),

	// Save one item
	evavel_route_post('/app/settings/:settingName/:settingId', 'AppSettingsController@update', \Evavel\Enums\Context::UPDATE_OR_CREATE),

	// Delete one item
	evavel_route_delete('/app/settings/:settingName/:settingId', 'AppSettingsController@delete', \Evavel\Enums\Context::DELETE),


	// LOG VIEW
	evavel_route_get('/app/log/files', 'LogController@index', \Evavel\Enums\Context::INDEX),
	evavel_route_get('/app/log/file/download', 'LogController@getDownloadUrl', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/log/option/:option', 'LogController@toggleOption', \Evavel\Enums\Context::UPDATE_OR_CREATE),

	// Bulk resource update
	//---------------------------------------

	evavel_route_post('/bulk/:resourceName/delete', 'ResourceDestroyBulkController@handle', Context::DELETE),

	evavel_route_post('/bulk/:resourceName', 'ResourceUpdateBulkController@handle', Context::UPDATE),
	evavel_route_delete('/bulk/:resourceName', 'ResourceDestroyBulkController@handle', Context::DELETE),


	// Tenant resource settings
    evavel_route_get('/:resourceName/settings', 'ResourceSettingsController@handle'),
	evavel_route_post('/:resourceName/settings', 'ResourceSettingsController@store', Context::UPDATE),

	// Resource fields
    evavel_route_get('/:resourceName/:resourceId/update-fields', 'ResourceUpdateFieldsController@index', Context::UPDATE),
    evavel_route_get('/:resourceName/creation-fields', 'ResourceCreationFieldsController@index', Context::CREATE),

    // Lenses
	evavel_route_get('/:resourceName/lens/:lens/filters', 'LensFilterController@index'),
	evavel_route_get('/:resourceName/lens/:lens/actions', 'LensActionController@index'),
	evavel_route_get('/:resourceName/lens/:lens', 'LensIndexController@handle'),
	evavel_route_get('/:resourceName/lenses', 'LensIndexController@index'),

	// Get resource filters
	evavel_route_get('/:resourceName/filters', 'FilterController@index'),

    // Resource Actions
    evavel_route_get('/:resourceName/actions', 'ActionController@index'),
    evavel_route_post('/:resourceName/actions', 'ActionController@store', Context::CREATE),

	// Resource delete
	evavel_route_post('/:resourceName/delete', 'ResourceDestroyController@handle', Context::DELETE),

    // Associate resources
    evavel_route_get('/:resourceName/associatable/:field', 'AssociatableController@index', Context::UPDATE_OR_CREATE),

	// Resource detail
    evavel_route_get('/:resourceName/:resourceId', 'ResourceDetailController@handle', Context::DETAIL),

	// Resource index
    evavel_route_get('/:resourceName/', 'ResourceIndexController@handle'),

	// Resource update
    evavel_route_post('/:resourceName/:resourceId', 'ResourceUpdateController@handle', Context::UPDATE),

	// Resource create
    evavel_route_post('/:resourceName', 'ResourceCreateController@handle', Context::CREATE),

	// Resource delete
    evavel_route_delete('/:resourceName', 'ResourceDestroyController@handle', Context::DELETE),


]);




