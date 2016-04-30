<?php

// setup routes 
if (config('mitter.route.useMitterRoutes')) {

    Route::group(config('mitter.route.routeGroupConfig', []), function () {

        /**
         * index
         */
        Route::get('/{model}', 'BaseController@index');

        /**
         * create
         */
        Route::get('/{model}/create', 'BaseController@create');

        /**
         * store
         */
        Route::post('/{model}', 'BaseController@store');

        /**
         * show
         */
        Route::get('/{model}/{id}', 'BaseController@show');

        /**
         * edit
         */
        Route::get('/{model}/{id}/edit', 'BaseController@edit');

        /**
         * update
         */
        Route::put('/{model}/{id}', 'BaseController@update');

        /**
         * delete
         */
        Route::delete('/{model}/{id}', 'BaseController@destroy');

    });
}

