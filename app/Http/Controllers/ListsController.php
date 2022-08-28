<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Lists;

class ListsController extends Controller
{
    function action(Request $request)
    {
        $uid = auth()->user()->id;
        $action = $request->action;

        if (!$action) $action = $request->input('action');

        // Получение списков пользователя
        if ($action === 'getLists') {
            return Lists::getLists($uid);
        }

        // Переименование списка
        if ($action === 'changeTitleList') {
            $listid = $request->input('listid');
            $titleList = $request->input('listtitle');
            return Lists::changeTitleList($uid, $listid, $titleList);
        }

        // Удаление списка
        if ($action === 'deleteList') {
            $listid = $request->input('listid');
            return Lists::deleteList($uid, $listid);
        }

        // Добавление списка
        if ($action === 'appendList') {
            $title = $request->input('title');
            $image = $request->input('image');
            return Lists::appendList($uid, $title, $image);
        }
        

        return 'Действие "' . $action . '" не найдено';
    }
    
}