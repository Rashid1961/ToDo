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

    Route::get('/', function() {  //--------------- Стартовая страница (перечень списков)
        $user = [
            'id'      => auth()->user()->id,
            'name'    => auth()->user()->name,
            'email'   => auth()->user()->email,
            'image'   => auth()->user()->image,
            'preview' => auth()->user()->preview,
        ];
        return view('lists', $user);
    });

    Route::group(['prefix' => '/Lists/'], function () {  //------------------- Списки
        Route::post('/getLists',        'ListsController@getLists');        // Получение списков пользователя
        Route::post('/changeTitleList', 'ListsController@changeTitleList'); // Переименование списка
        Route::post('/deleteList',      'ListsController@deleteList');      // Удаление списка и всех его пунктов
        Route::post('/appendList',      'ListsController@appendList');      // Добавление списка
        Route::post('/getImgList',      'ListsController@getImgList');      // Получение image и preview списка
    });
    
    Route::group(['prefix' => '/Items/'], function () {  //--------------------------------------- Пункты списков
        Route::get('expandList/{idList}', 'ItemsController@expandList')->where('idList', '[0-9]+'); // Вывод пунктов списка
        Route::post('/getItems',          'ItemsController@getItems');                              // Получение пунктов списка
        Route::post('/getImgItem',        'ItemsController@getImgItem');                            // Получение image и preview пункта
        Route::post('/appendItem',        'ItemsController@appendItem');                            // Добавление пункта
        Route::post('/changeTitleItem',   'ItemsController@changeTitleItem');                       // Переименование пункта
        Route::post('/deleteItem',        'ItemsController@deleteItem');                            // Удаление пункта
        Route::post('/changeTagsItem',    'ItemsController@changeTagsItem');                        // Изменение тегов пункта
    });

    Route::group(['prefix' => '/Images/'], function () {  //----------- Изображения
        Route::get('/showImage',    'ImagesController@showImage');   // Вывод изображения в отдельном окне
        Route::post('/uploadImage', 'ImagesController@uploadImage'); // Загрузка нового изображения при изменении
        Route::post('/delImage',    'ImagesController@delImage');    // Удаление изображения (замена дефолтным)
    });
});

Route::auth();  // Авторизация
