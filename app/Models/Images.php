<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;

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
            $purpose = 'lists';
        }
        else {                              // Изображение пункта
            $purpose = 'items';
        }
        $fname = $u . $l . $i;
        if (Storage::disk('images')->put($purpose . '/' . $fname . '_img.jpg', (string)file_get_contents($file->getRealPath()))) {
            // preview
            $img = new Image();
            //print_r('public/images/' . $purpose . '/' . $fname . '_img.jpg');
            $img->make(public_path() . '/images/' . $purpose . '/' . $fname . '_img.jpg');
            $img->resize(150, 150);
            $img->save('public/images' . $purpose . '/preview/' . $fname . '_preview.jpg');

            // сохраняем в базу
            $fname = '/images/' . $fname;
            switch ($purpose) {
                case 'users':
                    DB::update("UPDATE users
                                SET image = ?
                                WHERE id = ?",
                               [$fname, $uid]);
                    break;
                case 'lists':
                    DB::update("UPDATE lists
                                SET image = ?
                                WHERE id = ? AND id_user = ?",
                               [$fname, $idList, $uid]);
                    break;
                case 'items':
                    DB::update("UPDATE items
                                SET image = ?
                                WHERE id = ? AND id_list = ?",
                               [$fname, $idItem, $idList]);
                    break;
            };
        }
    }
}