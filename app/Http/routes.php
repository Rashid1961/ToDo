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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


    Route::get('/', function () {
        $user = [
            "id"    => auth()->user()->id,
            "name"  => auth()->user()->name,
            "email" => auth()->user()->email
        ];
        $view = view('todo', $user);
        return $view;
    })->middleware('auth');

    Route::post('/ListsUser', 'ListsController@action')->middleware('auth');


    Route::get('auth/logout', 'Auth\AuthController@logout')->middleware('auth');
    //Route::post('/Users', 'UsersController@indexAction');

    Route::auth();

//Route::get('/home', 'HomeController@index');
