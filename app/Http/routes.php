<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () {
        $user = [
            "id"    => auth()->user()->id,
            "name"  => auth()->user()->name,
            "email" => auth()->user()->email,
            "image" => auth()->user()->image,
        ];
        $view = view('todo', $user);
        return $view;
    });
    Route::get('auth/logout', 'Auth\AuthController@logout');
    Route::post('/ListsUser', 'ListsController@action');
});

Route::auth();
