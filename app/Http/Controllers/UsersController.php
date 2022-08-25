<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 18.02.20
 * Time: 17:35
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

//use App\Models\Auth;
use App\Models\Users;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UsersController extends Controller
{


    function indexAction(Request $request)
    {
        $action = $request->action;
        if (!$action) $action = $request->input('action');
        $params = $request->input('params', []);

        // Информация о профиле пользователя
        if ($action === 'login') {
            $login = arrayGetItem($params, 'login');
            $password = arrayGetItem($params, 'password');
            print_r("\$login = '" . $login . "';   \$password = '" . $password . "'");
            exit;
            $uid = (int)arrayGetItem($params, 'uid', $request->user->id);
            $response = Users::getUserProfile($uid);
            return $this->jsonResponse([
                'message' => $response['message'],
            ], $response['code']);
        }

        return $this->jsonResponse_400('Действие не найдено');
    }
    
}