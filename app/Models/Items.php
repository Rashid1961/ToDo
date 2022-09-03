<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Lists
{
    /**
     *  Списки пользователя и количество пунктов в них
     */
    static function getLists($lisId)
    {
        $items = [
            'items' => [],
            'tags'  => [],
        ];
        $rowsItems = DB::select(
            "
                SELECT
                    i.id_list,
                    i.id,
                    i.title,
                    i.image,
                    (
                        SELECT GROUP_CONCAT(id_tag)
                        FROM tags_items
                        WHERE id_item = i.id AND
                    )  AS ids_tags
                FROM items AS i
                WHERE i.id_list = ?
                ORDER BY i.title
            ", [$lisId]
        );
        if (count($rowsItems) > 0) {
        }

        $rowsTags = DB::select(

        );
        if (count($rowsTags) > 0) {
        }

        return $items;
    }
    
    /**
     *  Изменение наименования списка
     */
    static function changeTitleList ($uid, $listid, $titleList) {
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
            ", [$listid]
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

                    ", [$uid, $titleList]
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
                    [$titleList, $listid]
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
    static function deleteList ($uid, $listid) {
        $row = DB::selectOne(
            "
                SELECT
                    *
                FROM lists AS l
                WHERE   l.id = ?
            ", [$listid]
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
                    ", [$listid]
                );
                DB::delete(
                    "
                        DELETE FROM items
                        WHERE id_list = ?
                    ", [$listid]
                );
                DB::delete(
                    "
                        DELETE FROM lists
                        WHERE id = ?
                    ", [$listid]
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
}
