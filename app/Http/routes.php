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

Route::group(['middleware' => 'auth'], function () {

    Route::get('/', function() {
        $user = [
            'id'      => auth()->user()->id,
            'name'    => auth()->user()->name,
            'email'   => auth()->user()->email,
            'image'   => auth()->user()->image,
            'preview' => auth()->user()->preview,
        ];
        return view('lists', $user);
    });

    Route::group(['prefix' => '/Lists/'], function () {  // Списки
        Route::post('/getLists',        'ListsController@getLists');
        Route::post('/changeTitleList', 'ListsController@changeTitleList');
        Route::post('/deleteList',      'ListsController@deleteList');
        Route::post('/appendList',      'ListsController@appendList');
        Route::post('/getImgList',      'ListsController@getImgList');
    });
    
    Route::group(['prefix' => '/Items/'], function () {  // Пункты списков
        Route::get('expandList/{idList}', 'ItemsController@expandList')->where('id', '[0-9]+');
        Route::post('/getItems',          'ItemsController@getItems');
        Route::post('/getImgItem',        'ItemsController@getImgItem');
        Route::post('/appendItem',        'ItemsController@appendItem');
        Route::post('/changeTitleItem',   'ItemsController@changeTitleItem');
        Route::post('/deleteItem',        'ItemsController@deleteItem');
        Route::post('/changeTagsItem',    'ItemsController@changeTagsItem');
    });

    Route::group(['prefix' => '/Images/'], function () {  // Изображения
        Route::get('/showImage',    'ImagesController@showImage');
        Route::post('/uploadImage', 'ImagesController@uploadImage');
        Route::post('/delImage',    'ImagesController@delImage');
    });
});

Route::auth();
