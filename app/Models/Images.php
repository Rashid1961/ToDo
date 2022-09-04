<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Images
{
    /**
     *  Загрузка изображения
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
        $image = $u . $l . $i;
        if (Storage::disk('images')->put($purpose . '/' . $image . '_img.jpg', (string)file_get_contents($file->getRealPath()))) {
            // создание и сохранение preview
            \Gregwar\Image\Image::open(public_path() . '/images/' . $purpose . '/' . $image . '_img.jpg')
                ->resize(150,150)
                ->save(public_path() . '/images/' . $purpose . '/preview/' . $image . '_preview.jpg');

            // корректируем таблицу
            $preview = '/images/' . $purpose . '/preview/' . $image . '_preview.jpg';
            $image = '/images/' . $purpose . '/' . $image. '_img.jpg';
            switch ($purpose) {
                case 'users':
                    DB::update("UPDATE users
                                SET image = ?,
                                    preview = ?
                                WHERE id = ?",
                               [$image, $preview, $uid]);
                    break;
                case 'lists':
                    DB::update("UPDATE lists
                                SET image = ?,
                                    preview = ?
                                WHERE id = ? AND id_user = ?",
                               [$image, $preview, $idList, $uid]);
                    break;
                case 'items':
                    DB::update("UPDATE items
                                SET image = ?,
                                    preview = ?
                                WHERE id = ? AND id_list = ?",
                               [$image, $preview, $idItem, $idList]);
                    break;
            };
        }
    }

    /**
     *  Удаление изображения
     */
    static function delImage($uid, $idList, $idItem)
    {
        $u = 'u' . substr(('00' . (string)$uid), -3);
        $l = '_l' . substr(('00' . (string)$idList), -3);
        $i = '_i' . substr(('00' . (string)$idItem), -3);

        if ($idList == 0 && $idItem == 0) { // Изображение пользователя
            $l = '';
            $i = '';
            $noImage = "noUserImage.jpg";
            $noPreview = "noUserPreview.jpg";
            $purpose = 'users';
        }
        else if ($idItem == 0) {            // Изображение списка
            $i = '';
            $noImage = "noListImage.jpg";
            $noPreview = "noListPreview.jpg";
            $purpose = 'lists';
        }
        else {                              // Изображение пункта
            $noImage = "noItemImage.jpg";
            $noPreview = "noItemPreview.jpg";
            $purpose = 'items';
        }

        // Удаляем файлы
        Storage::disk('images')->delete([
            $purpose . '/' . $u . $l . $i . '_img.jpg',
            $purpose . '/preview/' . $u . $l . $i . '_preview.jpg'
        ]);

        // корректируем таблицу
        $noImage = '/images/' . $purpose . '/' . $noImage;
        $noPreview = '/images/' . $purpose . '/preview/' . $noPreview;
        switch ($purpose) {
            case 'users':
                DB::update("UPDATE users
                            SET image = ?,
                                preview = ?
                            WHERE id = ?",
                           [$noImage, $noPreview, $uid]);
                break;
            case 'lists':
                DB::update("UPDATE lists
                            SET image = ?,
                                preview = ?
                            WHERE id = ? AND id_user = ?",
                           [$noImage, $noPreview, $idList, $uid]);
                break;
            case 'items':
                DB::update("UPDATE items
                            SET image = ?,
                                preview = ?
                            WHERE id = ? AND id_list = ?",
                           [$noImage, $noPreview, $idItem, $idList]);
                break;
        };
        return 1;
    }    
}