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
        $lists = [];

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
                    ) AS number_items,              # Количество пунктов в списке
                    (
                        SELECT
                            MIN(sii.id_item)
                        FROM shared_items AS sii
                        WHERE sii.id_user_owner = l.id_user
                          AND sii.id_list  = l.id
                    ) AS shared_items,              # Есть ли расшаренные пункты в списке (если вернёт 0 - расшарен весь список)
                    (
                        SELECT
                            MIN(sir.id_user_reader)
                        FROM shared_items AS sir
                        WHERE sir.id_user_owner = l.id
                          AND sir.id_list = l.id
                    ) AS shared_for_users           # Рашарено ли для пользователей (если вернёт 0 - расшарен для всех пользователей)
                FROM lists AS l
                WHERE l.id_user = ?
                ORDER BY l.title
            ", [$uid]
        );

        if ($rows) {
            foreach ($rows as $row) {
                $lists[] = [
                    'id'               => $row->id,
                    'title'            => $row->title,
                    'image'            => $row->image,
                    'preview'          => $row->preview,
                    'number_items'     => $row->number_items,   // Количество пунктов в списке
                    'shared_items'     => (is_null($row->shared_items) ? -1 :               // -1 нет расшаренных пунктов
                                                                       $row->shared_items), // есть расшаренные пункты (если 0 - расшарен весь список)
                    'shared_for_users' => (is_null($row->shared_for_users) ? -1 :                       // -1 не расшарено для пользователей
                                                                             $row->shared_for_users),   // есть для пользователей (если 0 - для всех пользователей)
                    // если $lists[i]['shared_items'
                ];
            }
        }

        return $lists;
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
                    ", [$titleList, $idList]
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
                // Удаленеие "ссылок" на теги всех пунктов удаляемого списка
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

                // Удаленеие всех пунктов удаляемого списка
                DB::delete(
                    "
                        DELETE FROM items
                        WHERE id_list = ?
                    ", [$idList]
                );

                // Удаленеие списка
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

        // Добавление списка
        DB::insert(
            "
                INSERT INTO lists
                    (id_user, title, image)
                VALUES
                    (?, ?, ?)
            ",            
            [$uid, $titleList, $image]
        );

        // Получение id добавленного списка
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
