<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Http\Requests;

use App\Models\Images;

class ImagesController extends Controller
{
    // Вывод изображения
    public function showImage(Request $request) {
        $data = [
            'id'         => auth()->user()->id,
            'idList'     => $request->input('idList'),
            'idItem'     => $request->input('idItem'),
            'imgPath'    => $request->input('imgPath'),
            'titleImg'   => $request->input('titleImg'),
            'hrefRet'   => $request->input('hrefRet'),
        ];
        return view('image', $data);
    }

    // Загрузка изображения
    public function uploadImage(Request $request) {
        $uid = auth()->user()->id;
        $file = $request->file('selected-image');
        $idList = $request->idList;
        $idItem = $request->idItem;

        return Images::uploadImage($uid, $file, $idList, $idItem);
    }

    // Удаление изображения
    public function delImage(Request $request) {
        $uid = auth()->user()->id;
        $idList = $request->idList;
        $idItem = $request->idItem;

        return Images::delImage($uid, $idList, $idItem);
    }
}
