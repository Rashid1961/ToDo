<?php
include_once('mySQL.php');
// Для актов Винкора и Софткейс: гарантия
define('PAID', '1'); //платная
define('NOPAID', '2'); //бесплатная
define('WARRANTY', '1'); //гарантийная
define('NOWARRANTY', '2'); //не гарантийная
define('POSTWARRANTY', '3'); //постгарантийная
define('INSTALL', '4'); //инсталляция

function logPost($uid, $type, $var)
{
    //$post = mysqlStr(serialize($var));
    //$query = "/*".__FILE__.':'.__LINE__."*/ ".
    //"SELECT post_log_add($uid, '$type', '$post')";
    //$result = mysql_query($query) or sendMysqlError(mysql_error(), $query);

    $post = mysqlStr(print_r($var, true));
    $query = "/*".__FILE__.':'.__LINE__."*/ ".
    "SELECT post_log_add($uid, '$type', '$post')";
    $result = mysql_query($query) or sendMysqlError(mysql_error(), $query);

}

function getPureString($str)
{
    $pattern = "/^0*|[^0-9a-zA-Zа-яА-я]*/i";
    $replacement = "";
    return preg_replace($pattern, $replacement, $str);
}

function getErrorMailParams()
{
    return [
        'MAILPWD' => ERROR_MAILPWD,
        'MAILUSER' => ERROR_MAILUSER,
        'MAILFROM' => ERROR_MAILFROM,
        'MAILHOST' => ERROR_MAILHOST,
        'MAILPORT' => ERROR_MAILPORT,
        'MAILSSL' => ERROR_MAILSSL,
    ];
}

// проверяем картинка или нет
function isPicture($name)
{
    if (strpos($name, '.jpg')) {
        return true;
    }
    if (strpos($name, '.png')) {
        return true;
    }
    if (strpos($name, '.jpeg')) {
        return true;
    }
    if (strpos($name, '.gif')) {
        return true;
    }
    return false;
}

function getIdActionForSp($idSp)
{
    $idAction = 0;

    $query = "/*".__FILE__.':'.__LINE__."*/ ".
        "SELECT s.api_id_action
        FROM service_partners s
        WHERE s.id = '$idSp'";
    $result = mysql_query($query) or sendMysqlError(mysql_error(), $query);
    while ($row = mysql_fetch_row($result)) {
        return $row[0];
    }

    return $idAction;
}

function getIdActionForClient($idClient)
{
    $idAction = 0;

    $query = "/*".__FILE__.':'.__LINE__."*/ ".
        "SELECT s.api_id_action
        FROM clients s
        WHERE s.id = '$idClient'";
    $result = mysql_query($query) or sendMysqlError(mysql_error(), $query);
    while ($row = mysql_fetch_row($result)) {
        return $row[0];
    }

    return $idAction;
}

function xmlEntities($string)
{
    return strtr(
        $string,
        array(
            "<" => "&lt;",
            ">" => "&gt;",
            '"' => "&quot;",
            "'" => "&apos;",
            "&" => "&amp;",
        )
    );
}

function getFname($alias)
{
    $fnames = [
        'leaveXml' => 'getLeaveXml',
        'xmlOrder' => 'getXmlOrders',
        'xmlMessage' => 'getXmlMessage',
        'xmlMessageCancel' => 'getXmlMessageCancel',
        'xmlMessageReopen' => 'getXmlMessageReopen',
        'sbXml' => 'getXmlSbLeave',
        'gpbXml' => 'getXmlGpbLeave',
        'lanitLeaveXml' => 'getXmlLanitLeave',
        'reopenSuccess' => 'writeReopenToAdditional',
        'noAction' => 'noAction',
        'newOrderApiMessage' => 'newOrderApiMessage',
        'cancelationApiMessage' => 'cancelationApiMessage',
        'reopenApiMessage' => 'reopenApiMessage',
        'messageApiMessage' => 'messageApiMessage',
        'messageClientApiMessage' => 'messageClientApiMessage',
        'setEngeneerApiMessage' => 'setEngeneerApiMessage',
        'setEngeneerClientApiMessage' => 'setEngeneerClientApiMessage',
        'leaveApiMessage' => 'leaveApiMessage',
        'setSpByServiceContract' => 'setSpByServiceContract',
        'checkEscalation' => 'checkEscalation',
        'setStatusIfNewOrder' => 'setStatusIfNewOrder',
        'setServiceEngeneer' => 'setServiceEngeneer',
        'sendTinkoffMessage' => 'sendTinkoffMessage',
        'writePcClientInProblem' => 'writePcClientInProblem',

        'getTmsOpenedXml' => 'getTmsOpenedXml',
        'getTmsDeleteXml' => 'getTmsDeleteXml',
        'getTmsSetEngeneerXml' => 'getTmsSetEngeneerXml',
        'getTmsLeaveXml' => 'getTmsLeaveXml',
        'getTmsChangeXml' => 'getTmsChangeXml',

        'getLanitOpenedXml' => 'getLanitOpenedXml',
        'getLanitDeleteXml' => 'getLanitDeleteXml',
        'getLanitSetEngeneerXml' => 'getLanitSetEngeneerXml',
        'getLanitLeaveXml' => 'getLanitLeaveXml',
        'getLanitChangeXml' => 'getLanitChangeXml',
        'getLanitSetStatusXml' => 'getLanitSetStatusXml',
        'getLanitSetCloseCodeXml' => 'getLanitSetCloseCodeXml',

        'SET_ENGINEER_NIGHT' => 'set_engineer_night',
    ];

    return !empty($fnames[$alias]) ? $fnames[$alias] : false;
}

