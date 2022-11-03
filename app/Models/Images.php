<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Images
{
    /**
     *  Загрузка изображения
     * 
     * @param $idUser  id пользователя
     * @param $file загружаемый файл
     * @param $idList id списка (0 - для изображения пользователя)
     * @param $idItem id пункта списка (0 - для изображений пользователя и списка)
     * 
     * Структура имени формируемых файлов изображения и preview:
     *      uUUU_vVVV.jpg           - для пользователя
     *      uUUU_lLLL_vVVV.jpg      - для списка
     *      uUUU_lLLL_iIII_vVVV.jpg - для пункта списка
     *          UUU - id пользователя
     *          LLL - id списка
     *          III - id пункта
     *          VVV - версия изображения
     * 
     * @return 0/1
     */
    static function uploadImage($idUser, $file, $idList, $idItem)
    {
        if ($idUser == 0) { // Не определён пользователь
            return 0;
        }

        $user = 'u'  . substr(('00' . (string)$idUser), -3);                          // id пользователя
        $list = $idList == 0 ? '' : ('_l' . substr(('00' . (string)$idList), -3)); // id списка
        $item = $idItem == 0 ? '' : ('_i' . substr(('00' . (string)$idItem), -3)); // id пункта
        $vers = '_v000';                                                           // версия

        $table = '';
        $where = '';
        $params = [];
        $noImage = '';
        $noPreview = '';
        $oldImage = '';
        $oldPrewview = '';
        
        // Определение параметров в зависимости от типа загружаемого изображения (Плоьзователь, Список, Пункт)
        if ($idList == 0 && $idItem == 0) { // Пользователь
            $noImage = "/images/users/noUserImage.jpg";
            $noPreview = "/images/users/preview/noUserPreview.jpg";
            $table = 'users';
            $where = 'id = ?';
            $params = [$idUser];
        }
        else if ($idItem == 0) {            // Список
            $noImage = "/images/lists/noListImage.jpg";
            $noPreview = "/images/lists/preview/noListPreview.jpg";
            $table = 'lists';
            $where = 'id = ? AND id_user = ?';
            $params = [$idList, $idUser];
        }
        else {                              // Пункт
            $noImage = "/images/items/noItemImage.jpg";
            $noPreview = "/images/items/preview/noItemPreview.jpg";
            $table = 'items';
            $where = 'id = ? AND id_list = ?';
            $params = [$idItem, $idList];
        }

        // Проверка наличия текущих изображения и preview (для определения новой версии и имён файлов для удаления)
        $row = DB::selectOne(
            "
                SELECT
                    image, preview
                FROM {$table}
                WHERE {$where}
            ", $params
        );

        // Определение номера новой версии, если есть запись о текущих изображении и preview 
        if ($row) {
            $oldImage = $row->image;
            $oldPrewview = $row->preview;
            $i = strpos($oldImage, '_v');
            if ($i !== false) {
                $newVersion = substr($oldImage, $i + 2, 3) + 1;
                if ($newVersion == 999) {
                    $newVersion = 0;
                }

                $vers = '_v' . substr(('00' . (string)$newVersion), -3);
            }
        }

        $image = $user . $list . $item . $vers . '.jpg';  // Формирование имени файла изображения и preview
        // Сохранение изображения
        if (Storage:: disk('images')->put('/' . $table . '/' . $image, (string)file_get_contents($file->getRealPath()), 'public')) {
            // Создание и сохранение preview
            \Gregwar\Image\Image::open(public_path() . '/images/' . $table . '/' . $image)
                ->resize(150,150)
                ->save(public_path() . '/images/' . $table . '/preview/' . $image);

            // Корректировка полей image и preview таблицы $table
            array_unshift($params, '/images/' . $table . '/' . $image, '/images/' . $table . '/preview/' . $image);
            DB::update(
                "
                    UPDATE {$table}
                    SET image = ?, preview = ?
                    WHERE {$where}
                ", $params
            );
        }

        // Удалeние старых изображения и preview
        if ($oldImage !== $noImage) {
            Storage:: disk('images')->delete(str_replace('/images', '', $oldImage));
        }

        if ($oldPrewview !== $noPreview) {
            Storage:: disk('images')->delete(str_replace('/images', '', $oldPrewview));
        }

        return 1;
    }

    /**
     *  Удаление изображения и preview
     * 
     * @param $idUser  id пользователя
     * @param $idList id списка (0 - для изображения пользователя)
     * @param $idItem id пункта списка (0 - для изображений пользователя и списка)
     * 
     */
    static function delImage($idUser, $idList, $idItem)
    {
        if ($idUser == 0) { // Не определён пользователь
            return 0;
        }

        $table = '';
        $where = '';
        $params = [];
        $needImageDelete = false;
        $needPreviewDelete = false;
        $noImage = '';
        $noPreview = '';
        $image = '';
        $preview = '';

        // Определение параметров в зависимости от типа загружаемого изображения (Плоьзователь, Список, Пункт)
        if ($idList == 0 && $idItem == 0) { // Пользователь
            $noImage = "/images/users/noUserImage.jpg";
            $noPreview = "/images/users/preview/noUserPreview.jpg";
            $table = 'users';
            $where = 'id = ?';
            $params = [$idUser];
        }
        else if ($idItem == 0) {            // Список
            $noImage = "/images/lists/noListImage.jpg";
            $noPreview = "/images/lists/preview/noListPreview.jpg";
            $table = 'lists';
            $where = 'id = ? AND id_user = ?';
            $params = [$idList, $idUser];
        }
        else {                              // Пункт
            $noImage = "/images/items/noItemImage.jpg";
            $noPreview = "/images/items/preview/noItemPreview.jpg";
            $table = 'items';
            $where = 'id = ? AND id_list = ?';
            $params = [$idItem, $idList];
        }

        // Проверка наличия текущих изображения и preview (для определения имён файлов для удаления)        
        $row = DB::selectOne(
            "
                SELECT image, preview
                FROM {$table} 
                WHERE {$where}
            ", $params
        );

        if ($row) {
            if ($row->image !== $noImage) {
                $needImageDelete = true;
                $image = str_replace('/images', '', $row->image);
            }

            if ($row->preview !== $noPreview) {
                $needPreviewDelete = true;
                $preview = str_replace('/images', '', $row->preview);
            }
        }
        else {
            return 0;
        }

        // Удаление изображения
        if ($needImageDelete) {
            Storage:: disk('images')->delete($image);
        }

        // Удаление preview
        if ($needPreviewDelete) {
            Storage:: disk('images')->delete($preview);
        }

        // Корректировка полей image и preview таблицы $table
        array_unshift($params, $noImage, $noPreview);
        DB::update(
            "
                UPDATE {$table}
                SET image = ?, preview = ?
                WHERE {$where}
            ", $params
        );

        return 1;
    }    
}