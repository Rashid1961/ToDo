<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;

class Items
{
    /**
     *  Пункты списка
     */
    static function getItems($idList)
    {
        // Массив пунктов списка тегов, привязанных к ним
        $items = [
            'items' => [],
            'tags'  => [],  // $items['tags']['id_tag'] = title_tag
        ];

        // Шаблон одного элемента массива $items['items']
        $item = [
            'id'      => '',    // id пункта
            'id_list' => '',    // id списка
            'title'   => '',    // наименование  пункта
            'image'   => '',    // изображение пункта
            'preview' => '',    // preview пункта
            'ids_tag' => [      // массив тегов пункта списка
                'id'    => 0,
                'name'  => '',
            ],
            'tags'    => '',    // строка со списком тегов пункта списка
        ];

        // Формирование массива $items['tags'] - наименования всех тегов
        $rowsTags = DB::select(
            "
                SELECT
                    t.id, t.title
                FROM tags AS t
                ORDER BY t.id
            "
        );
        if (count($rowsTags) > 0) {
            foreach ($rowsTags as $row) {
                $items['tags'][$row->id] = $row->title;
            }
        }

        // Формирование массива $items['items'] - информация о пунктах списка
        $rowsItems = DB::select(
            "
                SELECT
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
                ORDER BY i.title
            ", [$idList]
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
                    $items['items'][$i]['ids_tag']['id'] = explode(",", $row->ids_tags);
                    for ($j = 0; $j < count($items['items'][$i]['ids_tag']['id']); $j++) {
                        $items['items'][$i]['tags'] .= $items['tags'][$items['items'][$i]['ids_tag']['id'][$j]] . ' ';
                        $items['items'][$i]['ids_tag']['name'][] = $items['tags'][$items['items'][$i]['ids_tag']['id'][$j]] . ' ';
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
    static function appendItem($idList, $title, $image) {
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
            ", [$idList, $title]
        );
        if ($row) {
            return -3;              // Дублирование наименования списка
        }

        // Добавление пункта списка
        DB::insert(
            "
                INSERT INTO items
                    (id_list, title, image)
                VALUES
                    (?, ?, ?)
            ",            
            [$idList, $title, $image]
        );

        // Получение id добавленного пункта списка
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
    static function changeTitleItem($idList, $itemId, $title) {
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
            ", [$idList, $title, $itemId]
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
            ", [$title, $itemId, $idList]
        );
        return 0;
    }
    
    /**
     * Удаление пункта
     */
    static function deleteItem($idList, $itemId) {
        // Удаленеие "ссылок" на теги пункта
        DB::delete(
            "
                DELETE FROM tags_items
                WHERE id_item = ?
            ", [$itemId]
        );

        // Удаление пункта
        DB::delete(
            "
                DELETE FROM items
                WHERE id_list = ?
                  AND id = ?
            ", [$idList, $itemId]
        );
        return 0;
    }

    /**
     * Изменение тегов пункта
     */
    static function changeTagsItem($itemId, $tags) {
        // Удаленеие "ссылок" на теги пункта, если новый список тегов пустой
        if (strlen($tags) == 0) {
            DB::delete(
                "
                    DELETE FROM tags_items
                    WHERE id_item = ?
                ", [$itemId]
            );
            return 0;
        }

        // Формирование массива текущих id тегов пункта
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

        // Преобразование нового списка наименований тегов в массив
        $arrNewTags = explode('#', $tags);

        // Обход массива нового списка тегов
        for ($i = count($arrNewTags) -1; $i >= 0; $i--) {
            $curNewTag = '#' . trim($arrNewTags[$i]);

            // Пустой тег
            if ( strlen($curNewTag) == 1) {
                continue;
            }

            // Проверка наличия тега в справочнике тегов
            $row = DB::selectOne(
                "
                    SELECT t.id
                    FROM tags AS t
                    WHERE t.title = ?
                ", [$curNewTag]
            );

            if ($row) {             // Тег есть в справвочнике
                $idCurTag = $row->id;
                $conti = false;
                for ($j = 0; $j < count($arrCurTags); $j++) {
                    if ($arrCurTags[$j] == $idCurTag) {  // Новый тег есть в массиве текущих
                        unset($arrCurTags[$j]);          // удаление его из массива текущих
                        $conti = true;                   // т.е. его не надо будет удалять
                    }
                }
                if ($conti) {
                    continue;
                }
            }
            else {                  // Если нет такого тега в справочнике - добавление (значит, и среди текущих его тоже нет)
                // Добавление тега в справочник
                DB::insert(
                    "
                        INSERT INTO tags
                            (title)
                        VALUES
                            (?)
                    ",            
                    [$curNewTag]
                );
                $rowNewTag = DB::selectOne(
                    "
                        SELECT LAST_INSERT_ID() AS id
                    "
                );
                $idCurTag = $rowNewTag->id;
            }

            // Добавленеие "ссылки" на тег для пункта
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
        
        // Удаляем "лишние" теги, оставщиеся в массиве текущих тегов
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
