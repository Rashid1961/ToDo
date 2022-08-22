<?php

use Illuminate\Support\Facades\DB;

/**
 * запись информации в системный лог
 * @param integer $id user-ID
 * @param string $text
 * @return void
 */
function writeSystemLog($id_user, $action)
{
    DB::insert(
        "INSERT into system_log (id_user, `action`) values (?, ?)",
        [$id_user, $action]
    );
}

/**
 * получение элемента массива по ключу
 * @param array $arr
 * @param integer|string $key
 * @param boolean $default
 * @return mixed
 */
function arrayGetItem($arr, $key, $default = NULL)
{
    if (!is_array($arr)){
        return $default;
    }
    return key_exists($key, $arr) ? $arr[$key] : $default;
}

/**
 * получение элемента массива по пути
 * @param array $arr
 * @param array<string> $key_list
 * @param boolean $default
 * @return mixed
 */
function arrayGetValue($arr, $key_list, $default = NULL)
{
    if (count($key_list) == 0) return $arr;
    if (!is_array($arr)) return $default;

    $first_key = array_shift($key_list);
    return key_exists($first_key, $arr) ? arrayGetValue($arr[$first_key], $key_list, $default) : $default;
}

/**
 * сцепление строк с разделителем
 * @param string $separator
 * @param array<string> $arr
 * @return string
 */
function concat_ws($separator, $arr)
{
    $diff_arr = array_diff($arr, array('', null));
    return implode($separator, $diff_arr);
}

/**
 * экранирование спец символов для sql запроса
 * @param mixed
 * @return string
 */
function db_quote($value)
{
    return DB::connection()->getPdo()->quote($value);
}

/**
 * размер файла
 * @param integer $filesize
 * @return string
 */
function getFileSize($filesize) {
  foreach (array(0x40000000=>'Гб', 0x100000=>'Мб', 0x400=>'Кб', 1=>'б') as $k=>$v)
    if ($filesize >= $k) return round($filesize/$k).' '.$v;
  return '0 б';
}

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

// функция сортировки объекта
function cmpO($a, $b)
{
    // сортируем по любимости инженеров
    if ($a->fav != $b->fav) {
        return ($a->fav > $b->fav) ? -1 : 1;
    } else {
        // сортируем по количеству заявок
        if ($a->cnt == $b->cnt) {
            return 0;
        }
        return ($a->cnt < $b->cnt) ? -1 : 1;
    }
}

/**
 * форматирование даты
 * @param string $date
 * @param string $format
 * @param boolean $default значение если $date не дата
 * @return string
 */
function str_to_date($date, $format='Y-m-d', $default=null)
{
    $time = strtotime(trim($date));
    if (!$time)  return $default;

    return date($format, $time);
}

/**
 * получение первого ключа в массиве
 * @param array $arr
 * @return mixed
 */
if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}

/**
 * Перевод секунд во время
 * @param integer $timeSec время в секундах
 * @param string $format формат времени
 * @return string
 */
function secToTimeStr($timeSec, $format="H:i")
{
    $time = (int)$timeSec;
    if ($time <= 0) {
        return '';
    }

    $hours = floor($time / (60 * 60));
    $time -= $hours * (60 * 60);

    $minutes = floor($time / 60);
    $time -= $minutes * 60;

    $seconds = floor($time);

    $seconds = $seconds < 10 ? ('0' . $seconds) : $seconds;
    $minutes = $minutes < 10 ? ('0' . $minutes) : $minutes;

    $result = "";


    switch ($format) {
        case "H:i":
            $result = "{$hours}:{$minutes}";
            break;
    }


    return $result;
}
