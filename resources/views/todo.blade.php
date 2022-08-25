<?php
?>

<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />

        <title>ToDo</title>
    </head>
    <body>

        <!-- <img
            src="images/users/noUserImage.jpg"
            alt="картинка где-то затерялась("
            width="150px"
            height="150px" 
        /> -->

        <div class="container panel panel-default ">
            <!-- <h2 class="panel-heading">id: {{$id}} name:{{$name}}</h2> -->
            <form id="main">

                <div id="img-list">
                </div>

                <div class="form-group col-md-4">
                    <button class="btn btn-success" id="lists-user">Загрузить</button>
                </div>
                <div class="form-group col-md-4">
                    <!-- <button class="btn btn-success" id="exit">Выход</button> -->
                    <a href="{{ url('/auth/logout') }}">Выход</a>
                </div>

            </form>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


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
            //$('#lists-user').click(function() {
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
            //});
            imgList =
                '<img' +
                    ' src=' + userLists[0].image +
                    ' alt="картинка где-то затерялась("' +
                    ' width="150px"' +
                    ' height="150px"' + 
                '/>';
            $("#img-list").append(imgList);

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