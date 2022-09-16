var tabName = '';
var errMess = null;

var noImageUser = "/images/users/noUserImage.jpg";
var noImageList = "/images/lists/noListImage.jpg";
var noImageItem = "/images/items/noItemImage.jpg";
var noImageUserPreview = "/images/users/preview/noUserPreview.jpg";
var noImageListPreview = "/images/lists/preview/noListPreview.jpg";
var noImageItemPreview = "/images/items/preview/noItemPreview.jpg";

var lists = [];
var iCur = '';

var items = [];
var iCurI = '';
var idListForItem = '';

var idList   = '';
var idItem   = '';
var imgPath  = '';
var titleImg = '';
var file     = '';
var filterTags = [
    {id:      []},
    {checked: []}
];

$(document).ready(function() {
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

    idList   = $('#idList').html();
    idItem   = $('#idItem').html();
    imgPath  = $('#imgPath').html();
    titleImg = $('#titleImg').html();

    if (idList == 0 && idItem == 0) {
        $('#title-image').html('Пользователь: ' + titleImg);
    }
    else if (idList < 0)  {
        $('#change-img').css('display', 'none');
        $('#del-img').css('display', 'none');
        $('#title-image').html('Изменение изображения возможно только после сохранения<br/>' +
                              'списка "' +titleImg + '"');
    }
    else if (idItem < 0) {
        $('#change-img').css('display', 'none');
        $('#del-img').css('display', 'none');
        $('#title-image').html('Изменение изображения возможно только после сохранения<br/>' +
                              'пункта "' + titleImg + '"');
    }
    else if (idItem == 0) {
        $('#title-image').html('Список "' + titleImg + '"');
        if (imgPath == noImageList) {
            $('#del-img').css('display', 'none');
        }
    }
    else {
        $('#title-image').html('Пункт "' +titleImg + '"');
        if (imgPath == noImageItem) {
            $('#del-img').css('display', 'none');
        }
    }

    showImage(idList, idItem, imgPath, titleImg);
});

/* ------------------ И З О Б Р А Ж Е Н И Я ------------------ /
/** 
 * Вывод изображения
 */
function showImage(idList, idItem, imgPath, titleImg) {
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
                url:         '/Images/uploadImage',
                type:        'post',
                dataType:    'json',
                cache:       false,
                contentType: false,
                processData: false,
                data :       new FormData($form[0]),
                success : function(result) {
                    $('#select-file-form').addClass('hide');
                    if (idList == 0 && idItem == 0) { // Изображение пользователя
                        storageSetItem("idUser", "idUser");
                    }
                    else if (idItem == 0) {            // Изображение списка
                        storageSetItem("idList", idList);
                    }
                    else {                              // Изображение пункта    
                        storageSetItem("idItem", idItem);
                    }
                    $('#del-img').removeAttr('display');
                }
            });
        });        
    });

    // Нажата кнопка "Удалить изображение"
    $('#del-img').click(function() {
        $.ajax({
            url:         '/Images/delImage',
            type:        'post',
            dataType:    'json',
            data : {
                'idList': idList,
                'idItem': idItem,
            },
            success : function(result) {
                $('#select-file-form').addClass('hide');
                $('#del-img').css('display', 'none');
                if (idList == 0 && idItem == 0) { // Изображение пользователя
                    noImage = noImageUser;
                    storageSetItem("idUser", "idUser");
                }
                else if (idItem == 0) {            // Изображение списка
                    storageSetItem("idList", idList);
                    noImage = noImageList;
                }
                else {                              // Изображение пункта    
                    storageSetItem("idItem", idItem);
                    noImage = noImageItem;
                }
                $('#upload-img').attr('src', noImage);
            }
        });

    });

    $('#return-todo').click(function () {
        location.href = "todo.test";
    });
}
