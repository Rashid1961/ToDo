<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\Users;

class Sharing
{
    /**
     *  Удаление изображения и preview
     * 
     * Пользователи для которых расшарен пункт/пункты списка/списков
     * @param $idList  id списка пользователя (0 - все списки, значение $idItem неважно)
     * @param $idItem  id пункта списка (имеет смысл только если указан конкретный id списка, 0 - все пункты списка)
     * 
     */
    static function getForWhomShared($idUser, $idList, $idItem)
    {
        // Массив расшаренных списков
        $sharedItems = [
            'id_list' => 0,    // id списка
            'items'  => []     // Расшаренные пункты списка
        ];

        // Шаблон одного расшаренного пункта
        $item = [
            'id_item' => 0,     // id пункта
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
                SELECT id_user_reader, id_list, id_item
                FROM shared_items 
                WHERE id_user_owner=?" . $where . "
                ORDER BY id_list, id_item, id_user_reader
            "
            , [$idUser]
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
}