<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Sharing
{
    /**
     *  Удаление изображения и preview
     * 
     * @param $uid    id пользователя
     * @param $idList id списка (0 - все списки)
     * @param $idItem id пункта списка (0 - все пункты)
     * 
     */
    static function getWithWhomShared($uid, $idList, $idItem)
    {
        if ($uid == 0) { // Не определён пользователь
            return 0;
        }

        return 1;
    }    
}