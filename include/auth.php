<?php
//include_once('include/function.php');
$sid = false;
if (!empty($_POST)) {
    $email = mysqlStr($_POST['email']);
    $password = mysqlStr($_POST['password']); //md5($_POST['password']);
    header("Location: http://".$_SERVER['HTTP_HOST']."/hello");
    break;
include('hello.php');
    //print_r("\$email = '" . $email . "'");
    //exit;
    //echo '\n';
    //echo $password;
//
//    $query = "/*".__FILE__.':'.__LINE__."*/ ".
//        "SELECT u.id, u.password, u.first_page 
//        from v2_real_users u, v2_real_clients c, general_settings g
//        where upper(email) = upper('$email')
//            and c.id = u.id_client
//            and u.status = 1
//            and g.maintenance = 0";
//    $result = mysql_query($query)or die(mysql_error());
//    $firstPage = false;
//    $pwdDb = false;
//    $uid = false;
//    while ($row = mysql_fetch_assoc($result)) {
//        $firstPage = $row['first_page'];
//        $pwdDb = $row['password'];
//        $uid = $row['id'];
//    }
//
//    if ($uid && $password == $pwdDb) {
//        // пробуем регистрировать сессию
//        $token = md5(Date('YMdHis'));
//        $query = "/*".__FILE__.':'.__LINE__."*/ "."SELECT session_new($uid, '$token') sid;";
//
//        $result = mysql_query($query)or die(mysql_error());
//        while ($row = mysql_fetch_assoc($result)) {
//            $sid = $row['sid'];
//        }
//    }
//
//    if ($sid && $sid != -1) {
//        writeSystemLog($uid, 'Вход', 1);
//
//        SetCookie("_sd", $token);
//
//        //перекидываем на главную
//        header("Location: https://".$_SERVER['HTTP_HOST']."/v2/index?sid=$sid");
//        break;
//    }
//
//    if ($sid == -1) {
//        writeSystemLog($uid, 'Попытка входа. Превышено число сессий', 1);
//    }
//}
//
//$query = "/*".__FILE__.':'.__LINE__."*/ "."SELECT g.is_demo from general_settings g";
//$result = mysql_query($query)or die(mysql_error());
//while ($row = mysql_fetch_assoc($result)) {
//    $isDemo = $row['is_demo'];
//}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <!-- <link rel="stylesheet" type="text/css" href="css/auth.css?< ?php echo filemtime("css/auth.css");?>" /> -->
        <title>Авторизация</title>
    </head>

    <body>
        <div id="authBack">
            <div id="authDialog">
                <!-- < ?php
                if ($isDemo == 1) { ?>
                <button id="learning" class="btnBlue" style="width:60%" onclick="window.open('https://youtu.be/436yXhc5gTA','_blank'); return false;">Обучение</button>
                <hr />

                <p>Вход без пароля</p> 
                <form method="post"> 
                    <button type="submit" class="btnBlue" style="width:60%">Оператор</button>
                    <input type="hidden" name="guest" value="operator" />
                </form>
                <form method="post"> 
                    <button type="submit" class="btnBlue" style="width:60%">Инженер</button>
                    <input type="hidden" name="guest" value="engineer" />
                </form>
                <form method="post"> 
                    <button type="submit" class="btnBlue" style="width:60%">Клиент</button>
                    <input type="hidden" name="guest" value="client" />
                </form>

                <hr />
                < ?php
                } ?> -->
            <form method="post"> 
                <input id="authElBack" name="email" class="input" type="text" placeholder="Введите логин">
                <br> 
                <input name="password" class="input" type="password" placeholder="Введите пароль">
                <br>
                <button type="submit" class="btnBlue">Вход</button>
            </form>
            <!--form method="post"> 
                <button type="submit" class="btnBlue" style="width:60%">Гость (Администратор)</button>
                <input type="hidden" name="guest" value="admin" />
            </form-->
            </div>
            <?php
            if ($sid == -1) {
                echo "<div class='warning'>Превышено число одновременно работающих пользователей</div>";
            }
            ?>
        </div>

        <!-- <div id="logo-div"> -->
            <img class="logo" src="img/logo.png" alt="" />
        <!-- </div> -->

    </body>
</html>
