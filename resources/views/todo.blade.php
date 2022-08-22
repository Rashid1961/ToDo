<?php
    //use App\Http\Middleware\checkAuth;
    //include_once('/var/www/ToDo/include/auth.php');
    //define('ROOT_DIR', '/var/www/ToDo');
    define('PATH', '/var/www/ToDo/');
?>

<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- <link rel="icon" href="/v2/favicon.ico" />  -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />

        <title>ToDo</title>
    </head>
    <body>
        <div class="container panel panel-default ">
            <h2 class="panel-heading">Авторизация</h2>
            <form id="authForm">
                <div class="form-group col-md-4">
                    <input
                        class="form-control"
                        type="text"
                        name="email"
                        placeholder="Введите Email"
                        id="email"
                    >
                </div>
                <div class="form-group col-md-4">
                    <input
                        class="form-control"
                        type="text"
                        name="password"
                        placeholder="Введите пароль"
                        id="password"
                    >
                </div>
                <div class="form-group col-md-4">
                    <button class="btn btn-success" id="login">Вход</button>
                </div>
                <div class="form-group col-md-4">
                    <button class="btn btn-success" id="registration">Регистрация</button>
                </div>
            </form>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="/include/util.js"></script>


        <script>
            /*import {Util} from './include/util.js'

            $('#authForm').on('login',function(event){
                event.preventDefault();

                let email    = $('#email').val();
                let password = $('#password').val();
                console.log("email = '" + email + "';");
                console.log("password = '" + password + "'");
                return;

                /*
                Util.http.post(
                    {
                        url: "/users",
                        data: {
                            action:   'login',
                            email:    email,
                            password: password,
                        }
                    },
                    function(response){
                        if ('message' in response) {
                            console.log("OK!   " + response.message);
                        }
                    },
                    function(response){
                        if ('message' in response) {
                            console.log("ERROR!   " + response.message);
                        }
                    },
                    function() {
                    }
                );
                */
                /*
                $.ajax({
                    url:  "/users",
                    type: "POST",
                    data: {
                            action:   'login',
                            email:    email,
                            password: password,
                    },
                    success:function(response){
                        console.log(response);
                    },
                });
                
                $.post(
                    "/users",
                    {
                        action:   'login',
                        email:    email,
                        password: password,
                    },
                    onSuccess=>function(response){
                        console.log(response);
                    },
                );
            });
            */
         </script>
    </body>
</html>