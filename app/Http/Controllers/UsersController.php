<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Users;

class UsersController extends Controller
{
    // Получение информации о пользователе / пользователях
    public function getLists() {
        $uid = auth()->user()->id;
        return Users::getUsers($uid);
    }
}