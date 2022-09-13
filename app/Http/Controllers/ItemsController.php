<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Items;

class ItemsController extends Controller
{
    // Вывод пунктов списка
    public function expandList($idList){
        //$id      = auth()->user()->id;
        //$name    = auth()->user()->name;
        //$email   = auth()->user()->email;
        //$image   = auth()->user()->image;
        //$preview = auth()->user()->preview;
        $data = [
            'id'      => auth()->user()->id,
            'name'    => auth()->user()->name,
            'email'   => auth()->user()->email,
            'image'   => auth()->user()->image,
            'preview' => auth()->user()->preview,
            'idList'  => $idList
        ];

        return view('items', $data); // [$idList, $id, $name, $email, $image, $preview]); //[$idList, Items::getItems($idList)]);
    }

    // Получение пунктов списка
    public function getItems(Request $request){
        $idList = $request->input('idList', 0);
        return Items::getItems($idList);
    }

    // Получение image и preview пункта
    public function getImgItem(Request $request) {
        $itemId = $request->input('itemId', '');
        return Items::getImgItem($itemId);
    }

    // Добавление пункта
    public function appendItem(Request $request) {
        $idList = $request->input('idList', '');
        $title  = $request->input('title', '');
        $image  = $request->input('image', '');
        return Items::appendItem($idList, $title, $image);
    }

    // Переименование пункта
    public function changeTitleItem(Request $request) {
        $idList    = $request->input('idList', 0);
        $itemId    = $request->input('itemid', 0);
        $title = $request->input('itemtitle', '');
        return Items::changeTitleItem($idList, $itemId, $title);
    }

    // Удаление пункта
    public function deleteItem(Request $request) {
        $idList = $request->input('idList', 0);
        $itemId = $request->input('itemid', 0);
        return Items::deleteItem($idList, $itemId);
    }

    // Удаление пункта
    public function changeTagsItem(Request $request) {
        $itemId = $request->input('itemid', 0);
        $tags = $request->input('tags', 0);
        return Items::changeTagsItem($itemId, $tags);
    }
}


