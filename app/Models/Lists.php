<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Lists
{
    /**
     *  Списки пользователя и количество пунктов в них
     */
    static function getUserLists($uid)
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
    static function changeTitleList ($uid, $listid, $titleList) {
        $retVal = 0;
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
                    WHERE id=?
                    ",
                    [$titleList, $listid]
                );
            }
            else {
                $retVal = -1; // Список не принадлежит пользователю $uid
            }
        }
        else {
            $retVal = -2; // Список отсутствует в таблице lists
        }
        return $retVal;
    }
}
