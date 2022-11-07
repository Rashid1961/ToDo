<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Users;

class UsersController extends Controller
{
    /**
     *  Информация о пользователе/пользователях
     * 
     * @param $idUser  id пользователя (0 - все пользователи)
    */
    public function getUsers($idUser) {
        return Users::getUsers($idUser);
    }
}