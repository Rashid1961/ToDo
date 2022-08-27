<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

//use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
//use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class Lists
{
    static function getUserLists($id)
    {
        $rows = DB::select(
            "
                SELECT
                    l.id,
                    l.title,
                    l.image,
                    (
                        SELECT
                            count(*)
                        FROM items AS i
                        WHERE i.id_list = l.id
                    ) AS number_items
                FROM lists AS l
                WHERE l.id_user = ?
            ", [$id]
        );

        if (count($rows) == 0) return [];
        else return $rows;
    }
}
