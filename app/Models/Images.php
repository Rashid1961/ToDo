<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Images
{
    /**
     *  Списки пользователя и количество пунктов в них
     */
    static function uploadImage($uid, $file, $idList, $idItem)
    {
        $u = 'u' . substr(('00' . (string)$uid), -3);
        $l = '_l' . substr(('00' . (string)$idList), -3);
        $i = '_i' . substr(('00' . (string)$idItem), -3);
        if ($idList == 0 && $idItem == 0) { // Изображение пользователя
            $l = '';
            $i = '';
            $purpose = 'users';
        }
        else if ($idItem == 0) {            // Изображение списка
            $i = '';
            $purpose = 'lists/';
        }
        else {                              // Изображение пункта
            $purpose = 'items/';
        }
        $fname = $purpose . '/' . $u . $l . $i . '_img.jpg';
        if (Storage::disk('images')->put($fname, (string)file_get_contents($file->getRealPath()))) {
            switch ($purpose) {
                case 'users':
                    DB::update("UPDATE users
                                SET image = ?,
                                WHERE id = ?
                               ",["/" . $fname, $uid]);
                    break;
                case 'lists':
                    DB::update(
                        "
                        UPDATE lists
                        SET image = ?,
                        WHERE id = ?
                          AND id_user = ?
                        ",
                        ["/" . $fname, $idList, $uid]
                    );
                    break;
                case 'items':
                    DB::update(
                        "
                        UPDATE items
                        SET image = ?,
                        WHERE id = ?
                          AND id_list = ?
                        ",
                        ["/" . $fname, $idItem, $idList]
                    );
                    break;
            };
        }
    }
}