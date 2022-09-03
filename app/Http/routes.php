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

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\ListsController;

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
    Route::group(['prefix' => '/Images/'], function () {
        Route::get('/showImage', 'ImagesController@showImage');
        //Route::get('/ShowImage', function(Request $request) {
        //    $data = [
        //        'id'       => $request->input('id'),
        //        'whatShow' => $request->input('whatShow'),
        //        'image'    => $request->input('image'),
        //        'name'     => $request->input('name'),
        //    ];
        //    $view = view('showimage', $data);
        //    return $view;
        //});
    });

    Route::group(['prefix' => '/Lists/'], function () {
        Route::post('/getLists',        'ListsController@getLists');
        Route::post('/changeTitleList', 'ListsController@changeTitleList');
        Route::post('/deleteList',      'ListsController@deleteList');
        Route::post('/appendList',      'ListsController@appendList');
    });
    
    Route::group(['prefix' => '/Items/'], function () {
        Route::post('/getItems', 'ItemsController@getItems');
    });
});

Route::auth();
