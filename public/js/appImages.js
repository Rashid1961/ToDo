/* ------------------ И З О Б Р А Ж Е Н И Я ------------------ */
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
    hrefRet   = $('#hrefRet').html();

    if (idList == 0 && idItem == 0) {
        $('#title-image').html('Пользователь: ' + titleImg);
    }
    else {
        let itIsList;
        if (idList < 0 || idItem < 0)  {
            // Новый (несохранённый) список (idList < 0) или пункт (idItem < 0)
            itIsList = idList < 0 ? true : false;
            $('#change-img').css('display', 'none');
            $('#del-img').css('display', 'none');
            $('#title-image').html('Изменение изображения возможно только после сохранения нового ' + (itIsList ? 'списка' : 'пункта'));
        }
        else {
            // Существующий список (idItem == 0) или пункт
            itIsList = idItem == 0 ? true : false;
            $('#title-image').html((itIsList ? 'Список' : 'Пункт') + ' "' + titleImg + '"');
            if (imgPath == (itIsList ? noImageList : noImageItem)) {
                $('#del-img').css('display', 'none');
            }
        }
        $('#goBack').html(
            '<i class="fa fa-reply" style="margin-right: 5;"></i>' +
            'Вернуться к ' + (itIsList ? 'спискам' : 'пунктам')
        );
}

    $('#goBack').attr('href', hrefRet);


    showImage(idList, idItem); 
});

/** 
 * Вывод изображения
 */
function showImage(idList, idItem) {
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
