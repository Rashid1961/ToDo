<?php
?>

<html lang='ru'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1 shrink-to-fit=no'>
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
        <div id='tabName'  hidden>image</div>
        <div id='idList'   hidden>{{$idList}}</div>
        <div id='idItem'   hidden>{{$idItem}}</div>
        <div id='imgPath'  hidden>{{$imgPath}}</div>
        <div id='titleImg' hidden>{{$titleImg}}</div>

        <div class = 'container' id = 'image' style = 'margin-left: auto;'>
                <div class='form-inline'  style='text-align: center; margin-top: 20'>
                    <!-- "Изменить изображение", "Удалить изображение", "Выбор файла" -->
                    <div class='form-horizontal'  style='margin-bottom: 10px;'>
                        <button
                            id='change-img'
                            type='button'
                            class='btn btn-primary'
                            style='display: inline; margin-right: 2;'
                        >
                           Изменить изображение
                        </button>

                        <button
                            id='del-img' 
                            type='button'
                            class='btn btn-danger'
                            style='display: inline; margin-left: 2;'
                        >
                            Удалить изображение
                        </button>
                        
                        <!-- Выбор файла для загрузки -->
                        <form
                            class="form-inline hide"
                            id='select-file-form'
                            enctype="multipart/form-data"
                            Style="margin-top: 10;"
                        >
                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                            <input type="hidden" name="idList" value="{{$idList}}">
                            <input type="hidden" name="idItem" value="{{$idItem}}">
                            <div class="form-group" style="margin-right: 5;">
                                <input
                                    class='btn btn-primary'
                                    type='file'
                                    id='selected-image'
                                    name='selected-image'
                                    accept='image/*'
                                />
                            </div>
                            <div class="form-group" style="margin-left: 5;">
                                <button
                                    type="submit"
                                    class="btn btn-success"
                                    id='selected-submit'
                                >
                                    Сохранить
                                </button>
                            </div>
		                </form>
                    </div>

                    <!-- Наименование изображения -->
                    <div
                        class='row'
                        id='title-image'
                        style='font-size: 175%; color:#000; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'
                    >
                        {{$titleImg}}
                    </div>

                    <!-- Изображение -->
                    <div class='img-preview' style='margin-bottom: 10px;'> 
                        <img
                            id='upload-img'
                            src='{{$imgPath}}'
                            alt='Изображения нет'
                        />
                    </div>

                </div>
            <!-- </form>                 -->
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
        <script src="/js/jquery.notification.min.js"></script>
        <script src="/js/appToDo.js"></script>
        <!-- <script src='/js/appImage.js'></script> -->
    </body>
</html>