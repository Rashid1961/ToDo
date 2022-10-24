<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;

class Users
{
    /**
     *  Информация о пользователе / пользователях
     * 
     * @param $idUser  id пользователя (0 - все пользователи)
     */
    static function getUsers($idUser)
    {
        $where = $idUser == 0 ? '' : ' WHERE u.id = ' . $idUser;
        $rows = DB::select(
            "
                SELECT
                    u.id,
                    u.name,
                    u.email,
                    u.image,
                    u.preview
                FROM users AS u 
            " . $where .
            "   ORDER BY u.name
            ", []
        );

        if (count($rows) == 0) return [];
        else return $rows;
    }

}
