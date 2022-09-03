<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
//use App\Http\Controllers\Controller;

use App\Models\Lists;

class ListsController extends Controller
{
    // Получение списков пользователя
    public function getLists() {
        $uid = auth()->user()->id;
        return Lists::getLists($uid);
    }

    // Переименование списка
    public function changeTitleList(Request $request) {
        $uid = auth()->user()->id;
        $listid = $request->input('listid', 0);
        $titleList = $request->input('listtitle', '');
        return Lists::changeTitleList($uid, $listid, $titleList);
    }

    // Удаление списка
    public function deleteList(Request $request) {
        $uid = auth()->user()->id;
        $listid = $request->input('listid', 0);
        return Lists::deleteList($uid, $listid);
    }

    // Добавление списка
    public function appendList(Request $request) {
        $uid = auth()->user()->id;
        $title = $request->input('title', '');
        $image = $request->input('image', '');
        return Lists::appendList($uid, $title, $image);
    }
}


//{
//    /**
//     * Display a listing of the resource.
//     *
//     * @return \Illuminate\Http\Response
//     */
//    public function index()
//    {
//        //
//    }
//
//    /**
//     * Show the form for creating a new resource.
//     *
//     * @return \Illuminate\Http\Response
//     */
//    public function create()
//    {
//        //
//    }
//
//    /**
//     * Store a newly created resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @return \Illuminate\Http\Response
//     */
//    public function store(Request $request)
//    {
//        //
//    }
//
//    /**
//     * Display the specified resource.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function show($id)
//    {
//        //
//    }
//
//    /**
//     * Show the form for editing the specified resource.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function edit($id)
//    {
//        //
//    }
//
//    /**
//     * Update the specified resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function update(Request $request, $id)
//    {
//        //
//    }
//
//    /**
//     * Remove the specified resource from storage.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function destroy($id)
//    {
//        //
//    }
//}