<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;

class Lists
{
    /**
     *  Списки пользователя и количество пунктов в них
     */
    static function getLists($uid)
    {
        $rows = DB::select(
            "
                SELECT
                    l.id,
                    l.title,
                    l.image,
                    l.preview,
                    (
                        SELECT
                            count(*)
                        FROM items AS i
                        WHERE i.id_list = l.id
                    ) AS number_items
                FROM lists AS l
                WHERE l.id_user = ?
                ORDER BY l.title
            ", [$uid]
        );

        if (count($rows) == 0) return [];
        else return $rows;
    }

    /**
     *  Один список пользователя и количество пунктов в нём
     */
    static function getOneList($uid, $idList)
    {
        $row = DB::selectOne(
            "
                SELECT
                    l.id,
                    l.title,
                    l.image,
                    l.preview,
                    (
                        SELECT
                            count(*)
                        FROM items AS i
                        WHERE i.id_list = l.id
                    ) AS number_items
                FROM lists AS l
                WHERE l.id_user = ?
                  AND l.id = ?
                ORDER BY l.title
            ", [$uid, $idList]
        );

        return ($row ? $row : []);
    }

    /**
     *  Изменение наименования списка
     */
    static function changeTitleList ($uid, $idList, $titleList) {
        $titleList = trim($titleList);
        if (mb_strlen($titleList) < 5) {
            return -4;              // Длина наименования (меньше 5 символов)
        }
        $row = DB::selectOne(
            "
                SELECT
                    *
                FROM lists AS l
                WHERE l.id = ?
            ", [$idList]
        );
        if ($row) {
            if ($row->id_user == $uid) {
                $rowDup = DB::selectOne(
                    "
                        SELECT
                            *
                        FROM lists AS l
                        WHERE l.id_user = ?
                          AND l.title = ?
                          AND l.id != ?

                    ", [$uid, $titleList, $idList]
                );
                if ($rowDup) {
                    return -3;      // Дублирование наименования списка
                }
        
                DB::update(
                    "
                    UPDATE lists
                    SET   title = ?
                    WHERE id = ?
                    ",
                    [$titleList, $idList]
                );
                return 0;
            }
            else {
                return -1;          // Список не принадлежит пользователю $uid
            }
        }
        return -2;          // Список отсутствует в таблице lists
    }

    /**
     * Удаление списка
     */
    static function deleteList ($uid, $idList) {
        $row = DB::selectOne(
            "
                SELECT
                    *
                FROM lists AS l
                WHERE   l.id = ?
            ", [$idList]
        );
        if ($row) {
            if ($row->id_user == $uid) {
                DB::delete(
                    "
                        DELETE FROM tags_items
                        WHERE id_item IN
                            (
                                SELECT id FROM items AS i
                                WHERE id_list = ?
                            )
                    ", [$idList]
                );
                DB::delete(
                    "
                        DELETE FROM items
                        WHERE id_list = ?
                    ", [$idList]
                );
                DB::delete(
                    "
                        DELETE FROM lists
                        WHERE id = ?
                    ", [$idList]
                );
                return 0;
            }
            else {
                return -1;          // Список не принадлежит пользователю $uid
            }
        }
        return -2;                  // Список отсутствует в таблице lists
    }

    /**
     * Добавление списка
     */
    static function appendList ($uid, $titleList, $image) {
        if(mb_strlen($titleList) < 5) {
            return -4;              // Длина наименования (меньше 5 символов)
        }
        $row = DB::selectOne(
            "
                SELECT
                    *
                FROM lists AS l
                WHERE   l.id_user = ?
                    AND l.title = ?
            ", [$uid, $titleList]
        );
        if ($row) {
            return -3;              // Дублирование наименования списка
        }
        DB::insert(
            "
            INSERT INTO lists
                (id_user, title, image)
            VALUES
                (?, ?, ?)
            ",            
            [$uid, $titleList, $image]
        );
        $row = DB::selectOne(
            "
            SELECT LAST_INSERT_ID() AS id
            "
        );
        return $row->id;
    }

    /**
     * Получение image и preview списка
     */
    static function getImgList($idList) {
        $images = [
            'image'   => '',
            'preview' => ''
        ];
        $row = DB::selectOne(
            "
                SELECT
                    l.image,
                    l.preview
                FROM lists AS l
                WHERE l.id = ?
            ", [$idList]
        );
        if ($row) {
            $images['image'] =$row->image;
            $images['preview'] =$row->preview;
        }
        return $images;
    }
}
