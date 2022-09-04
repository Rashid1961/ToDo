<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Http\Requests;

use App\Models\Items;

class ItemsController extends Controller
{
    // Получение пунктов списка
    public function getItems(Request $request)
    {
        $listId = $request->input('listid', 0);
        return Items::getItems($listId);

    }
}
