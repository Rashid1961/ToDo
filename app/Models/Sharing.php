<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\Users;

class Sharing
{
    /**
     * Пункт/пункты списка/списков, расшаренные для других пользователей
     * 
     * @param $idUser  id пользователя, чьи пункты расшарены 
     * @param $idList  id списка пользователя (0 - все списки, значение $idItem неважно)
     * @param $idItem  id пункта списка (имеет смысл только если указан конкретный id списка, 0 - все пункты списка)
     * 
     */
    static function sharedItemsForOther($idUser, $idList, $idItem)
    {
        // Массив расшаренных пунктов списков
        $sharedItems = [
            'id_list' => 0,    // id списка
            'items'  => []     // Расшаренные пункты списка
        ];

        // Шаблон одного расшаренного пункта
        $item = [
            'id_item' => 0,         // id пункта
            'readers' => [          // Пользователи, для которых расшарен пункт списка
                'id_user' => 0,     // id пользователя
                'name'  => '',      // имя пользователя
            ],
        ];

        if ($idUser == 0) { // Не определён пользователь
            return $sharedItems;
        }

        $where = '';
        if ($idList > 0) {
            $where .= ' AND id_list=' . $idList;
            if ($idItem > 0) {
                $where .= ' AND id_item=' . $idItem;
            }
        }

        $rows = DB::select(
            "
                SELECT
                    id_user_reader,
                    id_list,
                    id_item
                FROM shared_items 
                WHERE id_user_owner = ?" . $where . "
                ORDER BY id_list, id_item, id_user_reader
            ",
            [$idUser]
        );

        if ($rows) {
            $curIdList = $curIdItem = -1;
            $users = Users::getUsers(0);
            foreach ($rows as $row) {
                if ($row->id_list != $curIdList) {
                    $idxList = array_push($sharedItems, ['id_list' => $row->id_list, 'items' => $item]) - 1;
                    $curIdItem = -1;
                }
                if ($row->id_item != $curIdItem) {
                    $idxItem = array_push($sharedItems[$idxList]['items'], ['id_item' => $row->id_item, 'readers' =>[]]) - 1;
                }

            }
        }

        return $sharedItems;
    }

    /**
     * Пункты списков, расшаренные для пользователя
     * 
     * @param $idUser  id пользователя 
     * 
     */
    static function getSharedForUser($idUser)
    {
        // Массив расшаренных пунктов списков
        $sharedItems = [
            'list'  => [    // Расшаренные списки
                'id'      => 0,     // id списка
                'title'   => '',    // Наименование списка
                'image'   => '',    // Спецификация файла с изображением списка
                'preview' => '',    // Спецификация файла с превью списка
                'owner'   => [      // Пользователь, расшаривший пункты списка
                    'id'   => 0,        // id пользователя
                    'name' => '',       // Имя пользователя
                ],
            ],
            'items' => [    // Расшаренные пункты списка
            ],
        ];

        // Шаблон одного расшаренного пункта
        $item = [
            'id'      => 0,     // id пункта
            'title'   => '',    // Наименование пункта
            'image'   => '',    // Спецификация файла с изображением пункта
            'preview' => '',    // Спецификация файла с превью пункта
        ];

        if ($idUser == 0) { // Не определён пользователь
            return $sharedItems;
        }

        $rows = DB::select(
            "
                SELECT
                    si.id_user_owner,
                    si.id_list,
                    si.id_item,
                    u.name,
                    l.title     AS title_list,
                    l.image     AS image_list,
                    l.preview   AS preview_list,
                FROM shared_items   AS si
                    LEFT JOIN users AS u  ON u.id = si.id_user_owner
                    LEFT JOIN lists AS l  ON l.id = si.id_list
                    LEFT JOIN items AS i  ON i.id = si.id_item
                WHERE si.id_user_reader = ?
                ORDER BY si.id_user_reader, si.id_list, si.id_item
            ",
            [$idUser]
        );

    }

}