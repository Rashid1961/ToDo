<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;

class Lists
{
    /**
     *  Списки пользователя и 
     *  расшаренных другими пользователями
     * 
     * @param $idUser  id пользователя
     */
    static function getLists($idUser)
    {
        $lists = [];

        $rows = DB::select(
            "
                (       #  Собственные списки
                    SELECT
                        '0'         AS reader,
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
                        ) AS shared_for_users           # Расшарено ли для пользователей (если вернёт 0 - расшарен для всех пользователей)
                    FROM lists AS l
                    WHERE l.id_user = ?
                    ORDER BY l.title
                )
                UNION
                (       #  Списки, расшаренные другими пользователями
                    SELECT
                        '1'         AS reader,
                        l.id,
                        l.title,
                        l.image,
                        l.preview,
                        '0'         AS number_items,              #
                        '0'         AS shared_items,              # Для расшаренных не используется
                        '0'         AS shared_for_users           #
                        FROM lists AS l
                            INNER JOIN shared_items AS si ON si.id_user_reader = ?
                                                          OR (si.id_user_reader = 0 AND si.id_user_reader != ?)
                )
            ", [$idUser, $idUser, $idUser]
        );

        if ($rows) {
            foreach ($rows as $row) {
                $lists[] = [          // Массив списков пользователя и расшаренных другими пользователями
                    'reader'           => $row->reader,                              // 0 - владелец списка, 1 - "читатель" (в списке есть расшаренные для пользователя пункты)
                    'id'               => $row->id,                                  // id списка
                    'title'            => $row->title,                               // наименование списка
                    'image'            => $row->image,                               // изображение списка
                    'preview'          => $row->preview,                             // preview списка
                    'number_items'     => $row->number_items,                        // Количество пунктов в списке
                    'shared_items'     => (is_null($row->shared_items) ? 0 : 1),     // 0 - нет расшаренных пунктов; 1 - есть
                    'shared_for_users' => (is_null($row->shared_for_users) ? 0 : 1), // 0 - нет пользователей "читателей"; 1 - есть
                ];
            }
        }

        return $lists;
    }

    /**
     *  Один список пользователя и количество пунктов в нём
     * 
     * @param $idUser  id пользователя
     * @param $idList  id списка
     */
    static function getOneList($idUser, $idList)
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
            ", [$idUser, $idList]
        );

        return ($row ? $row : []);
    }

    /**
     *  Изменение наименования списка
     * 
     * @param $idUser     id пользователя
     * @param $idList     id списка
     * @param $titleList  новое наименование списка
     */
    static function changeTitleList ($idUser, $idList, $titleList) {
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
            if ($row->id_user == $idUser) {
                $rowDup = DB::selectOne(
                    "
                        SELECT
                            *
                        FROM lists AS l
                        WHERE l.id_user = ?
                          AND l.title = ?
                          AND l.id != ?

                    ", [$idUser, $titleList, $idList]
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
                return -1;          // Список не принадлежит пользователю $idUser
            }
        }
        return -2;          // Список отсутствует в таблице lists
    }

    /**
     * Удаление списка
     * 
     * @param $idUser  id пользователя
     * @param $idList  id списка
     */
    static function deleteList ($idUser, $idList) {
        $row = DB::selectOne(
            "
                SELECT
                    *
                FROM lists AS l
                WHERE   l.id = ?
            ", [$idList]
        );
        if ($row) {
            if ($row->id_user == $idUser) {
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
                return -1;          // Список не принадлежит пользователю $idUser
            }
        }
        return -2;                  // Список отсутствует в таблице lists
    }

    /**
     * Добавление списка
     * 
     * @param $idUser     id пользователя
     * @param $titleList  наименование списка
     * @param $imageList  спецификация файла с изображением списка
     */
    static function appendList ($idUser, $titleList, $imageList) {
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
            ", [$idUser, $titleList]
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
            [$idUser, $titleList, $imageList]
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
     * 
     * @param $idList  id списка
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
