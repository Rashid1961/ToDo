var idList   = '';
var idItem   = '';
var imgPath  = '';
var titleImg = '';
var file     = '';
var url      = '';
$(document).ready(function() {
    idList   = $('#idList').html();
    idItem    = $('#idItem').html();
    imgPath  = $('#imgPath').html();
    titleImg = $('#titleImg').html();
    url      = '/Images/uploadImage';
    if (idList == 0 && idItem == 0) {
        $('title-image').html('Пользователь: ' + titleImg);
    }
    else if (idList < 0)  {
        $('title-image').html('Изменение изображения возможно только после сохранения<br/>' +
                              'списка "' +titleImg + '"');
        $('#change-img').addClass('hide');
        $('#del-img').addClass('hide');
    }
    else if (idItem < 0) {
        $('title-image').html('Изменение изображения возможно только после сохранения<br/>' +
                              'пункта "' + titleImg + '"');
    }
    else if (idIist == 0) {
        $('title-image').html('Список "' +titleImg + '"');
    }
    else {
        $('title-image').html('Пункт "' +titleImg + '"');
    }

    showImage();
});

/** 
 * Вывод изображения
 */
function showImage() {
    // Нажата кнопка "Изменить изображение"
    $('#change-img').click(function() {
        $('#select-file-form').removeClass('hide');
        $("#selected-image").focus();
    });

    // Файл выбран (input) - выводим для просмотра
    $('#selected-image').on('change', function(){
        var files = this.files;
        if (files && files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#upload-img').attr('src', e.target.result);
            }
            reader.readAsDataURL(files[0]);
        }
    });

    // Нажата кнопка "Загрузить"
    $('#selected-submit').click(function() {
        $('#select-file-form').on('submit', function(e) {
            e.preventDefault();
        
            let $form = $(e.currentTarget);
            $.ajax({
                url:         url, //$form.attr('action'),
                type:        'post', //$form.attr('method'),
                dataType:    'json',
                cache:       false,
                contentType: false,
                processData: false,
                data :       new FormData($form[0]),
                success : function(result) {
                    $('#select-file-form').addClass('hide');
                }
            });
        });        
    });

}