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
                        <li id="user-name" style="color: #000; margin: 15 10 0 0;"></li>
                        <li>
                            <img
                                src="" 
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
                <!-- Перечень списков -->
                <table class="table table-striped" id="lists" width="100%">
                    <caption style="font-size: 200%; color:#000;">Ваши списки</caption>
                    <tbody id="one-list">
                    </tbody>
                </table>

                <!-- Пункты списка -->
                <table class="table table-striped" id="items-list" width="100%">
                    <!-- <caption style="font-size: 250%; color:#000;">Пункты списка</caption> -->
                    <tbody id="one-item">
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
            $('#lists').hide();
            $("#items-list").hide();
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
            var iCur = '';
            showUserLists();

            /**
             *  Заполнение и отображение таблицы списков
             */
            function showUserLists() {
                $.ajax({
                    url:    '/Lists',
                    method: 'post',
                    dataType: 'json',
                    async:   false,
                    data: {
                        'action': 'getUserLists'
                    },
                    success: function(response){
                        userLists = response;
                    },
                });

                $("#one-list").empty();
                if (userLists.length == 0) {
                    $("#one-list").append(
                        '<tr>' +
                            '<td colspan="3" style="font-size: 175%; text-align: center;">' +
                                'У Вас пока нет ни одного списка' +
                            '</td>' +
                        '</tr>'
                    );
                }
                else {
                    for (let i = 0; i < userLists.length; i++) {
                        $("#one-list").append(
                            '<tr id="list-' + i + '">' +
                                '<td style="text-align: center; width: 170px;">' + 
                                    '<a id="image-list-' + i + '"' +
                                        ' href="' + userLists[i].image + //{{ url(`/image`) }}
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
                                '</td>' +
                                '<td' +
                                    ' style="vertical-align: middle;"' +
                                '>' +
                                    '<div' +
                                        ' id="title-list-' + i + '"' + 
                                        ' class="row text-break"'+
                                        ' style="margin: 0; font-size: 175%; word-break: break-word;"' +
                                    '>' +
                                        userLists[i].title +
                                    '</div>' +
                                    '<div class="row" style="margin: 0; color: #777;">' +
                                        'Количество пунктов: ' + userLists[i].number_items +
                                    '</div>' +
                                '</td>' +
                                '<td style="text-align: right; vertical-align: middle; width: 150px;">' + 
                                    '<div class="row" style="margin: 10 10 5 10;">' +
                                        '<button' +
                                        ' id="expand-list-' + i + '"'+
                                        ' type="button"' +
                                        ' class="btn btn-block btn-primary"' +
                                        '>' +
                                            'Развернуть список' +
                                        '</button>' +
                                    '</div>' +
                                    '<div class="row" style="margin: 5 10 5 10;">' +
                                        '<button' +
                                        ' id="edit-list-' + i + '"'+
                                        ' type="button"' +
                                        ' class="btn btn-block btn-primary"' +
                                        '>' +
                                            'Изменить наименование' +
                                        '</button>' +
                                    '</div>' +
                                    '<div class="row" style="margin: 5 10 10 10;">' +
                                        '<button' +
                                            ' id="del-list-' + i + '"' +
                                            ' type="button"' +
                                            ' class="btn btn-block btn-danger"' +
                                        '>' +
                                            'Удалить список' +
                                        '</button>' +
                                    '</div>' +
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
                $('#lists').show();

                $(":button").click(function() {
                    let clickId = this.id;
                    if (clickId === "append-list") {
                        // Добавить список
                        appendList();
                    }
                    else if(clickId.substring(0, 12) === "expand-list-") {
                        // Развернуть список
                        $('#lists').hide();
                        $("#items-list").show();
                    }
                    else if(clickId.substring(0, 10) === "edit-list-") {
                        // Изменить "Наименование списка"
                        iCur = clickId.substring(10);
                        $("#title-list-" + iCur).html(
                            '<input' +
                                ' type="text"' +
                                ' style="width: 100%"' +
                                ' value="' + userLists[iCur].title + '"' +
                                ' maxlength="100"' +  
                                ' onchange="changeTitleList()"' +
                            '/>'
                        );
                        $("#title-list-" + iCur + ">input").focus();
                    }
                    else if(clickId.substring(0, 9) === "del-list-") {
                        // Удалить список
                    }
                });
            }

            /** 
             * Изменение наименования списка
             */
            function changeTitleList() {
                let newTitle = document.getElementsByTagName("input")[0].value;
                if (userLists[iCur].title != newTitle) {
                    userLists[iCur].title = newTitle;
                    $.ajax({
                        url:    '/Lists',
                        method: 'post',
                        dataType: 'json',
                        async:   true,
                        data: {
                            'action':    'changeTitleList',
                            'listid':    userLists[iCur].id,
                            'listtitle': userLists[iCur].title,
                        },
                        // success: function(response){
                            // userLists = response;
                        // },
                    });
                    $("#title-list-" + iCur).html(userLists[iCur].title);
                }
            }
        </script>
    </body>
</html>
