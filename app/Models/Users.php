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
        $users = [
            'id' => [],
            'name' => [],
            'email' => [],
            'image' => [],
            'preview' => [],
        ];
        $where = $idUser == 0 ? '' : (' WHERE u.id = ' . $idUser);
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


        if ($rows) {
            foreach ($rows as $row) {
                $users['id'][] = $row->id;
                $users['name'][] = $row->name;
                $users['email'][] = $row->email;
                $users['image'][] = $row->image;
                $users['preview'][] = $row->preview;
            }
        }
        return $users;
    }

}
