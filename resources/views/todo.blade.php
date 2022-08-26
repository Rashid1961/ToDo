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
        <!-- Шапка -->
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        ToDo
                    </a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li style="color: #777; margin: 15 10 0 0;">{{$name}}</li>
                        <li>
                            <img
                                src={{$image}} 
                                width="30px"
                                height="30px"
                                style="border-radius: 50%; margin: 10 0 0 0;"
                                alt="Ивините, изображения нет"
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
        <!-- Списки пользователя -->
        <div class="container" style="margin-top: 50; margin-left: auto;">
            <form id="main">
                <table id="lists" width="100%">
                    <caption>Ваши списки</caption>
                    <thead>
                        <tr>
                            <th style="text-align: center;">Id</th>
                            <th style="text-align: center;">Наименование списка</th>
                            <th style="text-align: center;">Изображение</th>
                            <th style="text-align: center;">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="one-list">
                    </tbody>
                </table>
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
                    //console.log(userLists[0].image);
                },
            });
            var titleList=[];
            var idList=[];
            var iCur = '';
            var titleListCur = '';
            var elementCur = null;
            if (userLists.length == 0) {
                $("#one-list").append(
                    '<tr>' +
                        '<td colspan="4" style="text-align: center;">' +
                            'У Вас нет пока ни одного списка' +
                        '</td>' +
                    '</tr>'
                );
            }
            else {
                for (let i = 0; i < userLists.length; i++) {
                    idList[i] = userLists[i].id;
                    titleList[i] = userLists[i].title;
                    $("#one-list").append(
                        '<tr id="list-' + idList[i] + '">' +
                            '<td style="text-align: center;">' + idList[i] + '</td>' +
                            '<td' +
                                ' id="title-list-' + i + '"' +
                            '>' +
                                titleList[i] + 
                            '</td>' +
                            //'<td>' +
                            //    '<div' +
                            //        ' id="title-list-' + i +'"' +
                            //    '>' +
                            //        titleList[i] +
                            //    '</div>' +
                            //'</td>' +
                            '<td style="text-align: center;">' + 
                                '<a id="image-list-' + i + '"' +
                                    ' href=' + userLists[i].image +
                                    //' target="_blank"' +
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
                            '</td>' +
                            '<td style="text-align: center;">' + 
                                '<div class="row" style="margin-bottom: 10;">' +
                                    '<button' +
                                    ' id="edit-list-' + i + '"'+
                                    ' type="button"' +
                                    ' class="btn btn-primary"' +
                                    '>' +
                                        'Изменить' +
                                    '</button>' +
                                '</div>' +
                                '<div class="row">' +
                                    '<button' +
                                        ' id="del-list-' + i + '"' +
                                        ' type="button"' +
                                        ' class="btn btn-danger"' +
                                    '>' +
                                        'Удалить' +
                                    '</button>' +
                                '</div>' +
                            '</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td colspan="4">' +
                                '<hr/>' +
                            '</td>' +
                        '</tr>'
                    );
                }
            }
            $("#one-list").append(
                '<tr>' +
                    '<td colspan="4" style="text-align: center;">' +
                        '<button id="append-list" type="button" class="btn btn-success">' +
                            'Добавить список' +
                        '</button>' +
                    '</td>' +
                '</tr>'
            );
            $(":button").click(function() {
                let clickId = this.id;
                if (clickId === "append-list") {
                    // Добавить лист
                }
                else if(clickId.substring(0, 10) === "edit-list-") {
                    // Редактировать "Наименование списка"
                    iCur = clickId.substring(10);
                    titleListCur = titleList[iCur];
                    $("#title-list-" + iCur).html(
                        '<input' +
                            //' id="title-list-input-' + iCur + '"' +
                            ' type="text"' +
                            ' style="width: 100%"' +
                            ' name="titleListCur"' + 
                            ' value="' + titleListCur + '"' +
                            ' maxlength="100"' +  
                            ' onblur="changeTitle(iCur, titleListCur.value)"' +
                        '/>'
                    );
                    $("#title-list-" + iCur + ">input").focus();
                }
                else if(clickId.substring(0, 9) === "del-list-") {
                    // Удалить лист
                }
            });
            function changeTitle(idx, titleListValue) {
                console.log("idx = '" + idx + "'");
                console.log("newTitle = '" + titleListValue + "'");
                titleList[idx] = titleListValue;
                console.log("titleList[idx] = '" + titleList[idx] + "'");
                $("#title-list-" + idx).html(titleList[idx]);
            }
         </script>
    </body>
</html>