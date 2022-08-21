<?php
function checkScript($idUser, $scriptName)
{
    //$query='select s.name_rus from scripts s, v2_right_users r
    //  where s.id_right = r.id_right and r.`status` = 1 and s.script_name = \''.$scriptName.'\' and r.id_user = '.$idUser;
    $query = "select s.name_rus from scripts s, v2_right_users r, main_menu m 
        where r.`status` = 1 
        and (s.script_name = '$scriptName' or REPLACE(s.script_name, '/v2/', '/') = '$scriptName') and r.id_user = $idUser
        and m.id_script = s.id and m.id_right = r.id_right";

    //echo $query;
    $result=mysql_query($query)or die(mysql_error().$query);
    //echo $query;
    if (!mysql_num_rows($result)) {
        return false;
    } else {
        $row = mysql_fetch_assoc($result);
        return $row['name_rus'];
    }
}
// =============================================================================================
$id = 0;
if (!empty($_GET['sid'])) {
    //Проверяем есть ли сессия и не просрочилась ли она
    $sid = mysqlStr($_GET['sid']);
    $token = empty($_COOKIE['_sd']) ? '' : $_COOKIE['_sd'];
    $query = "SELECT session_check('$sid', '$token') id";
    // echo "<pre>"; print_r($query); echo "</pre>";
    // exit;
    $result = mysql_query($query) or die('Ошибка '.mysql_error().'<br />');
    $row = mysql_fetch_assoc($result);

    if (empty($row['id'])) { // не авторизован, или просрочен, то перекидываем на авторизацию
        header("Location: https://".$_SERVER['HTTP_HOST']."/auth.php");
        exit;
    } else { // авторизация - ОК, извлекаем цвет, права
        $id = $row['id'];
        $query = "SELECT r.name from v2_right_users u, `rights` r where r.id = u.id_right and u.`status` = 1 and u.id_user = $id";
        $result = mysql_query($query) or die('Ошибка '.mysql_error().'<br />');
        while ($row = mysql_fetch_assoc($result)) {
            $rights[$row['name']] = 1;
        }

        $query = "SELECT u.color, f.main_refresh_period, f.pager_step, u.id_client,
                _get_time_offset_by_user(f.id_user) time_offset, u.android_id as android_id
            from users u left join users_filter f on u.id = f.id_user, general_settings g
            where u.id = $id";
        $result = mysql_query($query) or die('Ошибка '.mysql_error().'<br />');
        $row = mysql_fetch_assoc($result);
        $color = $row['color'];
        $user_android_id = $row['android_id'];
        // Количество загружаемых файлов
        $maxUploadedFiles = getMaxUploadFilesNumber();
        $refreshPeriod = $row['main_refresh_period'];
        $pagerStep = $row['pager_step'];
        $timeOffset = $row['time_offset'];
        $idClientUser = $row['id_client'];

        if (empty($rights)) { //нет прав ни на что, выкидываем
            header("Location: https://".$_SERVER['HTTP_HOST']."/auth.php");
            exit;
        }

        $isClient = (empty($rights['is_client'])) ? 0 : 1;
        $isPartner = (empty($rights['is_partner'])) ? 0 : 1;
        $isWe = (!$isClient && !$isPartner) ? 1 : 0;
        $isOperator = (empty($rights['is_operator'])) ? 0 : 1;
        $isAdmin = (empty($rights['is_admin'])) ? 0 : 1;
    }
    //Проверяем, разрешен ли для этого пользователя этот скрипт
    if (!$scriptName = checkScript($id, $_SERVER['SCRIPT_NAME'])) {
        header("Location: https://".$_SERVER['HTTP_HOST']."/auth.php");
        exit;
    }
} else { //нет id сессии, выкидываем
    header("Location: https://".$_SERVER['HTTP_HOST']."/auth.php");
    exit;
}
/* На выходе:
$id - id пользователя, 
$user_android_id
$scriptName - название скрипта, 
$rights - массив с правами, 
*/
if ($id) {
    writeSystemLog($id, "Запрошена страница '$scriptName' ({$_SERVER['SCRIPT_NAME']})", 335);
}
