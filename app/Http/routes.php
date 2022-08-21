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
use App\Models\Auth;
use Illuminate\Support\Facades\DB;

/*
Route::group(['prefix' => 'api/web/'], function () {
    Route::post('/login', function(Request $request){
        $input = $request->input();
        if (key_exists('params', $input)){
            $login = $input['params']['login'];
            $password = $input['params']['password'];

            $sid = Auth::login($login, $password);
            /*
            if (is_string($sid)){
                $a = explode('sid=', $sid);
                if (count($a) == 2) {
                    // data
                    $row = DB::selectOne(
                        "
                        select
                            s.id_user as user_id,
                            u.android_id as user_aid
                        from `session` as s
                            inner join users as u on u.id=s.id_user
                        where s.id_session = ?
                        ",
                        [ $a[1] ]
                    );
                    if ($row === null) {
                        return ['message'=>'failed'];
                    }
                    return [
                        'sid' => $a[1],
                        'id' => $row->user_id,
                        'aid' => $row->user_aid
                    ];
                }
            }
            * /
            return [
                'sid' => $sid ,
                'id' => 9902,
            ];

            //return ['message'=>'failed'];
        }

        return ['message'=>'failed'];
    });
    //Route::any('/start.php', 'scripts\StartController@index');//->middleware('checkAuth:check_script');
});
*/    


//Route::group(['middleware' => 'checkAuth:not_check_script', 'prefix' => '/'], function () {
//Route::group(['prefix' => '/'], function () {
    //Route::group(['middleware' => 'checkAuth:not_check_script'], function () {
    //Route::GET('/', function () {return view('todo');});
    //Route::GET('/{a}', function () {return view('todo');});
    //Route::GET('/{a}/{b}', function () {return view('todo');});
    //Route::GET('/{a}/{b}/{c}', function () {return view('todo');});
    //Route::GET('/{a}/{b}/{c}/{d}', function () {return view('todo');});
//});

//);

Route::group(['prefix' => '/'], function () {
    Route::post('/login', function(Request $request){
        $input = $request->input();
        if (key_exists('params', $input)){
            $login = $input['params']['login'];
            $password = $input['params']['password'];

            $sid = Auth::login($login, $password);
            /*
            if (is_string($sid)){
                $a = explode('sid=', $sid);
                if (count($a) == 2) {
                    // data
                    $row = DB::selectOne(
                        "
                        select
                            s.id_user as user_id,
                            u.android_id as user_aid
                        from `session` as s
                            inner join users as u on u.id=s.id_user
                        where s.id_session = ?
                        ",
                        [ $a[1] ]
                    );
                    if ($row === null) {
                        return ['message'=>'failed'];
                    }
                    return [
                        'sid' => $a[1],
                        'id' => $row->user_id,
                        'aid' => $row->user_aid
                    ];
                }
            }
            */
            return [
                'sid' => $sid ,
                'id' => 9902,
            ];

            //return ['message'=>'failed'];
        }

        return ['message'=>'failed'];
    });

    Route::get('/', function () {
        //return view('welcome');
        return view('todo');
    });
});
Route::get('contact-form', 'ContactController@create');
Route::post('contact-form', 'ContactController@store');