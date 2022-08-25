<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\DB;

use App\Models\Lists;

//use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
//use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ListsController extends Controller
{
    function action(Request $request)
    {
        $uid = auth()->user()->id;
        $action = $request->action;

        if (!$action) $action = $request->input('action');
        $params = $request->input('params', []);

        // Получение списков пользователя
        if ($action === 'getUserLists') {
            $response = Lists::getUserLists($uid);
            return $response;
        }

        return 'Действие не найдено';
    }
    
}