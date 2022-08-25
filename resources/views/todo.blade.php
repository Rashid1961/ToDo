<?php
?>

<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1 shrink-to-fit=no">
        <!-- <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        /> -->
        <title>ToDo</title>
        <!-- Fonts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">
        <!-- Styles -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}
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
        <!-- <div class="block" style="position:sticky"> -->
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        ToDo
                    </a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <!-- <div hidden id="img-user">{{$image}}</div> -->
                            <img
                                src={{$image}} 
                                width="20px"
                                height="20px"
                                style="border-radius: 50%"
                                alt="Изображения нет"
                            />

                        </li>
                        <li>
                            <a
                                href="{{ url('/logout') }}"
                            >
                                <i class="fa fa-btn fa-sign-out"></i>Выход
                            </a>
                        </li>
                    </ul>
                </div> 
            </div>
        </nav>
        <!-- </div> -->



        <!-- <div hidden id="img-user">{{$image}}</div> -->
        <!-- <div class="container panel panel-default"> -->
        <div class="container">
            
            <form id="main">

                <div class="row" id="lists">

                </div>

                <!-- <div class="form-group col-md-4">
                    <a
                        href="{{ url('/auth/logout') }}"
                        style="color:#000000; text-decoration:none"
                    >Выход</a>
                </div> -->

            </form>
        </div>

        <!-- JavaScripts -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
        {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}

        <!-- <script
            src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        ></script> -->
        <!-- <script
            src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
            integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
            crossorigin="anonymous"
        ></script> -->
        <!-- <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF"
            crossorigin="anonymous"></script> -->

        <script>
            var imgUser =
                '<img' +
                    ' src=' + $("#img-user").text() +
                    ' width="20px"' +
                    ' height="20px"' +
                    ' style="border-radius: 50%"' +
                    ' alt="Изображения нет"' +
                '/>';
            //$("#img-user").replaceWith(imgUser);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },                
                statusCode: { 
                    0: function(){ 
                        alert('Сеть недоступна.');
                    },
                    403: function(){
                      alert( 'Доступ запрещен (403).');
                    },
                    404: function(){ 
                        alert('Запрашиваемая страница не найдена (404).');
                    },
                    500: function(){
	                	alert('Внутренняя ошибка сервера (500).');
                    }
                }
            });
            var userLists = [];
            $.ajax({
                url:    '/ListsUser',
                method: 'post',
                dataType: 'json',
                async:   false,
                data: {
                    'action': 'getUserLists'
                },
                success: function(response){
                    userLists = response;
                    console.log(userLists[0].image);
                },
            });
            $("#lists").append('<ul>');
            var lists = '';
            for (let i = 0; i < userLists.length; i++) {
                lists = 
                    '<div class="col-2">' +
                        userLists[i].id +
                    '</div>' +
                    '<div class="col-7">' +
                        userLists[i].title +
                    '</div>' +
                    '<div class="col-3">' +
                        '<a' +
                            ' href=' + userLists[i].image +
                            ' target="_blank"' +
                        '>' +
                            '<img' +
                                ' src=' + userLists[i].image +
                                ' width="150px"' +
                                ' height="150px"' +
                                ' href="#"' +
                                ' alt="Изображения нет"' +
                                ' title="Посмотреть в отдельной вкладке"' +
                            '/>' +
                        '</a>' +
                    '</div>' +
                    '<hr/>';
                $("#lists").append(lists);
            }
            $("#lists").append('<ul>');

            // $('#exit').click(function(event){
            //     //event.preventDefault();
            //     $.ajax({
            //         url:    '/logout',
            //         method: 'get',
            //     });
            // });
         </script>
    </body>
</html>