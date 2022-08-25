<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

//use App\Models\MessageFunctions;

//use PHPExcel;
//use PHPExcel_IOFactory;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class Users
{
    static function all($params=NULL)
    {
        $filter = arrayGetItem($params, 'filter');

        $request = request();
        $role_group = $request->role_group;
        $isClient = $role_group->isClient;
        $isDeveloper = $role_group->isDeveloper;

        $session_user_id = $request->user->id;
        $session_client_id = $request->user->id_client;

        $client_id = (int)arrayGetItem($filter, 'client_id', 0);
        $role_id = (int)arrayGetItem($filter, 'role_id', 0);
        $status_id = (int)arrayGetItem($filter, 'status', 1);

        $filials_id = arrayGetItem($filter, 'filials_id', []);
        $filials_id_sql = NULL;
        if (is_array($filials_id)) {
            $t = [];
            foreach ($filials_id as $item) {
                $t[] = (int)$item;
            }
            if (count($t)) $filials_id_sql = 'in (' . implode(',', $t) . ')';
        }
        $otdels_id = arrayGetItem($filter, 'otdels_id', []);
        $otdels_id_sql = NULL;
        if (is_array($otdels_id)) {
            $t = [];
            foreach ($otdels_id as $item) {
                $t[] = (int)$item;
            }
            if (count($t)) $otdels_id_sql = 'in (' . implode(',', $t) . ')';
        }

        $rows = DB::select(
            "
                SELECT
                      u.id
                    , concat_ws(' ',
                        u.surname,
                        concat(left(u.name,1), '.'),
                        concat(left(u.middlename,1), '.')
                        ) as name
                    , concat_ws(' ',
                        u.surname,
                        u.name,
                        u.middlename
                        ) as fullname
                    , u.phone as phone
                from users as u
                    inner join clients as c
                        on u.id_client = c.id
                where u.`new` = 0
                and u.status={$status_id}
                " . ($isClient ? "and c.id={$session_client_id}" : "") . "
                " . ($client_id ? "and c.id={$client_id}" : "") . "
                " . ($role_id ? "and u.role={$role_id}" : "") . "
                " . ($isDeveloper ? "" : "and u.role>1") . "
                " . ($filials_id_sql ? "and u.id_filial {$filials_id_sql}": "") . "
                " . ($otdels_id_sql ? "and u.id_dept {$otdels_id_sql}": "") . "
                order by u.surname
            "
        );

        return $rows;
    }

    static function get_time_offset_by_user($id)
    {
        $rows = DB::select(
            "
                SELECT _get_time_offset_by_user(?) time_offset_user
            ",
            [$id]
        );

        if (count($rows)){
            return $rows[0]->time_offset_user;
        }

        return 0;
    }

    static function get($filter)
    {
        $request = request();
        $role_group = $request->role_group;
        $isClient = $role_group->isClient;

        $ids = arrayGetItem($filter, 'ids', []);
        foreach ($ids as $key => $value) {
            $ids[$key] = (int)$value;
        }

        $rows = DB::select(
            "
            SELECT
                  u.*
                , concat_ws(' ',
                    u.surname,
                    u.name,
                    u.middlename
                ) as fullname
            from users as u
            where u.`new` = 0
            " . (count($ids)>0 ? ("and u.id in (".implode(',',$ids).")") : "") . "
            " . ($isClient ? "and u.id_client={$request->user->id_client}" : "") . "
            order by u.surname
            "
        );

        return $rows;
    }

    static function setPwd($new_password, $new_password2)
    {
        $request = request();
        $uid = $request->user->id;

        if (!key_exists('user_change_password_self', $request->rights)) {
            throw new AccessDeniedHttpException();
        }

        // проверка пароля
        if ($new_password !== $new_password2) {
            throw new BadRequestHttpException('Пароли не совпадают');
        }
        // Длина пароля не менее 6 символов
        if (mb_strlen($new_password,'utf-8') < 6) {
            throw new BadRequestHttpException('Длина пароля не менее 6 символов');
        }
        // Пароль должен содержать как минимум одну цифру
        if (! preg_match("/[\d]{1,}/u", $new_password)) {
            throw new BadRequestHttpException('Пароль должен содержать как минимум одну цифру');
        }
        // Пароль должен содержать как минимум одну строчную букву
        if (! preg_match("/[a-zа-яё]{1,}/u", $new_password)) {
            throw new BadRequestHttpException('Пароль должен содержать как минимум одну строчную букву');
        }
        // Пароль должен содержать как минимум одну прописную букву
        if (! preg_match("/[A-ZА-ЯЁ]{1,}/u", $new_password)) {
            throw new BadRequestHttpException('Пароль должен содержать как минимум одну прописную букву');
        }

        try {
            DB::update(
                "update users set password = ? where id =?",
                [ md5($new_password), $uid ]
            );
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Ошибка сохранения');
        }

        writeSystemLog($uid,'Пароль изменен', 127);

        return true;
    }

    /**
     *  Информация о профиле пользовател
     * 
     * @param $uid
     * 
     * @param bool     $needEquipment: необходимо формировать данные по оборудованию и материалам
     * 
     * @return array   $userProfile
     */
    static function getUserProfile($uid, $needEquipment = true)
    {
        $request = request();
        if (!key_exists('user_edit_self', $request->rights)) {
            throw new AccessDeniedHttpException('Недостаточно прав');
        }

        $userProfile = [    // Профиль пользователя
            'general' => [      // Общие сведения
                'email'      => '',
                'phone'      => '',
                'address'    => '',
                'android_id' => '',
                'mail_sign'  => '',
                'fio'        => '',
                'color'      => '',
                'first_page' => '',
                'comment'    => '',
            ],
            'settings' => [     // Настройки
                'alertChannels' => [            // Оповещеиня
                    'checked'  => [],
                    'enabled' => [],
                ],
                'send_schedule_notif_hr' => false,  // Посылать сообщения об изменении расписания пользователей (для руководителей)
                'defaultTypeOrder'       => [       // Тип заявки по-умолчанию
                    'paid'     => '', // Платность
                    'warranty' => '', // Гарантийность
                ],
                'favoriteClients'        => [],     // Id избранных клиентов
                'visitedOffices'         => [],     // Id посещаемых офисов
            ],
            'cars' => [],       // Автомобили
            'equipment' => [    // Оборудование и материавлы
                'hws'    => [],     // Оборудование
                'spares' => [],     // Запасные части
                'tools'  => [],     // Инструменты
            ]
        ];

        $car = [                // Шаблон информации об одном автомобиле
            'id'               => '',
            'model'            => '',
            'doc'              => '',
            'gosRegNum'        => '',
            'owner'            => '',
            'id_org'           => [],
        ];

        $equipment_hw = [       // Шаблон информации об одной единице оборудования
            'model'  => '',
            'sn'     => '',
            'store'  => '',
        ];

        $equipment_spare = [    // Шаблон информации об одной группе запчастей
            'title'    => '',
            'pn'       => '',
            'sn'       => '',
            'quantity' => 0,
            'store'    => '',
        ];

        $equipment_tool = [     // Шаблон информации об одной группе инструментов
            'title'    => '',
            'pn'       => '',
            'sn'       => '',
            'quantity' => 0,
            'store'    => '',
        ];

        $row = DB::selectOne("
            SELECT
                u.email,
                u.phone,
                u.address,
                u.android_id,
                u.color,
                u.first_page,
                u.comment,
                u.mail_sign,
                CONCAT(u.surname, ' ', u.name, ' ', u.middlename) AS fio,
                u.lon,
                u.lat,
                u.display_notif                                   AS d_n_checked,
                u.email_notif                                     AS e_n_checked,
                u.sms_notif                                       AS s_n_checked,
                c.display_notif                                   AS d_n_enabled,
                c.email_notif                                     AS e_n_enabled,
                c.sms_notif                                       AS s_n_enabled,
                u.send_schedule_notif_hr,
                u.id_paid                                         AS paid,
                u.id_warranty                                     AS warranty,
                (
                    SELECT GROUP_CONCAT(id_client)
                    FROM users_favorite_clients AS ufc
                    WHERE ufc.id_user = u.id
                )                                                 AS favoriteClients,
                (
                    SELECT GROUP_CONCAT(id_office)
                    FROM ref_users_offices
                    WHERE id_user = u.id
                )                                                 AS visitedOffices,
                (
                    SELECT GROUP_CONCAT(CONCAT_WS(
                        '`separator`',
                        uc.id,
                        IFNULL(uc.model, ''),
                        IFNULL(uc.doc, ''),
                        IFNULL(uc.car_number, ''),
                        IFNULL(uc.owner, ''),
                        IFNULL(uc.id_org, ''),
                         '`separator`'             ))
                    FROM v2_real_users_cars  AS uc
                    WHERE uc.id_user = u.id AND uc.`new` = 0
                    ORDER BY uc.id
                )                                                 AS cars
            FROM users          AS u
              LEFT JOIN clients AS c ON c.id = u.id_client
            WHERE u.id = ?
        ", [$uid]);

        if ($row) {
            // Общие сведения
            $userProfile['general']['email']      = $row->email;
            $userProfile['general']['phone']      = $row->phone;
            $userProfile['general']['address']    = $row->address;
            $userProfile['general']['android_id'] = $row->android_id;
            $userProfile['general']['mail_sign']  = $row->mail_sign;
            $userProfile['general']['fio']        = $row->fio;
            $userProfile['general']['lon']        = $row->lon;
            $userProfile['general']['lat']        = $row->lat;
            $userProfile['general']['color']      = $row->color;
            $userProfile['general']['first_page'] = $row->first_page;
            $userProfile['general']['comment']    = $row->comment;

            // Настройки
            if ($row->d_n_checked === '1') $userProfile['settings']['alertChannels']['checked'][]  = '0';
            if ($row->e_n_checked === '1') $userProfile['settings']['alertChannels']['checked'][]  = '1';
            if ($row->s_n_checked === '1') $userProfile['settings']['alertChannels']['checked'][]  = '2';
            if ($row->d_n_enabled === '1') $userProfile['settings']['alertChannels']['enabled'][] = '0';
            if ($row->e_n_enabled === '1') $userProfile['settings']['alertChannels']['enabled'][] = '1';
            if ($row->s_n_enabled === '1') $userProfile['settings']['alertChannels']['enabled'][] = '2';
            $userProfile['settings']['send_schedule_notif_hr']       = $row->send_schedule_notif_hr;
            $userProfile['settings']['defaultTypeOrder']['paid']     = $row->paid;
            $userProfile['settings']['defaultTypeOrder']['warranty'] = $row->warranty;
            if ($row->favoriteClients) {
                $userProfile['settings']['favoriteClients']          = explode(",", $row->favoriteClients);
            }
            if ($row->visitedOffices) {
                $userProfile['settings']['visitedOffices']               = explode(",", $row->visitedOffices);
            }
            // Автомобили
            if (substr_count($row->cars, '`separator`') > 6) {
                $cars = explode('`separator`', $row->cars);
                for ($i = 0; $i < count($cars) - 7; $i += 7) {
                    $j = array_push($userProfile['cars'], $car) - 1;
                    $userProfile['cars'][$j]['id']        = substr($cars[$i], 0, 1) == ',' ? substr($cars[$i], 1) : $cars[$i];
                    $userProfile['cars'][$j]['model']     = $cars[$i + 1];
                    $userProfile['cars'][$j]['doc']       = $cars[$i + 2];
                    $userProfile['cars'][$j]['gosRegNum'] = $cars[$i + 3];
                    $userProfile['cars'][$j]['owner']     = $cars[$i + 4];
                    $userProfile['cars'][$j]['id_org']    = $cars[$i + 5];
                }
            }
            unset($row);

            // Оборудование и материалы
            if ($needEquipment) {
                $rows = self::getUserEquipment($uid);
                foreach ($rows as $row) {
                    if ($row->type_eq === 'HWS') {          // Оборудование
                        $j = array_push($userProfile['equipment']['hws'], $equipment_hw) - 1;
                        $userProfile['equipment']['hws'][$j]['model']  = $row->title;
                        $userProfile['equipment']['hws'][$j]['sn']     = $row->sn;
                        $userProfile['equipment']['hws'][$j]['store']  = $row->store;
                    }
                    else if ($row->type_eq === 'SPARES') {  // Запасные части
                        $j = array_push($userProfile['equipment']['spares'], $equipment_spare) - 1;
                        $userProfile['equipment']['spares'][$j]['title']    = $row->title;
                        $userProfile['equipment']['spares'][$j]['pn']       = $row->pn;
                        $userProfile['equipment']['spares'][$j]['sn']       = $row->sn;
                        $userProfile['equipment']['spares'][$j]['quantity'] = $row->qty;
                        $userProfile['equipment']['spares'][$j]['store']    = $row->store;
                    }
                    else if ($row->type_eq === 'TOOLS') {  // Запасные части
                        $j = array_push($userProfile['equipment']['tools'], $equipment_tool) - 1;
                        $userProfile['equipment']['tools'][$j]['title']    = $row->title;
                        $userProfile['equipment']['tools'][$j]['pn']       = $row->pn;
                        $userProfile['equipment']['tools'][$j]['sn']       = $row->sn;
                        $userProfile['equipment']['tools'][$j]['quantity'] = $row->qty;
                        $userProfile['equipment']['tools'][$j]['store']    = $row->store;
                    }
                }
                unset($row);
                unset($rows);
            }
            else {
                $userProfile['equipment']['hws'] = [];
                $userProfile['equipment']['spares'] = [];
                $userProfile['equipment']['tools']  = [];
            }
        }
        return  $userProfile;
    }

    /**
     *  Информация об оборудовании и материалах, числящихся за пользователем $uid
     * 
     * @param $uid
     * 
     * @return $rows
     */
    static function getUserEquipment($uid)
    {
        $rows = DB::select("
        (                   # Оборудование
            SELECT
                'HWS'                          AS type_eq,
                CONCAT(s.vendor, ' ', s.model) AS title,
                ''                             AS pn,
                s.sn,
                s.store_full_desc              AS store,
                '0'                            AS qty
            FROM v2_real_hws_store AS s
            WHERE (s.write_off = 0 OR s.write_off IS NULL)
                AND s.id_engineer = ?
                AND s.is_special = 0
                AND s.is_last = 1
                AND s.id_leave IS NULL
        )
        UNION
        (                   # Запапсные части
            SELECT
                'SPARES'          AS type_eq,
                s.title,
                s.pn,
                s.sn,
                s.store_full_desc AS store,
                count(*)          AS qty
            FROM v2_real_spares_store         AS s
                LEFT JOIN permanent_hws_parts AS p ON s.id = p.id_spare
            WHERE (s.write_off = 0 OR s.write_off IS NULL)
                AND s.id_engineer = ?
                AND s.is_spare = 1
                AND s.is_special = 0
                AND s.is_last = 1
                AND s.id_leave IS NULL
                AND p.id IS NULL
            GROUP BY s.title, s.pn, s.sn, s.store_full_desc
        )
        UNION
        (                   # Инструменты
            SELECT
                'TOOLS'           AS type_eq,
                s.title,
                s.pn,
                s.sn,
                s.store_full_desc AS store,
                count(*)          AS qty
            FROM v2_real_spares_store  AS s
            WHERE (s.write_off = 0 OR s.write_off IS NULL)
                AND s.id_engineer = ?
                AND s.is_special = 1
                AND s.is_last = 1
                AND s.id_leave IS NULL
            GROUP BY s.title, s.pn, s.sn, s.store_full_desc
        )
        ORDER BY 1, 2
        ", [$uid, $uid, $uid]);
        return $rows;

    }

    /**
     *  Сохранение изменений в профиле пользовател
     * 
     * @param $uid
     * 
     * @param $changedTabs
     * 
     * @param $userProfile
     * 
     */
    static function saveUserProfile($uid, $changedTabs, $userProfile) {
        $response = [
            'message' => 'Профиль пользователя изменен',
            'code'    => 200,
        ];
        $curUP = self::getUserProfile($uid, false);

        //print_r("\$currentUserProfile = ");
        //print_r($currentUserProfile);
        //print_r("\$changedTabs = ");
        //print_r($changedTabs);
        //print_r("\$uid = '" . $uid . "'");
        //print_r("\$userProfile = ");
        //print_r($userProfile);
        //exit;

        if ($changedTabs['GENERAL'] || $changedTabs['SETTINGS']) {
            $general = $userProfile['general'];
            $settings = $userProfile['settings'];
            $canal = [0, 0, 0];
            for ($i = 0; $i < count($settings['alertChannels']['checked']); $i++) {
                $canal[$settings['alertChannels']['checked'][$i]] = 1;
            }
            $row = DB::select(
                "SELECT user_edit_self(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) AS r",
                [
                    $uid, $general['email'], $general['phone'], $general['mail_sign'], $general['color'],
                    $settings['defaultTypeOrder']['warranty'], $settings['defaultTypeOrder']['paid'], $general['comment'],
                    $general['lon'], $general['lat'], $canal[0], $canal[1], $canal[2], $general['address'],
                    $general['first_page'], $settings['send_schedule_notif_hr']
                ]
            );
            if ($row[0]->r == -1) {
                $response['code'] = 400;
                $response['message'] = 'Ошибка сохранения профиля пользователя id=' . $uid;
                return $response;
            }
            writeSystemLog($uid, 'Профиль пользователя id='.$uid.' изменен', 128);

            if ($changedTabs['SETTINGS']) {
                // Уудаление из "Избранных" клиентов
                foreach ($curUP['settings']['favoriteClients'] as $iCur => $cur_client) {
                    $iNew = array_search($cur_client, $settings['favoriteClients']);
                    if ($iNew === false) {
                        // Клиент удалён из "Избранных"
                        $row = DB::select(
                            "SELECT users_favorite_clients_del(?, ?) AS r",
                            [$uid, $cur_client]
                        );
                        if ($row[0]->r == -1) {
                            $response['code'] = 400;
                            $response['message'] = 'Ошибка удаления "Избранного" клиента '. $cur_client . ' для пользователя id=' . $uid;
                            return $response;
                        }
                        writeSystemLog($uid, 'Избранный клиент ' . $cur_client . ' для пользователя id=' . $uid . ' удален', 130);
                    }
                    else {
                        // Клиент остался в "Избранных"
                        unset($settings['favoriteClients'][$iNew]);
                    }
                }
                // Добавление "Избранных" клиентов
                foreach ($settings['favoriteClients'] as $iNew => $new_client) {
                    $row = DB::select(
                        "SELECT users_favorite_clients_add(?, ?) as r",
                        [$uid, $new_client]
                    );
                    if ($row[0]->r == -1) {
                        $response['code'] = 400;
                        $response['message'] = 'Ошибка добавления "Избранного" клиента '. $new_client . ' для пользователя id=' . $uid;
                        return $response;
                    }
                    if ($row[0]->r == -2) {
                        $row = DB::selectOne("SELECT g.max_favorite_clients AS mfc FROM general_settings AS g");
                        $response['code'] = 400;
                        $response['message'] = 'Ошибка добавления "Избранного" клиента '. $new_client . ' для пользователя id=' . $uid .
                                               ' (максимальное количество "избранных клиентов" = '. $row->mfc .')';
                        return $response;
                    }
                    writeSystemLog($uid, 'Избранный клиент ' . $new_client . ' для пользователя id = ' . $uid . ' добавлен', 129);
                }

                // Уудаление посещаемых офисов
                foreach ($curUP['settings']['visitedOffices'] as $icur => $cur_office) {
                    $iNew = array_search($cur_office, $settings['visitedOffices']);
                    if ($iNew === false) {
                        // Офис удалён из посещаемых
                        $row = DB::select(
                            "SELECT ref_users_offices_full(?, ?, ?, ?) AS r",
                            [$uid, $uid, $cur_office, 0]
                        );
                        if ($row[0]->r == -1) {
                            $response['code'] = 400;
                            $response['message'] = 'Ошибка удаления посещаемого офиса '. $cur_office . ' для пользователя id=' . $uid;
                            return $response;
                        }
                        writeSystemLog($uid, 'Офис ' . $cur_office . ' удалён для пользователя id = ' . $uid, 203);
                    }
                    else {
                        // Офис остался в посещаемых
                        unset($settings['visitedOffices'][$iNew]);
                    }
                }
                // Добавление "Избранных" клиентов
                foreach ($settings['visitedOffices'] as $iNew => $new_office) {
                    $row = DB::select(
                        "SELECT ref_users_offices_full(?, ?, ?, ?) AS r",
                        [$uid, $uid, $new_office, 1]
                    );
                    if ($row[0]->r == -1) {
                        $response['code'] = 400;
                        $response['message'] = 'Ошибка долбавления посещаемого офиса '. $new_office . ' для пользователя id=' . $uid;
                        return $response;
                    }
                    writeSystemLog($uid, 'Офис ' . $new_office . ' добавлен для пользователя id = ' . $uid, 203);
                }
            }
        }
        
        if ($changedTabs['CARS']) {
            // Корректировка или удаление автомобиля
            foreach ($curUP['cars'] as $iCur => $cur_car) {
                $need_car_delete = true; //Автомобиль необходимо удалить?
                foreach ($userProfile['cars'] as $iNew => $new_car) {
                    if ($new_car['id'] == $cur_car['id']) {
                        // Автомобиль не удаляется
                        $need_car_delete = false;
                        if ($new_car['model']     != $cur_car['model']     ||
                            $new_car['doc']       != $cur_car['doc']       ||
                            $new_car['gosRegNum'] != $cur_car['gosRegNum'] ||
                            $new_car['owner']     != $cur_car['owner']     ||
                            $new_car['id_org']    != $cur_car['id_org']      ) {
                            // Автомобиль корректируется
                            $row = DB::select(
                                "SELECT users_cars_edit(?, ?, ?, ?, ?, ?, ?, ?) AS r",
                                [$uid, $cur_car['id'], $new_car['model'], $new_car['doc'], $new_car['gosRegNum'], $new_car['owner'], 0, $new_car['id_org']]
                            );
                            if ($row[0]->r == 0) {
                                $response['code'] = 400;
                                $response['message'] = 'Ошибка сохранения автомобиля '. $cur_car['id'] . ' для пользователя id=' . $uid;
                                return $response;
                            }
                            writeSystemLog($uid, 'Автомобиль ' . $cur_car['id'] . ' сохранен для пользователя id = ' . $uid, 133);
                        }
                        print_r("BEFORE \$userProfile['cars'] = ");
                        print_r($userProfile['cars']);
                        unset($userProfile['cars'][$iNew]);
                        print_r("AFTER \$userProfile['cars'] = ");
                        print_r($userProfile['cars']);
                        break;
                    }
                }
                if ($need_car_delete) {
                // Автомобиль удаляется
                    $row = DB::select(
                        "SELECT users_cars_del(?, ?) AS r",
                        [$uid, $cur_car['id']]
                    );
                    if ($row[0]->r == 0) {
                        $response['code'] = 400;
                        $response['message'] = 'Ошибка удаления автомобиля '. $cur_car['id'] . ' для пользователя id=' . $uid;
                        return $response;
                    }
                    writeSystemLog($uid, 'Автомобиль ' . $cur_car['id'] . ' удален для пользователя id = ' . $uid, 133);
                }
            }
            // Добавление автомобиля
            foreach ($userProfile['cars'] as $iNew => $new_car) {
                $row = DB::select(
                    "SELECT users_cars_add(?, ?, ?, ?, ?, ?, ?, ?) AS r ",
                    [$uid, $uid, $new_car['model'], $new_car['doc'], $new_car['gosRegNum'], $new_car['owner'], 0, $new_car['id_org']]
                );
                if ($row[0]->r == 0) {
                    $response['code'] = 400;
                    $response['message'] = 'Ошибка добавления автомобиля '. $new_car['model'] . ' ' . $new_car['gosRegNum'] . ' для пользователя id=' . $uid;
                    return $response;
                }
                writeSystemLog($uid, 'Автомобиль ' . $row[0]->r . ' добавлен для пользователя id = ' . $uid, 133);
            }
        }
        return $response;
    }
}
