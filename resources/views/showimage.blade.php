<?php
?>

<html lang='ru'>
    <head>
        <!-- <meta charset='utf-8'>
        <meta name='csrf-token' content='{{ csrf_token() }}'>
        <meta name='viewport' content='width=device-width, initial-scale=1 shrink-to-fit=no'> -->
        <title>Изображение</title>
        <!-- Fonts -->
        <link
            rel='stylesheet'
            href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css'
            integrity='sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+'
            crossorigin='anonymous'
        >
        <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700'>
        <!-- Styles -->
        <link
            rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css'
            integrity='sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7'
            crossorigin='anonymous'
        >
        <!--{{-- <link href='{{ elixir('css/app.css') }}' rel='stylesheet'> --}} -->
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
        <div id='elemId'   hidden>{{$id}}</div>
        <div id='whatShow' hidden>{{$whatShow}}</div>
        <div id='imgPath'  hidden>{{$image}}</div>
        <div id='elemName' hidden>{{$name}}</div>

        <div class = 'container' id = 'image' style = 'margin-left: auto;'>
            <form class='form-horizontal' id='form-image'>        
                <div class='form-group'  style='text-align: center;'>
                    <!-- Наименование изображения -->
                    <div
                        class = 'row'
                        style = 'font-size: 175%; color:#000; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'
                    >
                        {{$whatShow}}: {{$name}}
                    </div>

                    <!-- Изображение -->
                    <div class='img-preview' style='margin-bottom: 10px;'> 
                        <img
                            id='upload-img'
                            src='{{$image}}'
                            alt='Изображения нет'
                        />
                    </div>

                    <!-- Кнопки "Изменить изображение" и "Удалить изображение" -->
                    <div class='row'  style='margin-bottom: 10px;'>
                        <div class='col-md-6 col-md-offset-3'>
                            <div class='col-md-6'> 
                                <button
                                    id='change-img'
                                    type='button'
                                    class='btn btn-block btn-primary'
                                >
                                   Изменить изображение
                                </button>
                            </div>
                            <div class='col-md-6'> 
                                <button
                                    id='del-img' + 
                                    type='button'
                                    class='btn btn-block btn-danger'
                                >
                                    Удалить изображение
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Выбор файла изображения -->
                    <div
                        class='fileupload fileupload-new'
                        id='select-file'
                        data-provides='fileupload'
                        style='display: none;'
                    >
                        <span class='btn btn-primary btn-file'>
                            <input type='file' id='local-file' name='file' accept='image/*'/>
                        </span>
                        <span class='fileupload-preview'></span>
                        <a
                            href='#'
                            class='close fileupload-exists'
                            data-dismiss='fileupload'
                            style='float: none'
                        >
                            ×
                        </a>
                    </div>
                </div>
            </form>                
        </div>

        <!-- JavaScripts -->
        <script
            src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js'
            integrity='sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb'
            crossorigin='anonymous'>
        </script>
        <script
            src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js'
            integrity='sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS'
            crossorigin='anonymous'>
        </script>
        <script src='/js/appImage.js'></script>
    </body>
</html>