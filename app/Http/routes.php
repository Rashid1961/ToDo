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

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function() {
        $user = [
            'id'    => auth()->user()->id,
            'name'  => auth()->user()->name,
            'email' => auth()->user()->email,
            'image' => auth()->user()->image,
        ];
        $view = view('todo', $user);
        return $view;
    });
    Route::get('/ShowImage', function(Request $request) {
        $data = [
            'whatShow' => $request->input('whatShow'),
            'image'    => $request->input('image'),
        ];
        print_r('\$data =');
        print_r($data);
        $view = view('showimage', $data);
        return $view;
    });
    Route::post('/Lists', 'ListsController@action');
});

Route::auth();
