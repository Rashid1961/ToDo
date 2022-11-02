<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sharing;

class SharingController extends Controller
{
    // Пользователи для которых расшарен пункт/пункты списка/списков
    // @param $idList  id списка пользователя (0 - все списки)
    // @param $idItem  id пункта списка (0 - все пункты)
    public function getWithWhomShared($idList, $idItem) {
        $uid = auth()->user()->id;
        return Sharing::getWithWhomShared($uid, $idList, $idItem);
    }
}