var noImageUser = "/images/users/noUserImage.jpg";
var noImageList = "/images/lists/noListImage.jpg";
var noImageItem = "/images/items/noItemImage.jpg";

var idList   = '';
var idItem   = '';
var imgPath  = '';
var titleImg = '';

$(document).ready(function() {
    idUser    = $('#id').html();
    idList    = $('#idList').html();
    idItem    = $('#idItem').html();
    imgPath   = $('#imgPath').html();
    titleImg  = $('#titleImg').html();
    hrefRet = $('#hrefRet').html();

    if (idList == 0 && idItem == 0) {
        $('#title-image').html('Пользователь: ' + titleImg);
    }
    else if (idList < 0)  {             // Новый (несохранённый) список
        $('#change-img').css('display', 'none');
        $('#goBack').html(
            '<i class="fa fa-reply" style="margin-right: 5;"></i>' +
            'Вернуться к спискам'
        );
        $('#del-img').css('display', 'none');
        $('#title-image').html('Изменение изображения возможно только после сохранения нового списка');
    }
    else if (idItem < 0) {              // Новый (несохранённый) пункт
        $('#change-img').css('display', 'none');
        $('#goBack').html(
            '<i class="fa fa-reply" style="margin-right: 5;"></i>' +
            'Вернуться к пунктам'
        );
        $('#del-img').css('display', 'none');
        $('#title-image').html('Изменение изображения возможно только после сохранения нового пункта');
    }
    else if (idItem == 0) {             // Существующий список
        $('#title-image').html('Список "' + titleImg + '"');
        $('#goBack').html(
            '<i class="fa fa-reply" style="margin-right: 5;"></i>' +
            'Вернуться к спискам'
        );
        if (imgPath == noImageList) {
            $('#del-img').css('display', 'none');
        }
    }
    else {                              // Существующий пункт
        $('#title-image').html('Пункт "' +titleImg + '"');
        $('#goBack').html(
            '<i class="fa fa-reply" style="margin-right: 5;"></i>' +
            'Вернуться к пунктам'
        );
        if (imgPath == noImageItem) {
            $('#del-img').css('display', 'none');
        }
    }

    $('#goBack').attr('href', hrefRet);


    showImage(idList, idItem); //, imgPath, titleImg);
});

/* ------------------ И З О Б Р А Ж Е Н И Я ------------------ /
/** 
 * Вывод изображения
 */
function showImage(idList, idItem) { //, imgPath, titleImg) {
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
        $('#selected-submit').removeAttr('disabled', false);
    });

    // Нажата кнопка "Сохранить"
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
                        storageSetItem("idUserChangeImage", idUser);
                    }
                    else if (idItem == 0) {            // Изображение списка
                        storageSetItem("idListChangeImage", idList);
                    }
                    else {                              // Изображение пункта    
                        storageSetItem("idItemChangeImage", idItem);
                    }
                    $('#del-img').css('display', 'inline');
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
                    storageSetItem("idUserChangeImage", idUser);
                }
                else if (idItem == 0) {            // Изображение списка
                    storageSetItem("idListChangeImage", idList);
                    noImage = noImageList;
                }
                else {                              // Изображение пункта    
                    storageSetItem("idItemChangeImage", idItem);
                    noImage = noImageItem;
                }
                $('#upload-img').attr('src', noImage);
            }
        });

    });
}
