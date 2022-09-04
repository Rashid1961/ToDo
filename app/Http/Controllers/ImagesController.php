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
            'id'       => $request->input('id'),
            'whatShow' => $request->input('whatShow'),
            'image'    => $request->input('image'),
            'name'     => $request->input('name'),
        ];
        $view = view('showimage', $data);
        return $view;
    }

    // Загрузка изображения
    public function uploadImage(Request $request) {
        print_r("\$request->file = ");
        print_r($request->file);
        //Storage::disk('images')->put('file.txt', 'Contents');
        //Storage::put(
        //    'avatars/'.$user->id,
        //    file_get_contents($request->file('avatar')->getRealPath())
        //  );
        //$disk = Storage::disk('s3');
    }
}
