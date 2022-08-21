<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use App\Models\Auth;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class checkAuth
{

    /**
     * Отправка сообщения об ошибке
     */
    private function sendError($message)
    {
        return response()->json(
            [
                'message' => $message,
            ],
            401,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $param='')
    {
/*        if ( $request->header('SD-API-PLATFORM') === \App\Config::get('API_PLATFORM_MOBILE') ) {
            // если это мобильное приложение, то проверяем требуемые данные

            // проверка наличия токена в БД и получения по нему пользователя
            // проверка пользователя на возможность входа
            $token = $request->bearerToken();
            
            $user = DB::selectOne(
                "
                select
                    d.user_id as id
                from mobile_app_devices as d
                    inner join users  as u
                        on u.id = d.user_id
                        and u.status = 1
                        and u.`new` = 0
                        and u.dt_dismissal is null
                where BINARY d.token = ?
                ",
                [
                    $token
                ]
            );

            if (!$user) return $this->sendError('Требуется авторизация');

            // получение/генерация СИД из БД по токену
            $_sd = null;
            $sid = -1;

            $session = DB::selectOne(
                "
                    SELECT 
                        id_session,
                        token
                    from `session`
                    where id_user = ?
                    limit 1
                ",
                [$user->id]
            );
            // если сессии нет, делаем задержку и пробуем еще раз
            // проблема с параллельными запросами в мобильном клиенте
            if (!$session) {
                $r = rand(0,2000)*1000;
                usleep($r);
                $session = DB::selectOne(
                    "
                        SELECT 
                            id_session,
                            token
                        from `session`
                        where id_user = ?
                        limit 1
                    ",
                    [$user->id]
                );
            }
            if ($session) {
                $_sd = $session->token;
                $sid = $session->id_session;
            }


            // обновляем сессию
            DB::update(
                "update `session` set dt_reg = now() where id_user = ?",
                [$user->id]
            );
    
            // если активной сессии нет, то регистрируем сессию
            if ( !$_sd ) {
                $_sd = md5(date('YMdHis'));
                $session_register = DB::selectOne(
                    "SELECT session_new(?, ?) as sid",
                    [$user->id, $_sd]
                );
                if ($session_register) {
                    $sid = $session_register->sid;
                }
            }

            if ( $sid == -1 ) return $this->sendError('Превышено число сессий');

            setcookie("_sd", $_sd, 0, "/");
            $_COOKIE['_sd'] = $_sd;

            // проверка возможности входа по СИД
            $check = Auth::is_check_auth($sid, ($param !== 'not_check_script'));

            if ( $check === false ) return $this->sendError('Требуется авторизация');

            // возвращение ответа на устройство
            $request->user = $check['user'];
            $request->system = $check['system'];
            $request->rights = $check['rights'];
            $request->role_group = $check['role_group'];
            $request->script_name = $check['scriptName'];
            $request->script_url = $check['scriptUrl'];

            $request->action = $request->method() === 'POST' ? $request->input('params.action', '') : $request->input('action', '');
            $request->files = $_FILES;
    
            return $next($request);
        }
*/

/*
        $sid = $request->input('sid');
        if ($request->method() === 'POST'){
            $params = $request->input('params', []);
            $sid = arrayGetItem($params, 'sid', arrayGetItem($_SERVER, 'HTTP_SD_SID', NULL));

            $request->files = $_FILES;
            
            if ($sid){
                $request->action = key_exists('action', $params) ? $params['action'] : NULL;
            }
        }
        
        if ( $sid ) {
            $check = Auth::is_check_auth($sid, ($param === 'not_check_script') ? FALSE : TRUE);
            if ($check === FALSE){
                if ($request->ajax() === false) {
                    return redirect( get_app_url(false) );
                }

                return response()->json(
                    ['message' => 'Требуется авторизация 1'],
                    403,
                    ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
                    JSON_UNESCAPED_UNICODE
                );
            }
        } else {
            if ($request->ajax() === false) {
                return redirect( get_app_url(false) );
            }
            return response()->json(
                ['message' => 'Требуется авторизация 2'],
                403,
                ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
                JSON_UNESCAPED_UNICODE
            );
        }

        $request->user = $check['user'];
        $request->system = $check['system'];
        $request->rights = $check['rights'];
        $request->role_group = $check['role_group'];
        $request->script_name = $check['scriptName'];
        $request->script_url = $check['scriptUrl'];
*/
        return $next($request);
    }
}
