<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Storage;


//use App\Models\Images;

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
        print_r($idList);
        print_r($idItem);
        $u = 'u' . substr(('00' . (string)$uid), -3);
        $l = '_l' . substr(('00' . (string)$idList), -3);
        $i = '_i' . substr(('00' . (string)$idItem), -3);
        if ($idList == 0 && $idItem == 0) { // Изображение пользователя
            $l = '';
            $i = '';
            $subDir = 'users/'; 
        }
        else if ($idItem == 0) {            // Изображение списка
            $i = '';
            $subDir = 'lists/';
        }
        else {                              // Изображение пункта
            $subDir = 'items/';
        }
        if (Storage::disk('images')->put($subDir . $u . $l . $i . '_img.jpg', (string)file_get_contents($file->getRealPath()))) {

        }
        
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
