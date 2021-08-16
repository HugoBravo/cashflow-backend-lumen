<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group([

    // 'middleware' => 'api',
    'prefix' => 'api/auth'

], function ($router) {

    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');
    $router->post('refresh', 'AuthController@refresh');
    $router->post('me', 'AuthController@me');
});

$router->group([

    'middleware' => 'jwt.verify',
    'prefix' => 'api'

], function ($router) {

    // CATEGORIES
    $router->put('/cash-categories', 'CashCategoryController@update');
    $router->delete('/cash-categories/{id}', 'CashCategoryController@delete');
    $router->get('/cash-categories/{type}', 'CashCategoryController@getCategoriesByType');
    $router->post('/cash-categories', 'CashCategoryController@addCategory');

    // CONCEPTS
    $router->put('/cash-concepts', 'CashConceptsController@update');

    $router->get('/cash-concepts/{type}', 'CashConceptsController@getConceptsByType');

    $router->post('/cash-concepts', 'CashConceptsController@addConcept');
    $router->delete('/cash-concepts/{id}', 'CashConceptsController@delete');

    // CURRENCIES
    $router->get('/currencies/{showNulls}', 'CurrenciesController@index');
    $router->put('/currencies', 'CurrenciesController@update');

    // CASH FLOW
    $router->get('/cash-flow/not-closed', 'CashFlowController@flowsNotClosed');
    $router->get('/cash-flow/not-closed/{currency_id}/{showNulls}/', 'CashFlowController@flowsNotClosedByCurrency');

    $router->post('/cash-flow', 'CashFlowController@addFlow');
    $router->put('/cash-flow', 'CashFlowController@updateFlow');

    $router->get('/cash-flow/years-with-flow', 'CashFlowController@yearsWithFlow');
    $router->get('/cash-flow/{id}', 'CashFlowController@getFlow');

    $router->get('/cash-trace/{id}', 'CashTraceController@getCashTrace');

    $router->get('/cash-flow/search-json/{from}/{to}/{type}/{currency_id}/{user}/', 'CashFlowController@searchJson');
    $router->get('/cash-flow/search-xls/{from}/{to}/{type}/{currency_id}/{user}/', 'CashFlowController@searchXls');

    $router->post('/cash-flow/attach', 'CashFlowController@addAttachToFlow');

    $router->post('/cash-flow/status', 'CashFlowController@flowStatus');

    //CASH CLOSURE
    $router->post('/cash-closure', 'CashClosureController@store'); // Eliminar
    $router->post('/cash-closure/close', 'CashClosureController@cashClose');
    $router->put('/cash-closure/status', 'CashClosureController@statusClosure');
    $router->get('/cash-closure/historic/{n}/{showNull}', 'CashClosureController@getHistoricClosures');
    $router->get('/cash-closure/historic-graph/{n}/{showNull}', 'CashClosureController@getGraphHistoricClosures');
    $router->get('/cash-closure/last', 'CashClosureController@lastClosure');

    //GRAPHS
    $router->get('/cash-flow/graph/categories/{currency_id}/{type}/{year}/{initMonth}/{nMonths}', 'CashFlowController@graphFlowByCategoryAndMonth');
    $router->get('/cash-flow/graph/concepts/{currency_id}/{type}/{yearFrom}/{monthFrom}/{dayFrom}/{yearTo}/{monthTo}/{dayTo}', 'CashFlowController@graphFlowsByConceptAndDaysRange');

    // USERS
    $router->get('/users', 'UserController@users');

    $router->get('/users-admin', 'UserController@getUsers');
    $router->get('/users-admin/roles', 'UserRolesController@getRoles');
    $router->post('/users-admin', 'UserController@add');
    $router->put('/users-admin', 'UserController@update');
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});
