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
                    (
                        SELECT
                            count(*)
                        FROM items AS i
                        WHERE i.id_list = l.id
                    ) AS number_items
                FROM lists AS l
                WHERE l.id_user = ?
            ", [$uid]
        );

        if (count($rows) == 0) return [];
        else return $rows;
    }

    /**
     *  Изменение наименования списка
     */
    static function changeTitleList ($uid, $listid, $title) {
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
                DB::update(
                    "
                    UPDATE lists
                    SET   title = ?
                    WHERE id = ?
                    ",
                    [$title, $listid]
                );
                return 0;
            }
            else {
                return -1;  // Список не принадлежит пользователю $uid
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
                return -1;  // Список не принадлежит пользователю $uid
            }
        }
        return -2;          // Список отсутствует в таблице lists
    }

    /**
     * Добавление списка
     */
    static function appendList ($uid, $title, $image) {
        if (count($title) < 5) {
            return -4;      // Длина наименования (меньше 5 символов)
        }
        $row = DB::selectOne(
            "
                SELECT
                    *
                FROM lists AS l
                WHERE   l.id_user = ?
                    AND l.title = ?
            ", [$uid, $title]
        );
        if ($row) {
            return -3;      // Дублирование наименования списка
        }
        DB::insert(
            "
            INSERT INTO lists
                (id_user, title, image)
            VALUES
                (?, ?, ?)
            ",            
            [$uid, $title, $image]
        );
        $row = DB::selectOne(
            "
            SELECT LAST_INSERT_ID() AS id
            "
        );
        return $row->id;
    }    
}
