<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;

class Items
{
    /**
     *  Пункты списка
     */
    static function getItems($listId)
    {
        $items = [
            'items' => [],
            'tags'  => [],
        ];
        $item = [
            'id'      => '',
            'id_list' => '',
            'title'   => '',
            'image'   => '',
            'preview' => '',
            'tags'    => '',
        ];

        $rowsTags = DB::select(
            "SELECT *
             FROM tags
             ORDER BY id"
        );
        if (count($rowsTags) > 0) {
            foreach ($rowsTags as $row) {
                $items['tags'][$row->id] = $row->title;
            }
        }

        $rowsItems = DB::select(
            "SELECT
                i.id,
                i.id_list,
                i.title,
                i.image,
                i.preview,
                (
                    SELECT GROUP_CONCAT(id_tag)
                    FROM tags_items
                    WHERE id_item = i.id
                )  AS ids_tags
             FROM items AS i
             WHERE i.id_list = ?
             ORDER BY i.title",
            [$listId]
        );
        if (count($rowsItems) > 0) {
            foreach ($rowsItems as $row) {
                $i = array_push($items['items'], $item) - 1;
                $items['items'][$i]['id']       = $row->id;
                $items['items'][$i]['id_list']  = $row->id_list;
                $items['items'][$i]['title']    = $row->title;
                $items['items'][$i]['image']    = $row->image;
                $items['items'][$i]['preview']  = $row->preview;
                if ($row->ids_tags) {
                    $tags = explode(",", $row->ids_tags);
                    for ($j = 0; $j < count($tags); $j++) {
                        $items['items'][$i]['tags'] .= $items['tags'][$tags[$j]] . ' ';
                    }
                }
            }
        }

        return $items;
    }
    
    /**
     * Получение image и preview списка
     */
    static function getImgItem($itemId) {
        $images = [
            'image'   => '',
            'preview' => ''
        ];
        $row = DB::selectOne(
            "
                SELECT
                    i.image,
                    i.preview
                FROM items AS i
                WHERE i.id = ?
            ", [$itemId]
        );
        if ($row) {
            $images['image'] =$row->image;
            $images['preview'] =$row->preview;
        }
        return $images;
    }

    /**
     * Добавление пункта
     */
    static function appendItem($listId, $title, $image) {
        if(mb_strlen($title) < 5) {
            return -4;              // Длина наименования меньше 5 символов
        }
        $row = DB::selectOne(
            "
                SELECT
                    *
                FROM items AS i
                WHERE   i.id_list = ?
                    AND i.title = ?
            ", [$listId, $title]
        );
        if ($row) {
            return -3;              // Дублирование наименования списка
        }
        DB::insert(
            "
            INSERT INTO items
                (id_list, title, image)
            VALUES
                (?, ?, ?)
            ",            
            [$listId, $title, $image]
        );
        $row = DB::selectOne(
            "
            SELECT LAST_INSERT_ID() AS id
            "
        );
        return $row->id;
    }

    /**
     *  Изменение наименования пункта
     */
    static function changeTitleItem($listId, $itemId, $title) {
        $title = trim($title);
        if (mb_strlen($title) < 5) {
            return -4;              // Длина наименования меньше 5 символов
        }
        $rowDup = DB::selectOne(
            "
                SELECT
                    *
                FROM items AS i
                WHERE i.id_list = ?
                  AND i.title = ?
                  AND i.id != ?
            ", [$listId, $title, $itemId]
        );
        if ($rowDup) {
            return -3;      // Дублирование наименования
        }
        DB::update(
            "
            UPDATE items
            SET   title = ?
            WHERE id = ?
              AND id_list = ?
            ",
            [$title, $itemId, $listId]
        );
        return 0;
    }

    
    /**
     * Удаление пункта
     */
    static function deleteItem($listId, $itemId) {
        DB::delete(
            "
                DELETE FROM tags_items
                WHERE id_item = ?
            ", [$itemId]
        );
        DB::delete(
            "
                DELETE FROM items
                WHERE id_list = ?
                  AND id = ?
            ", [$listId, $itemId]
        );
        return 0;
    }

    /**
     * Изменение тегов пункта
     */
    static function changeTagsItem($itemId, $tags) {
        if (strlen($tags) == 0) {
            DB::delete(
                "
                    DELETE FROM tags_items
                    WHERE id_item = ?
                ", [$itemId]
            );
            return 0;
        }
        $arrCurTags = [];
        $rows = DB::select(
            "
                SELECT ti.id_tag
                FROM tags_items AS ti
                WHERE ti.id_item = ?
            ", [$itemId]
        );
        if ($rows) {
            foreach ($rows as $row) {
                $arrCurTags[] = $row->id_tag;
            }
        }

        $arrNewTags = explode('#', $tags);

        for ($i = count($arrNewTags) -1; $i >= 0; $i--) {
            $curTag = '#' . trim($arrNewTags[$i]);
            if ( strlen($curTag) == 1) {
                continue;
            }
            $row = DB::selectOne(
                "
                    SELECT t.id
                    FROM tags AS t
                    WHERE t.title = ?
                ", [$curTag]
            );
            if ($row) {
                $idCurTag = $row->id;
                if ($key = array_search($idCurTag, $arrCurTags) !== false) {
                    unset($arrCurTags[$key]);
                    continue;
                }
            }
            else {                  //Нет такого тега в справочнике - добавляем
                DB::insert(
                    "
                    INSERT INTO tags
                        (title)
                    VALUES
                        (?)
                    ",            
                    [$curTag]
                );
                $rowNewTag = DB::selectOne(
                    "
                    SELECT LAST_INSERT_ID() AS id
                    "
                );
                $idCurTag = $rowNewTag->id;
            }
            DB::insert(
                "
                INSERT INTO tags_items
                    (id_tag, id_item)
                VALUES
                    (?, ?)
                ",            
                [$idCurTag, $itemId]
            );
        }
        
        // Удаляем "лишние" теги
        if (count($arrCurTags) > 0 ) {
            DB::delete(
            "
                DELETE FROM tags_items
                WHERE id_item = ?
                  AND id_tag in ("  . implode(',', $arrCurTags) . ")",
                [$itemId]
            );
        }
    
        return 0;
    }
}
