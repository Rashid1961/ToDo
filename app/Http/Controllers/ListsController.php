<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Lists;
//use App\Models\Users;

class ListsController extends Controller
{
    // Получение списков пользователя
    public function getLists() {
        $idUser = auth()->user()->id;
        return Lists::getLists($idUser);
    }

    // Переименование списка
    public function changeTitleList(Request $request) {
        $idUser = auth()->user()->id;
        $idList = $request->input('idList', 0);
        $titleList = $request->input('listtitle', '');
        return Lists::changeTitleList($idUser, $idList, $titleList);
    }

    // Удаление списка
    public function deleteList(Request $request) {
        $idUser = auth()->user()->id;
        $idList = $request->input('idList', 0);
        return Lists::deleteList($idUser, $idList);
    }

    // Добавление списка
    public function appendList(Request $request) {
        $idUser = auth()->user()->id;
        $title = $request->input('title', '');
        $image = $request->input('image', '');
        return Lists::appendList($idUser, $title, $image);
    }

    // Получение image и preview списка
    public function getImgList(Request $request) {
        $idList = $request->input('idList', '');
        return Lists::getImgList($idList);
    }
}