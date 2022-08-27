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
        return view("todo");
        //$user = [
        //    "id"    => auth()->user()->id,
        //    "name"  => auth()->user()->name,
        //    "email" => auth()->user()->email,
        //    "image" => auth()->user()->image,
        //];
        //$view = view('todo', $user);
        //return $view;
    });
    Route::post('/Lists', 'ListsController@action');
});

Route::auth();