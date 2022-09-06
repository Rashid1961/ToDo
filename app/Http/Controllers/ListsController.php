<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Lists;

class ListsController extends Controller
{
    // Получение списков пользователя
    public function getLists() {
        $uid = auth()->user()->id;
        return Lists::getLists($uid);
    }

    // Переименование списка
    public function changeTitleList(Request $request) {
        $uid = auth()->user()->id;
        $listid = $request->input('listid', 0);
        $titleList = $request->input('listtitle', '');
        return Lists::changeTitleList($uid, $listid, $titleList);
    }

    // Удаление списка
    public function deleteList(Request $request) {
        $uid = auth()->user()->id;
        $listid = $request->input('listid', 0);
        return Lists::deleteList($uid, $listid);
    }

    // Добавление списка
    public function appendList(Request $request) {
        $uid = auth()->user()->id;
        $title = $request->input('title', '');
        $image = $request->input('image', '');
        return Lists::appendList($uid, $title, $image);
    }

    // Получение image и preview списка
    public function getImgList(Request $request) {
        $listId = $request->input('listId', '');
        return Lists::getImgList($listId);
    }
}