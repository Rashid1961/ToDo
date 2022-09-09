<?php
?>

<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{csrf_token()}}">
        <meta name="viewport" content="width=device-width, initial-scale=1 shrink-to-fit=no">

        <meta name="robots" content="noindex,nofollow">
        <link rel="stylesheet" type="text/css" href="style.css">

        <title>ToDo</title>
        <!-- Fonts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">
        <!-- Styles -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        <style>
            body {
                font-family: 'Lato';
            }

            .fa-btn {
                margin-right: 6px;
            }
        </style>
    </head>
    <body>
        <div id='tabName'   hidden>todo</div>
        <!-- Шапка -->
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{url('/')}}">
                        ToDo
                    </a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li id="user-name" style="color: #000; margin: 15 10 0 0;">{{$name}}</li>
                        <li>
                            <img
                                src="{{$image}}" 
                                width="30px"
                                height="30px"
                                style="border-radius: 50%; margin: 10 0 0 0;"
                                alt="Ивините, изображения нет"
                            />
                        </li>
                        <li>
                            <a
                                id="exit"
                                href="{{url('/logout')}}"
                            >
                                <i class="fa fa-btn fa-sign-out"></i>Выход
                            </a>
                        </li>
                    </ul>
                </div> 
            </div>
        </nav>
        <!-- Основной контейнер -->
        <div class="container" style="margin-top: 50; margin-left: auto;">
            <!-- Списки пользователя -->
            <form class="form-horizontal" id="form-lists">
                <div class="form-group">
                    <table class="table" id="lists" width="100%">
                        <caption style="font-size: 200%; color:#000;">Ваши списки</caption>
                        <tbody id="one-list">
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- Пункты списка -->
            <form class="form-horizontal" id="form-items">
                <!-- "Фильтр", "Поиск" -->
                <div class='form-inline' id="filter-search" style='text-align: center; margin-top: 20'>
                    <div class='form-horizontal' style='margin-bottom: 10px;'>

                        <div class="dropdown" id='filter' style="display: inline; margin-right: 100;">
                            <button class="btn btn-primary dropdown-toggle" type="button" 
                                    id="dropdownMenu1" data-toggle="dropdown" 
                                    aria-haspopup="true" aria-expanded="true">
                              <i class="glyphicon glyphicon-search" style="margin-right: 5;"></i>Фильтр по тегам
                              <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu checkbox-menu allow-focus" id="ul-filter" aria-labelledby="dropdownMenu1">
                                  
                                  
                            </ul>
                        </div>



                        <!-- <button
                            id='filter'
                            type='button'
                            class='btn btn-primary'
                            style='display: inline; margin-right: 100;'
                        >
                           Фильтр по тегам
                        </button> -->

                        <button
                            id='search' 
                            type='button'
                            class='btn btn-primary'
                            style='display: inline; margin-left: 100;'
                        >
                            Поиск по наименованию
                        </button>
                    </div>
                </div>
                <!-- выбор фильтра -->
                <div class="form-inline" id="select-filter" style="margin: 0; word-break: break-word;">

                </div>

                <!-- Пункты -->
                <div class="form-group">
                    <table class="table" id="items" width="100%">
                        <caption
                            id='caption-items'
                            style="font-size: 250%; color:#000; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                        >
                        </caption>
                        <tbody id="one-item">
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        <!-- JavaScripts -->
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js"
            integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb"
            crossorigin="anonymous">
        </script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"
            integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
            crossorigin="anonymous">
        </script>
        <script src="/js/jquery.notification.min.js"></script>
        <script src="/js/appToDo.js"></script>
    </body>
</html>
