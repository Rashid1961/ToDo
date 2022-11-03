<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sharing;

class SharingController extends Controller
{
    // Пользователи для которых расшарен пункт/пункты списка/списков
    // @param $idList  id списка пользователя (0 - все списки, значение $idItem неважно)
    // @param $idItem  id пункта списка (имеет смысл только если указан конкретный id списка, 0 - все пункты списка)
    public function getForWhomShared($idList, $idItem) {
        $idUser = auth()->user()->id;
        return Sharing::getForWhomShared($idUser, $idList, $idItem);
    }
}