function removeXml($list)
{
    foreach ($list as $id => $file) {
        if (endsWith(strtolower($file['filename']), '.xml')) {
            unset($list[$id]);
        }
    }

    return $list;
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}
//////////////////////////////////////////////////////////////////////////////////////////////
// Получение дополнительных параметров для роли или пользователя
//////////////////////////////////////////////////////////////////////////////////////////////
function getRolesAdditionalFields($idRole, $idUser)
{
    if (!$idRole) {
        $query = "/* ".__FILE__.':'.__LINE__." */ "."SELECT u.role from users u where u.id = 1";
        $result = mysql_query($query) or sendMysqlError(mysql_error(), $query);
        $row = mysql_fetch_row($result);
        $idRole = $row[0];
    }

    $list = array();
    // выбираем настройки для роли
    $query = "/* ".__FILE__.':'.__LINE__." */ ".
        "SELECT r.id_right, 'role' tp, r.`status`, r.role_additional, p.value, r.additional_comment, r.right_name
        from v2_right_roles r
            left join roles_additional_param p on r.id_right = p.id_right 
                and r.id_role = p.id_role
                and p.id_user is null
        where r.id_role = $idRole
            and r.role_additional is not null
        order by r.role_additional";
    // echo "<pre>"; print_r($query); echo "</pre>";
    $result = mysql_query($query) or sendMysqlError(mysql_error(), $query);
    while ($row = mysql_fetch_assoc($result)) {
        $list[$row['id_right']]['role'] = array(
            'status' => $row['status'],
            'title' => $row['role_additional'],
            'value' => stripcslashes($row['value']),
            'comment' => stripcslashes($row['additional_comment']),
            'class' => stripcslashes($row['right_name'])
            );
    }

    if ($idUser) {
        // выбираем персональные разрешения
        $query = "/* ".__FILE__.':'.__LINE__." */ ".
            "SELECT r.id_right, 'user' tp, r.`status`, r.role_additional, p.value
            from v2_right_users r
                left join roles_additional_param p on r.id_right = p.id_right 
                    and p.id_user = $idUser
                    and p.id_role = $idRole
            where r.id_user = $idUser
                and r.role_additional is not null";
        // echo "<pre>"; print_r($query); echo "</pre>";
        $result = mysql_query($query) or sendMysqlError(mysql_error(), $query);
        while ($row = mysql_fetch_assoc($result)) {
            $list[$row['id_right']]['user'] = array(
                'status' => $row['status'],
                'title' => $row['role_additional'],
                'value' => stripcslashes($row['value'])
                );
        }
    }

    $html = '';
    foreach ($list as $idRight => $params) {
        // если разрешение для пользователя = 0 - то пропускаем
        // если разрешение для пользователя = 1 и есть параметр пользователя - берем его
        // если разрешение для пользователя и роли = 1 и нет параметра пользователя - берем параметр роли
        // в остальных случаях - пустое значение

        $title = $params['role']['title'];
        $comment = $params['role']['comment'];
        $class = $params['role']['class'];

        if (empty($params['user']) && !$params['role']['status']) {
            continue;
        } elseif (empty($params['user'])) {
            $value = $params['role']['value'];
        } elseif (!$params['user']['status']) {
            continue;
        } elseif ($params['user']['status'] && $params['user']['value']) {
            $value = $params['user']['value'];
        } elseif ($params['user']['status'] && $params['role']['status'] && !$params['user']['value']) {
            $value = $params['role']['value'];
        } else {
            $value = '';
        }
            
        $html .= "<div><p>$comment</p>";
        $html .= "$title: <input id='$idRight' class='$class' value='$value' />";
        $html .= '<button class="saveRoleAdditional">Сохранить</button>';
        $html .= '</div>';
    }

    return $html;
}
////////////////////////////////////////////////////////////////////////
// скачивание файла с FTP
function getFile($fileParams, $needSolt = 1)
{
    // получаем параметры соединения
    $query = "/* ".__FILE__.':'.__LINE__." */ "."SELECT f.address, f.port, f.user, f.pass from general_settings g, ftp_params f where g.ftp_channel = f.id";
    $resSql = mysql_query($query) or die(mysql_error().'<br />'.$query);
    $params = mysql_fetch_assoc($resSql);

    // получаем имя файла на FTP-сервере
    if ($needSolt) {
        $query = "/* ".__FILE__.':'.__LINE__." */ ".
            "SELECT f.users_filename, f.local_filename, f.size 
            from history_files f 
            where f.id = '".mysqlStr($fileParams['id'])."' 
                and f.solt = '".mysqlStr($fileParams['val'])."'";
    } else {
        $query = "/* ".__FILE__.':'.__LINE__." */ ".
            "SELECT f.users_filename, f.local_filename, f.size 
            from history_files f 
            where f.id = '".mysqlStr($fileParams['id'])."'";
    }
    $resSql = mysql_query($query) or die(mysql_error().'<br />'.$query);
    $filenames = mysql_fetch_assoc($resSql);

    if (empty($filenames)) {
        return false;
    }

    $filenames['local_filename'] = str_replace('./', '/home/srvdesk/', $filenames['local_filename']);

    // проверяем кэш
    $cacheFn = "{$fileParams['id']}_{$filenames['users_filename']}";
    $cacheFullFn = __DIR__."/../tmp/cache/$cacheFn";
    $ftpFileName = "ssh2.sftp://{$params['user']}:{$params['pass']}@{$params['address']}:{$params['port']}{$filenames['local_filename']}";
    $fileInCache = file_exists($cacheFullFn);
    $fn = $fileInCache ? $cacheFullFn : $ftpFileName;

    if ($str = file_get_contents($fn)) {
        // пишем в кэш
        if (!$fileInCache) {
            $fwres = file_put_contents($cacheFullFn, $str);
        }
        return array('filename' => $filenames['users_filename'], 'body' => $str, 'idFile' => $fileParams['id'], 'cacheFilename' => $cacheFn);
    } else {
        return false;
    }
}
////////////////////////////////////////////////////////////////////////
// получаем максимальное число загружаемых файлов
function getMaxUploadFilesNumber()
{
    // Количество загружаемых файлов
    $query = "/* ".__FILE__.':'.__LINE__." */ ".'SELECT g.max_uploaded_files from general_settings g';
    $result = mysql_query($query) or sendMysqlError(mysql_error(), $query);
    $row = mysql_fetch_assoc($result);
    return $row['max_uploaded_files'];
}
////////////////////////////////////////////////////////////////////////
// разбираем СМС
function parseIncomeSms($contents)
{
    $sms['CODE'] = -1;
    // нужно преобразовать из UCS2
    $needConvertUcs2 = 0;
    // сообщение состоит из нескольких частей
    $sms['multupartSms'] = 0;
    
    if (!empty($contents)) {
        for ($i = 0; $i < count($contents); $i++) {
            if (preg_match('/From:\s(\d*)/ui', $contents[$i], $matches)) {
                $sms['PHONE'] = $matches[1];
            } elseif (preg_match('/To:\s(\d*)/ui', $contents[$i], $matches)) {
                $sms['TO'] = $matches[1];
            } elseif (preg_match('/Alphabet:\s*UCS/ui', $contents[$i])) {
                $needConvertUcs2 = 1;
            } elseif (preg_match('/UDH: true/ui', $contents[$i])) {
                $sms['multupartSms'] = 1;
            } elseif (preg_match('/UDH-DATA:\s05\s00\s03\s(\d*)\s(\d*)\s(\d*)/i', $contents[$i], $matches)) {
                //UDH представляет из себя следующее:
                //0x05
                //0x00
                //0x03
                //1 октет - уникальный для данной группы СМС номер
                //1 октет - количество СМС для склейки
                //1 октет - порядковый номер СМС
                //Далее идет текст в соответствующей кодировке.

                $sms['key'] = $matches[1];
                $sms['q'] = $matches[2];
                $sms['n'] = $matches[3];
            } elseif (preg_match('/Sent:\s(\d\d-\d\d-\d\d \d\d:\d\d:\d\d)/ui', $contents[$i], $matches)) {
                $sms['DT_RECEIVE'] = $matches[1];
            } elseif (trim($contents[$i]) == '') {
                // дошли до конца, остался только текст СМС
                $sms['TEXT'] = '';
                for ($i++; $i < count($contents); $i++) {
                    $sms['TEXT'] .= $contents[$i];
                }
            }
        }
        if ((!empty($sms['PHONE']) || !empty($sms['TO'])) && !empty($sms['DT_RECEIVE']) && !empty($sms['TEXT'])) {
            $sms['CODE'] = 0;
        }
    }
    
    if (!empty($sms['TEXT'])) {
        if ($needConvertUcs2) {
            $sms['TEXT'] = mb_convert_encoding($sms['TEXT'], 'UTF-8', 'UCS-2');
        }
        // получаем номер телефона по ID (для корпоративных карт МТС)
        if (preg_match('/3364\s*2\s*ID\s*(\d*)\s/ui', $sms['TEXT'], $matches)) {
            $id = mysqlStr(trim($matches[1]));
            $query = "/* ".__FILE__.':'.__LINE__." */ "."SELECT u.phone, u.time_zone from users u where u.android_id = '$id'";
            $result = mysql_query($query) or die('Ошибка '.mysql_error().'<br />');
            while ($row = mysql_fetch_assoc($result)) {
                $phone = $row['phone'];
                $timeZone = $row['time_zone'];
            }
            if (!empty($phone)) {
                // корректируем телефон
                $sms['PHONE'] = $phone;
                // корректируем время получения в соответствии с тайм-зоной пользователя
                $d = ($timeZone - 3);
                $s = ($d < 0) ? ' - '.abs($d) : " + $d";
                $sms['DT_RECEIVE'] = date('y-m-d H:i:s', strtotime('20'.$sms['DT_RECEIVE']."$s hours"));
            }
        }
    }

    return $sms;
}
/////////////////////////////////////////////////////////////////////////////////
// регистрация события в cron_now
function checkRunning($name, $action, $interval = 0)
{
    $query = "/* ".__FILE__.':'.__LINE__." */ "."SELECT cron_run(".SYSTEM_CRON.", '$name', '$action', $interval)";
    $result = mysql_query($query) or sendMysqlError(mysql_error(), $query);
    $row = mysql_fetch_row($result);
    return $row[0];
}
/////////////////////////////////////////////////////////////////////////////////
// получение параметров для FTP
function getFtpParams()
{
        $query = "/* ".__FILE__.':'.__LINE__." */ "."SELECT f.address, f.port, f.user, f.pass from general_settings g, ftp_params f where g.ftp_channel = f.id";
        $resSql = mysql_query($query) or die(mysql_error().'<br />'.$query);
        $row = mysql_fetch_assoc($resSql);
        return $row;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getFullName($file, $patch = '')
{
    // удаляем первый слэш если есть
    $first = substr($file, 0, 1);
    if ($first == '/' || $first == "\\") {
        $file = substr($file, 1);
    }
    
    //echo dirname(__FILE__);
    if ($pos = strpos(dirname(__FILE__), 'subdomains/www/html')) {
        $pos += 19;
    } elseif ($pos = strpos(dirname(__FILE__), 'www')) {
        $pos += 3;
    } else {
        return false;
    }
    
    $file = substr(dirname(__FILE__), 0, $pos).'/'.$file;
    //echo "<hr />$file<hr />";

    // сохраняем путь, если вдруг неудача нас постигнет
    $patch = $file;
    if (file_exists($file)) {
        return $file;
    } else {
        return false;
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function mySubstr($str, $start, $length = false)
{
    if (!$length) {
        $length = strlen($str);
    }
    $bpos = strrpos(substr($str, 0, $start), ' ');
    if (!$bpos) {
        $bpos = $start;
    }
    if ($start > $length) {
        return '';
    }
    if ($length >= strlen($str)) {
        return substr($str, $bpos);
    }
    $fpos = strrpos(substr($str, $start, $length), ' ');
    if ($fpos) {
        return substr($str, $bpos, $fpos+1);
    } else {
        return substr($str, $bpos, $length);
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function writeSystemLog($id, $text, $idAction = 'null', $idOrder = 'null', $idLeave = 'null')
{
    if (empty($idAction)) {
        $idAction = 'null';
    }
    if (empty($idOrder)) {
        $idOrder = 'null';
    }
    if (empty($idLeave)) {
        $idLeave = 'null';
    }
    $ip = getenv("REMOTE_ADDR");
    if (strlen($ip) == 0) {
        $query = "/* ".__FILE__.':'.__LINE__." */ "."INSERT into system_log (id_user, `action`, id_action, id_order) 
            values ($id, '".mysql_real_escape_string($text)."', $idAction, $idOrder)";
    } else {
        $query = "/* ".__FILE__.':'.__LINE__." */ "."INSERT into system_log (id_user, `action`, id_action, id_order) 
        values ($id, '".mysql_real_escape_string($text)." (user IP=$ip)', $idAction, $idOrder)";
    }
    $result = mysql_query($query) or die('Error '.mysql_error().'<br />'.$query);
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getId($sid)
{
    $sid = mysqlStr($sid);
    $token = empty($_COOKIE['_sd']) ? '' : $_COOKIE['_sd'];
    $query = "/* ".__FILE__.':'.__LINE__." */ ".
        "SELECT session_check('$sid', '$token') id";
    // echo $query;
    $result = mysql_query($query) or die('Error '.mysql_error().'<br />'.$query);
    $row = mysql_fetch_assoc($result);
    return $row['id'];
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Проверка прав пользователя
function checkRight($id, $right)
{
    $query = "/* ".__FILE__.':'.__LINE__." */ ".'SELECT r.`status` from v2_right_users r where r.id_user = '.$id.' and r.right_name = \''.mysqlStr($right).'\'';
    //echo $query;
    $result = mysql_query($query) or die('Error '.mysql_error().'<br />'.$query);
    $row = mysql_fetch_assoc($result);

    return $row['status'];
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Получение прав для роли
function getRoleRight($id)
{
    $query = "/* ".__FILE__.':'.__LINE__." */ ".'SELECT rr.right_name, rr.`status` from roles r left join v2_right_roles rr
        on r.id = rr.id_role and r.id = '.$id;
    //echo $query;
    $result = mysql_query($query) or die('Error '.mysql_error().'<br />'.$query);

    $rRight = array();
    while ($row = mysql_fetch_assoc($result)) {
        $rRight[$row['right_name']] = $row['status'];
    }
    return $$rRight;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Отправка JSON с ошибкой
function sendStatusJson($msg, $code)
{
    $response['message'] = $msg;
    $response['code'] = $code;
    //отправляем результат
    echo json_encode($response);
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Отправка JSON с ошибкой mysql
function sendMysqlError($error = '', $query = '')
{
    $response['message'] = 'Ошибка mySQL';
    $response['code'] = 'SDGE005';
    $response['error'] = $error;
    $response['query'] = $query;
    //отправляем результат
    echo json_encode($response);
    exit;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function printHtml($str)
{
    return htmlspecialchars(stripslashes($str));
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function mysqlStr($str)
{
    return mysql_real_escape_string($str);
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Проверка email
function validateEmail($val)
{
    if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    return false;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Проверка направления сортировки для jqGrid
function validateOrder($val)
{
    if (strtoupper($val) == 'ASC' || strtoupper($val) == 'DESC') {
        return true;
    }
    return false;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Проверка целого числа
function validateNumber($val, $min = -999999, $max = 999999999)
{
    if (filter_var($val, FILTER_VALIDATE_INT) !== false && $val >= $min && $val <= $max) {
        return true;
    }
    return false;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Проверка числа с плавающей точкой
function validateFloat($val, $min = -999999, $max = 99999999)
{
    if (filter_var($val, FILTER_VALIDATE_FLOAT) !== false && $val >= $min && $val <= $max) {
        return true;
    }
    return false;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Проверка времени
function validateTime($val)
{
    if (preg_match('/^[0-2]\d:[0-5]\d/', $val)) {
        return true;
    }
    return false;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Проверка даты
function validateDate($val)
{
    if (preg_match('/^[0-3]\d.[01]\d.[2][0][12]\d/', $val)) {
        return true;
    }
    return false;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Проверка цвета
function validateColor($val)
{
    if (preg_match('/^[0-9a-f]{6}/', strtolower($val))) {
        return true;
    }
    return false;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// функция возвращает цветовую схему пользователя
function getUserColor($uid)
{
    $query = "/* ".__FILE__.':'.__LINE__." */ ".'SELECT u.color, if(u.lon = 0, g.lon, u.lon) lon, if(u.lat = 0, g.lat, u.lat) lat
            from users u, general_settings g
            where u.id = '.$uid;
    $result = mysql_query($query) or die('Ошибка '.mysql_error().'<br />');
    $row = mysql_fetch_assoc($result);

    return $row;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// функция возвращает checkbox для выбора пользователем необходимости отправки сообщений
function printMessageCheckbox($ord, $leave, $array, $class, $defaultText, $ignoreWarning = false, $ignoreRequired = false)
{
    $text = '';
    if (!empty($array)) {
        // если есть формы сообщений, выводим кнопки
        foreach ($array as $mid => $message) {
            // если пустой элемент, то пропускаем
            if (count($message) == 0) {
                continue;
            }
            // если стоит признак "игнорировать обязательные сообщения" и это обязательное сообщение, то пропускаем
            if ($ignoreRequired && !empty($message['required'])) {
                continue;
            }
            // если стоит признак "игнорировать предупреждения" и это об этом сообщении надо предупредить
            $sel = (!$ignoreWarning && !empty($message['warning'])) ? "checked" : "";

            $text .= "<div class='inline-block'>";
            $text .= "    <input type='hidden' class='mid' value='$mid'>";
            $text .= "    <input type='hidden' class='idOrder' value='$ord'>";
            $text .= "    <input type='hidden' class='idLeave' value='$leave'>";
            $text .= "    <label>";
            $text .= "    <input type='checkbox' $sel class='message $class'/>";
            $text .= (!empty($message['desc'])) ? printHtml($message['desc']) : $defaultText.' '.$mid;
            $text .= "    </label>";
            $text .= "</div>";
        }
    }
    return $text;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// добавление даты
// Интервалом может быть один из нижеизложенных:
// yyyy год
// q    четверть
// q    четверть
// m    месяц
// y    день года
// d    день
// w    день недели
// ww   неделя года
// h    час
// n    минута
// s    секунда
// Здесь w, y и d делают одно и то же, а именно добавляют 1 день к текущему дню, q добавляет 3 месяца, ww добавляет 7 дней

function dateAdd($interval, $number, $date)
{
    $date_time_array = getdate($date);
    $hours = $date_time_array['hours'];
    $minutes = $date_time_array['minutes'];
    $seconds = $date_time_array['seconds'];
    $month = $date_time_array['mon'];
    $day = $date_time_array['mday'];
    $year = $date_time_array['year'];

    switch ($interval) {
        case 'yyyy':
            $year+=$number;
            break;
        case 'q':
            $year+=($number*3);
            break;
        case 'm':
            $month+=$number;
            break;
        case 'y':
        case 'd':
        case 'w':
            $day+=$number;
            break;
        case 'ww':
            $day+=($number*7);
            break;
        case 'h':
            $hours+=$number;
            break;
        case 'n':
            $minutes+=$number;
            break;
        case 's':
            $seconds+=$number;
            break;
    }
    $timestamp= mktime($hours, $minutes, $seconds, $month, $day, $year);
    return $timestamp;
}

////////////////////////////////////////////////////////////////////////
// функция сортировки массива
function cmp($a, $b)
{
    // сортируем по любимости инженеров
    if ($a['fav'] != $b['fav']) {
        return ($a['fav'] > $b['fav']) ? -1 : 1;
    } else {
        // сортируем по количеству заявок
        if ($a['cnt'] == $b['cnt']) {
            return 0;
        }
        return ($a['cnt'] < $b['cnt']) ? -1 : 1;
    }
}

// секунды в период
// 1800 -> 30м 00с
function secToPeriod($time, $default = '')
{
    if ($time <= 0) {
        return $default;
    }

    $days = floor($time / (60 * 60 * 24));
    $time -= $days * (60 * 60 * 24);

    $hours = floor($time / (60 * 60));
    $time -= $hours * (60 * 60);

    $minutes = floor($time / 60);
    $time -= $minutes * 60;

    $seconds = floor($time);

    $result = "";
    $result = $seconds ? "{$seconds}с" : $result;
    $result = $minutes ? "{$minutes}м {$seconds}с" : $result;
    $result = $hours ? "{$hours}ч {$minutes}м {$seconds}с" : $result;
    $result = $days ? "{$days}дн {$hours}ч {$minutes}м {$seconds}с" : $result;

    return $result;
}

// debug - трассировка вызова функции
function trace($params = null, $append = false)
{
    //return 0;
    $result = debug_backtrace();
    $str = "";
    foreach ($result as $key => $item) {
        $str .= "file: {$item['line']}: {$item['file']}\n";
        if ($item['function'] === 'trace') {
            continue;
        }
        $str .= "function: " . ($item['class'] ? $item['class'] . '::' : '') . "{$item['function']}(" . implode(', ', $item['args']) . ")\n";
    }
    file_put_contents(__DIR__ . '/trace.log', $str . "\n", ($append) ? FILE_APPEND : null);

    if ($params) {
        file_put_contents(__DIR__ . '/trace.log', 'vars:' . "\n", FILE_APPEND);
        ob_start();
        var_dump($params);
        $output = ob_get_clean();
        file_put_contents(__DIR__ . '/trace.log', $output . "\n", FILE_APPEND);
    }

    return 0;
}

// отладка с выводм на экран и завершением работы скрипта
function dd($var)
{
    echo "<pre>";
    if (is_array($var)) {
        print_r($var);
    } else {
        print_r($var);
    }
    echo "</pre>";
    exit();
}

// отладка и вывод в консоль браузера
function dd_console($var)
{
    echo "<script>";
    echo "console.log(".json_encode($var).")";
    echo "</script>";
}

/**
 * Сумма прописью
 * 
 */
function sum2str($num) {
	$nul='ноль';
	$ten=array(
		array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
		array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
	);
	$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
	$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
	$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
	$unit=array( // Units
		array('копейка' ,'копейки' ,'копеек',	 1),
		array('рубль'   ,'рубля'   ,'рублей'    ,0),
		array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
		array('миллион' ,'миллиона','миллионов' ,0),
		array('миллиард','милиарда','миллиардов',0),
	);
	//
	list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
	$out = array();
	if (intval($rub)>0) {
		foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
			if (!intval($v)) continue;
			$uk = sizeof($unit)-$uk-1; // unit key
			$gender = $unit[$uk][3];
			list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
			// mega-logic
			$out[] = $hundred[$i1]; # 1xx-9xx
			if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
			else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
			// units without rub & kop
			if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
		} //foreach
	}
	else $out[] = $nul;
	$out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
	$out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
	return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

/**
 * Склонение словоформы
 * 
 */
function morph($n, $f1, $f2, $f5) {
	$n = abs(intval($n)) % 100;
	if ($n>10 && $n<20) return $f5;
	$n = $n % 10;
	if ($n>1 && $n<5) return $f2;
	if ($n==1) return $f1;
	return $f5;
}


class Debug
{

    function __construct()
    {
    }

    static  function clear()
    {
        file_put_contents( __DIR__ .  '/../api_log.t', '');
    }


    static function log($text, $fileName="", $functionName="", $className="", $args=null)
    {
        $time = date('y-m-d H:i:s', time());
        $fpc_message = "$time";
        if ($fileName)
        {
            
        }
        if ($className)
        {
            $fpc_message .= "\tclass $className";
        }
        if ($functionName)
        {
            $fpc_message .= "\t$functionName()";
        }
        $fpc_message .= "\t$text";

        $fpc_args = "";
        if ($args)
        {
            $fpc_message .= "\n" . print_r($args, TRUE);
            $fpc_args = print_r($args, TRUE);
        }

        //file_put_contents(__DIR__ .  '/../api_log.t', $fpc_message . "\n", FILE_APPEND);

        $text = mysqlStr($text);
        $fileName = mysqlStr($fileName);
        $functionName = mysqlStr($functionName);
        $className = mysqlStr($className);
        $fpc_args = mysqlStr($fpc_args);

        $query = "
          INSERT INTO tmp_cronstream2_log
            (date,message,file_name, function_name, class_name, args)
          VALUES
            (now(), '$text','$fileName','$functionName','$className','$fpc_args');
        ";
        $result = mysql_query($query);// or sendMysqlError(mysql_error(), $query);
    }
}
