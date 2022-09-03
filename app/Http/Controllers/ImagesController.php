<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

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

}
