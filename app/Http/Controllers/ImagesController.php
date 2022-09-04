<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

use App\Models\Images;

class ImagesController extends Controller
{
    // Вывод изображения
    public function showImage(Request $request) {
        $data = [
            'idList'   => $request->input('idList'),
            'idItem'   => $request->input('idItem'),
            'imgPath'  => $request->input('imgPath'),
            'titleImg' => $request->input('titleImg'),
        ];
        $view = view('image', $data);
        return $view;
    }

    // Загрузка изображения
    public function uploadImage(Request $request) {
        $uid = auth()->user()->id;
        $file = $request->file('selected-image');
        $idList = $request->idList;
        $idItem = $request->idItem;

        return Images::uploadImage($uid, $file, $idList, $idItem);
        
    /*
        echo $file->getClientOriginalName();         //имя файла
        echo $file->getClientOriginalExtension();    //расширение файла
        echo $file->getRealPath();                   //фактический путь к файлу
        echo $file->getSize();                       //размер файла
        echo $file->getMimeType();                   //Mime-тип файла
        $newPath = 'uploads';                   //перемещение загруженного файла
        $newName = 'bla-bla-bla.jpg';           //из временного каталога в каталог
        $file->move($newPath, $neweName);       //$newPath с именем $neweName
    
        print_r('\$filename = "' . $filename . '"');
        print_r('\$path = "' . $path . '"');
        return $path;
    */
        return;
    }
}
