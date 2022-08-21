<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Cache;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Auth
{

    static function login($email, $password)
    {
        $sid = NULL;
        $password = md5($password);
        $rows = DB::select(
            "
            SELECT 
                u.id, 
                u.password, 
                u.first_page,
                u.role
            from v2_real_users u, v2_real_clients c, general_settings g
            where upper(email) = upper('$email')
                and c.id = u.id_client
                and u.status = 1
                and g.maintenance = 0
            limit 1
            ",
            [$email]
        );

        $firstPage = false;
        $pwdDb = false;
        $uid = false;
        $role = false;
        $token = null;
        foreach ($rows as $row) {
            $firstPage = $row->first_page;
            $pwdDb = $row->password;
            $uid = $row->id;
            $role = $row->role;
        }

        if ($uid && $password == $pwdDb) {
            $token = null;
            $sid = null;

            // пробуем регистрировать сессию
            if ($role == 1) {
                $rows = DB::select(
                    "
                        SELECT 
                            id_session,
                            token
                        from `session`
                        where id_user = ?
                        limit 1
                    ",
                    [$uid]
                );
                foreach ($rows as $row) {
                    $token = $row->token;
                    $sid = $row->id_session;
                }
            }

            if ($token == null) {
                $token = md5(date('YMdHis'));

                $rows = DB::select(
                    "
                        SELECT session_new(?, ?) sid;
                    ",
                    [$uid, $token]
                );

                foreach ($rows as $row) {
                    $sid = $row->sid;
                }
            }
        }

        if ($sid && $sid != -1) {
            //writeSystemLog($uid, 'Вход', 1);
            setcookie("_sd", $token, 0, "/");
            return $sid;            
        }

        if ($sid == -1) {
            //writeSystemLog($uid, 'Попытка входа. Превышено число сессий', 1);
        }
        return false;
    }

/*    static function logout($sid)
    {
        DB::select("SELECT session_del(?)", [$sid]);
        
        return true;
    }

    static function is_check_auth($sid, $is_check_script=TRUE)
    {
        $id = 0;
        $user = (object)[];
        $rights = [];
        $scriptName = NULL;

        $isClient = 0;
        $isPartner = 0;
        $isWe = 0;
        $isOperator = 0;
        $isAdmin = 0;
        $isDeveloper = 0;

        //Проверяем есть ли сессия и не просрочилась ли она
        $token = empty($_COOKIE['_sd']) ? '' : $_COOKIE['_sd'];

        $rows = DB::select(
            "
            SELECT session_check(?, ?) id
            ",
            [$sid, $token]
        );

        $row = $rows[0];

        if ($row->id == NULL) { // не авторизован, или просрочен, то перекидываем на авторизацию
            return FALSE;
        } else { // авторизация - ОК, извлекаем цвет, права
            $id = $row->id;
            $rows = Cache::remember("user.auth.rights.uid={$id}", 2, function() use ($id) {
                return DB::select(
                    "
                    SELECT 
                        r.name 
                    from v2_right_users u, `rights` r 
                    where r.id = u.id_right and u.`status` = 1 and u.id_user = ?
                    ",
                    [$id]
                );
            });

            foreach ($rows as $row) {
                $rights[$row->name] = 1;
            }

            $rows = Cache::remember("user.auth.info.uid={$id}", 2, function() use ($id) {
                return DB::select(
                    "
                    SELECT 
                        u.color
                        , f.main_refresh_period
                        , f.pager_step
                        , u.id_client
                        , c.id_partner
                        , c.`desc`                            as client_name
                        , _get_time_offset_by_user(f.id_user) as time_offset
                        , g.max_uploaded_files
                        , u.surname
                        , u.name
                        , u.middlename
                        , u.`role`                          as role_id
                        , r.`desc`                          as role_name
                        , ifnull(u.id_paid, 0)              as id_paid
                        , ifnull(u.id_warranty, 0)          as id_warranty
                        , g.time_zone                       as system_time_zone
                        , u.android_id                      as aid
                    from users u 
                        left join users_filter f on u.id = f.id_user
                        inner join roles as r on r.id = u.`role`
                        inner join general_settings as g on g.id = 1
                        left join clients as c
                        on c.id = u.id_client
                    where u.id = ?
                    limit 1
                    ",
                    [$id]
                );
            });

            if (count($rows)>0){
                $row = $rows[0];

                $user = (object)[
                    'id' => $id,
                    'aid' => $row->aid,
                    'surname' => $row->surname,
                    'firstname' => $row->name,
                    'middlename' => $row->middlename,
                    'role_name' => $row->role_name,
                    'name' => $row->surname .
                        ( $row->name ? ' ' . mb_substr($row->name, 0, 1) . '.' : '') .
                        ( $row->middlename ? ' ' . mb_substr($row->middlename, 0, 1) . '.' : ''),
                    'color' => $row->color,
                    'maxUploadedFiles' => $row->max_uploaded_files,
                    'refreshPeriod' => $row->main_refresh_period,
                    'pagerStep' => $row->pager_step,
                    'timeOffset' => $row->time_offset,
                    'idClientUser' => $row->id_client,
                    'id_client' => $row->id_client,
                    'client_id' => $row->id_client,
                    'client_name' => $row->client_name,
                    'id_sp' => $row->id_partner,
                    'id_paid' => $row->id_paid,
                    'id_warranty' => $row->id_warranty,
                    'sid' => $sid
                ];                

                $system_time_zone = $row->system_time_zone;

                $isDeveloper = $row->role_id == 1 ? 1 : 0;
            }

            if (count($rights) == 0) { //нет прав ни на что, выкидываем
                return FALSE;
            }

            $isClient = (empty($rights['is_client'])) ? 0 : 1;
            $isPartner = (empty($rights['is_partner'])) ? 0 : 1;
            $isWe = (!$isClient && !$isPartner) ? 1 : 0;
            $isOperator = (empty($rights['is_operator'])) ? 0 : 1;
            $isAdmin = (empty($rights['is_admin'])) ? 0 : 1;
            if ($isDeveloper){
                $rights['is_developer'] = 1;
            }
            if ($isWe) {
                $rights['is_we'] = 1;
            }
        }

        //Проверяем, разрешен ли для этого пользователя этот скрипт
        $scriptName = NULL;
        $script_from_url = get_script_from_url(FALSE);
        
        if ($is_check_script){
            $script_from_url = get_script_from_url();
            if (!$scriptName = checkScript($id, $script_from_url )) {
                return FALSE;
            }
        }

        writeSystemLog($id, "Запрошена страница '$scriptName' ({$script_from_url })", 335);

        return [
            'user' => $user,
            'system' => (object)[
                'time_zone' => $system_time_zone
            ],
            'rights' => $rights,
            'role_group' => (object)[
                'isClient' => $isClient,
                'isPartner' => $isPartner,
                'isWe' => $isWe,
                'isOperator' => $isOperator,
                'isAdmin' => $isAdmin,
                'isDeveloper' => $isDeveloper,
            ],
            'scriptName' => $scriptName,
            'scriptUrl' => $script_from_url
        ];
    }
*/
}