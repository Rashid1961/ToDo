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
        if ($action === 'getUserLists') {
            return Lists::getUserLists($uid);
        }

        // Переименование списка
        if ($action === 'changeTitleList') {
            $listid = $request->input('listid');
            $titleList = $request->input('listtitle');
            return Lists::changeTitleList($uid, $listid, $titleList);
        }

        return 'Действие "' . $action . '" не найдено';
    }
    
}