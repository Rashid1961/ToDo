<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Items;

class ItemsController extends Controller
{
    // Получение пунктов списка
    public function getItems(Request $request){
        $listId = $request->input('listid', 0);
        return Items::getItems($listId);
    }

    // Получение image и preview пункта
    public function getImgItem(Request $request) {
        $itemId = $request->input('itemId', '');
        return Items::getImgItem($itemId);
    }

    // Добавление пункта
    public function appendItem(Request $request) {
        $listId = $request->input('listId', '');
        $title  = $request->input('title', '');
        $image  = $request->input('image', '');
        return Items::appendItem($listId, $title, $image);
    }

    // Переименование пункта
    public function changeTitleItem(Request $request) {
        $listId    = $request->input('listid', 0);
        $itemId    = $request->input('itemid', 0);
        $title = $request->input('itemtitle', '');
        return Items::changeTitleItem($listId, $itemId, $title);
    }

    // Удаление пункта
    public function deleteItem(Request $request) {
        $listId = $request->input('listid', 0);
        $itemId = $request->input('itemid', 0);
        return Items::deleteItem($listId, $itemId);
    }

    // Удаление пункта
    public function changeTagsItem(Request $request) {
        $itemId = $request->input('itemid', 0);
        $tags = $request->input('tags', 0);
        return Items::changeTagsItem($itemId, $tags);
    }
}
