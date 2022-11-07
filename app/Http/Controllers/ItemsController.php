<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Lists;
use App\Models\Items;

class ItemsController extends Controller
{
    /**
     * Вывод пунктов списка
     * 
     * @param $idList  id списка
    */
    public function expandList($idList) { 
        $list = (array)Lists::getOneList(auth()->user()->id, $idList);
        $data = [
            'id'           => auth()->user()->id,
            'name'         => auth()->user()->name,
            'email'        => auth()->user()->email,
            'image'        => auth()->user()->image,
            'preview'      => auth()->user()->preview,
            'idList'       => $idList,
            'titleList'    => $list['title'],
            'number_items' => $list['number_items'],
        ];
        return view('items', $data);
    }

    /**
     * Получение пунктов списка
    */
    public function getItems(Request $request){
        $idList = $request->input('idList', 0);
        return Items::getItems($idList);
    }

    /**
     * Получение image и preview пункта
    */
    public function getImgItem(Request $request) {
        $idItem = $request->input('idItem', '');
        return Items::getImgItem($idItem);
    }

    /**
     * Добавление пункта
    */
    public function appendItem(Request $request) {
        $idList = $request->input('idList', '');
        $titleItem  = $request->input('title', '');
        $imageItem  = $request->input('image', '');
        return Items::appendItem($idList, $titleItem, $imageItem);
    }

    /**
     * Переименование пункта
    */
    public function changeTitleItem(Request $request) {
        $idList    = $request->input('idList', 0);
        $idItem    = $request->input('idItem', 0);
        $titleItem = $request->input('itemtitle', '');
        return Items::changeTitleItem($idList, $idItem, $titleItem);
    }

    /**
     * Удаление пункта
    */
    public function deleteItem(Request $request) {
        $idList = $request->input('idList', 0);
        $idItem = $request->input('idItem', 0);
        return Items::deleteItem($idList, $idItem);
    }

    /**
     * Изменение тегов пункта
    */
    public function changeTagsItem(Request $request) {
        $idItem = $request->input('idItem', 0);
        $tagsItem = $request->input('tags', 0);
        return Items::changeTagsItem($idItem, $tagsItem);
    }
}


