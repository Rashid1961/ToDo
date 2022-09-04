var tabName = '';
var errMess = null;

var noImageUser = "/images/users/noUserImage.jpg";
var noImageList = "/images/lists/noListImage.jpg";
var noImageItem = "/images/items/noItemImage.jpg";
var noImageUserPreview = "/images/users/preview/noUserImage.jpg";
var noImageListPreview = "/images/lists/preview/noListImage.jpg";
var noImageItemPreview = "/images/items/preview/noItemImage.jpg";
var lists = [];
var iCur = '';

var idList   = '';
var idItem   = '';
var imgPath  = '';
var titleImg = '';
var file     = '';
var url      = '';

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

    errMess = $.notification.init({
        time:  30,  // время отображения\скрытия (мсек)
        delay: 3000 // сколько будет висеть сообщение (мсек)
    });

    tabName = $('#tabName').html();
    if (tabName === 'todo') {
        $('.container').children().hide();
        showLists();
    }
    else if (tabName === 'image') {
        idList   = $('#idList').html();
        idItem    = $('#idItem').html();
        imgPath  = $('#imgPath').html();
        titleImg = $('#titleImg').html();
        url      = '/Images/uploadImage';

        console.log('idList = "' + idList + '"');
        console.log('idItem = "' + idItem + '"');
        console.log('imgPath = "' + imgPath + '"');
        console.log('titleImg = "' + titleImg + '"');

        if (idList == 0 && idItem == 0) {
            $('#title-image').html('Пользователь: ' + titleImg);
        }
        else if (idList < 0)  {
            $('#title-image').html('Изменение изображения возможно только после сохранения<br/>' +
                                  'списка "' +titleImg + '"');
            $('#change-img').css('display', 'none');
            $('#del-img').css('display', 'none');
        }
        else if (idItem < 0) {
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
        showImage();
    }
});

/* ------------------ С П И С К И ------------------ /
/**
 *  Заполнение и отображение таблицы списков
 */
function showLists() {
    $.ajax({
        url:    '/Lists/getLists',
        method: 'post',
        dataType: 'json',
        async:   false,
        success: function(response){
            lists = response;
        },
    });

    $("#one-list").empty();
    if (lists.length == 0) {
        noLists();
    }
    else {
        for (let i = 0; i < lists.length; i++) {
            addOneListFromUserList(i);
        }
    }
    $("#lists").after(
        '<div'+
            ' class="block"' +
            ' style="text-align: center; margin: 0;"' +
        '>' +
            '<button id="append-list" type="button" class="btn btn-success">' +
                'Добавить список' +
            '</button>' +
        '</div>'
    );
    $('#form-lists').show();
    $('.container').show();

    // Обработка нажатия кнопок
    $(":button").click(function() {
        let clickId = this.id;
        // Добавить список
        if (clickId === "append-list") {
            appendList();
        }
        // Развернуть список
        else if(clickId.substring(0, 12) === "expand-list-") {
            iCur = clickId.substring(12);
            expandList();
        }
        // Изменить "Наименование списка"
        else if(clickId.substring(0, 10) === "edit-list-") {
            iCur = clickId.substring(10);
            $(':button').attr('disabled', true);
            changeTitleList();
        }
        // Удалить список
        else if(clickId.substring(0, 9) === "del-list-") {
            iCur = clickId.substring(9);
            deleteList();
        }
    });
}

/** 
 * Строка таблицы при отсутствии списков
 */
function noLists(){
    $("#one-list").append(
        '<tr>' +
            '<td colspan="3" style="font-size: 150%; text-align: center;">' +
                'У Вас пока нет ни одного списка' +
            '</td>' +
        '</tr>'
    );
}

/** 
 * Изменение наименования списка
 */
 function changeTitleList() {
    $('#number-items-list-' + iCur).hide();
    $("#title-list-" + iCur).html(
        '<div class="row" style="margin: 0">' + 
            '<input' +
                ' id="title-list-edit-' + iCur + '"' +
                ' type="text"' +
                ' style="margi: 0; width: 100%"' +
                ' value="' + lists[iCur].title + '"' +
                ' minlength="5"' +
                ' maxlength="100"' +
                ' required' +
            '/>' +
            '<div style="font-size: 50%; color: #777;">' +
                 'Введите новое название (от 5 до 100 символов) и нажмите Enter или Escape - для отмены' +
            '</div>' +
        '</div>'
    );
    $("#title-list-edit-" + iCur).focus();

    $("#title-list-edit-" + iCur).keydown(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            let newTitle = $('#title-list-edit-' + iCur).val();
            let err = 0;
            if (lists[iCur].title === newTitle) {
                errAction('changeTitleList', -3);
            }
            else {
                $.ajax({
                    url:      '/Lists/changeTitleList',
                    method:   'post',
                    dataType: 'json',
                    async:    true,
                    data:  {
                        'listid':    lists[iCur].id,
                        'listtitle': newTitle,
                    },
                    complete: function(response) {
                        err = response.responseJSON;
                        if (err == 0) {
                            lists[iCur].title = newTitle;
                        }
                        else {
                            errAction('changeTitleList', err);
                        }
                        $('#title-list-' + iCur).html(lists[iCur].title);
                        $('#number-items-list-' + iCur).show();
                        $(':button').removeAttr('disabled', false);
                    }
                });
            }
        }
        else if (event.which == 27) {
            event.preventDefault();
            $('#title-list-' + iCur).html(lists[iCur].title);
            $('#number-items-list-' + iCur).show();
            $(':button').removeAttr('disabled', false);
        }
    });
}

/**
 * Удаление списка и всех его пунктов
 */
function deleteList() {
    let err = 0;
    $.ajax({
        url:      '/Lists/deleteList',
        method:   'post',
        dataType: 'json',
        async:    true,
        data: {
            'listid': lists[iCur].id,
        },
        complete: function(response){
            err = response.responseJSON;
            if (err == 0) {
                lists.splice(iCur, 1);
                $('#list-' + iCur).remove();
                if (lists.length == 0) {
                    noLists();
                }
            }
            else {
                errAction('deleteList', err);
            }
        },
    });
}

 /**
 * Добавление списка
 */
function appendList() {
    $('#append-list').hide();
    iCur = lists.push({
        id:           -1,
        title:        'Новый список',
        image:        noImageList,
        number_items: 0,
    }) - 1;
    $("#one-list").append(
        '<tr id="list-' + iCur + '">' +
            '<td style="text-align: center; width: 170px;">' + 
                '<a id="image-list-' + iCur + '"' +
                ' href="/Images/showImage?' +
                    'idList=' +   lists[iCur].id +
                    '&idItem=' + '0' +
                    '&imgPath=' + lists[iCur].image + 
                    '&titleImg='  + lists[iCur].title + '"' +
                    ' target="_blank"' +
                '>' +
                    '<img' +
                        ' src=' + lists[iCur].image +
                        ' width="150px"' +
                        ' height="150px"' +
                        ' alt="Изображения нет"' +
                        ' title="Посмотреть в отдельной вкладке"' +
                    '/>' +
                '</a>' +
            '</td>' +
            '<td' +
                ' style="vertical-align: middle;"' +
            '>' +
                '<div' +
                    ' id="title-list-' + iCur + '"' + 
                    ' class="row text-break"'+
                    ' style="margin: 0; font-size: 175%; word-break: break-word;"' +
                '>' +
                    '<div class="row" style="margin: 0">' + 
                        '<input' + 
                            ' id="title-list-new-' + iCur + '"' + 
                            ' type="text"' +
                            ' style="margi: 0; width: 100%"' +
                            ' value="' + lists[iCur].title + '"' +
                            ' minlength="5"' +
                            ' maxlength="100"' +
                            ' required' +
                        '/>' +
                        '<div style="font-size: 50%; color: #777;">' +
                            'От 5 до 100 символов' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '<td' +
                ' style="text-align: right; vertical-align: middle; width: 150px;"' +
            '>' + 
                '<div class="row" style="margin: 10 10 5 10;">' +
                    '<button' +
                        ' id="save-list-' + iCur + '"'+
                        ' type="button"' +
                        ' class="btn btn-block btn-primary"' +
                    '>' +
                        'Сохранить список' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 10 10;">' +
                    '<button' +
                        ' id="cancel-list-' + iCur + '"' +
                        ' type="button"' +
                        ' class="btn btn-block btn-danger"' +
                    '>' +
                        'Не добавлять' +
                    '</button>' +
                '</div>' +
            '</td>' +
        '</tr>'
    );
    $("#title-list-new-" + iCur).focus()

    $("#title-list-new-" + iCur).keydown(function(event) {
        if (event.which == 13) {
          event.preventDefault();
        }
    });

    
    // Обработка нажатия кнопок
    $("#save-list-" + iCur).click(function() {
        // Сохранить новый список
        saveNewList();
    });
    $("#cancel-list-" + iCur).click(function() {
        // Не сохранять новый список
        cancelNewList();
    });
}

/**
 * Сохранение нового списка
 */
function saveNewList() {
    let newTitle = $('#title-list-new-' + iCur).val();
    let err = 0;
    $.ajax({
        url:      '/Lists/appendList',
        method:   'post',
        dataType: 'json',
        async:    true,
        data: {
            'title':  newTitle,
            'image':  lists[iCur].image,
        },
        complete: function(response){
            err = response.responseJSON;
            if (err > 0) {
                lists[iCur].id = err;
                lists[iCur].title = newTitle;
                lists[iCur].image = noImageList;
                $('#list-' + iCur).remove();
                addOneListFromUserList(iCur);
            }
            else {
                errAction('appendList', err);
            }
        },
    });
}

/**
 *  Вывод одного списка (существующего или нового)
 */
function addOneListFromUserList(idxArr = -1) {
    if (idxArr < 0 || idxArr >= lists.length) {
        return false;
    }
    $("#one-list").append(
        '<tr id="list-' + idxArr + '">' +
            '<td style="text-align: center; width: 170px;">' + 
                '<a id="image-list-' + idxArr + '"' +
                    ' href="/Images/showImage?' +
                        '&idList=' +   lists[idxArr].id +
                        '&idItem=' + '0' +
                        '&imgPath=' + lists[idxArr].image + 
                        '&titleImg='  + lists[idxArr].title + '"' +
                    ' target="_blank"' +
                '>' +
                    '<img' +
                        ' src=' + lists[idxArr].image +
                        ' width="150px"' +
                        ' height="150px"' +
                        ' alt="Изображения нет"' +
                        ' title="Посмотреть в отдельной вкладке"' +
                    '/>' +
                '</a>' +
            '</td>' +
            '<td' +
                ' style="vertical-align: middle;"' +
            '>' +
                '<div' +
                    ' id="title-list-' + idxArr + '"' + 
                    ' class="row text-break"'+
                    ' style="margin: 0; font-size: 175%; word-break: break-word;"' +
                '>' +
                    lists[idxArr].title +
                '</div>' +
                '<div'+
                    ' id="number-items-list-' + idxArr + '"' +
                    ' class="row"'+
                    ' style="margin: 0; color: #777;"' +
                '>' +
                    'Пунктов: ' + lists[idxArr].number_items +
                '</div>' +
            '</td>' +
            '<td' +
                ' style="text-align: right; vertical-align: middle; width: 150px;"'+
            '>' + 
                '<div class="row" style="margin: 10 10 5 10;">' +
                    '<button' +
                    ' id="expand-list-' + idxArr + '"'+
                    ' type="button"' +
                    ' class="btn btn-block btn-primary"' +
                    '>' +
                        'Развернуть список' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 5 10;">' +
                    '<button' +
                    ' id="edit-list-' + idxArr + '"'+
                    ' type="button"' +
                    ' class="btn btn-block btn-primary"' +
                    '>' +
                        'Изменить наименование' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 10 10;">' +
                    '<button' +
                        ' id="del-list-' + idxArr + '"' +
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

/**
 * Отказ от сохранения нового списка
 */
function cancelNewList() {
    lists.pop();
    $('#list-' + iCur).remove();
    $('#append-list').show();
}

/* ------------------ П У Н К Т Ы   С П И С К О В ------------------ /
/** 
 * Вывод элементов списка
 */
 function expandList() {
    $.ajax({
        url:    '/Items/getItems',
        method: 'post',
        dataType: 'json',
        async:   false,
        data: {
        },
        success: function(response){
            items = response;
        },
    });

    $("#one-item").empty();
    if (items.length == 0) {
        noItems();
    }
    else {
        for (let i = 0; i < items.length; i++) {
            addOneListFromUserList(i);
        }
    }
    $("#items").after(
        '<div'+
            ' class="block"' +
            ' style="text-align: center; margin: 0;"' +
        '>' +
            '<button id="append-list" type="button" class="btn btn-success">' +
                'Добавить список' +
            '</button>' +
        '</div>'
    );

    $('#form-lists').hide();
    $('#form-items').show();

}


/* ------------------ И З О Б Р А Ж Е Н И Я ------------------ /
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

/* ------------------ О Б Щ Е Е ------------------ /
/**
 * Вывод сообщений при ошибках
 */
function errAction(action, response, needExit = false) {
    let actions = [
        {act: 'changeTitleList',   message: 'Изменение наименования списка'},
        {act: 'deleteList',        message: 'Удаление списка',},
        {act: 'appendList',        message: 'Добавление нового списка',},
        {act: 'checkNewListTitle', message: 'Проверка наименования нового списка'},
    ];
    let responses = [
        {resp: '-1', message: 'Список не принадлежит текущему пользователю'},
        {resp: '-2', message: 'Список отсутствует в базе данных'},
        {resp: '-3', message: 'Дублирование наименования списка'},
        {resp: '-4', message: 'Длина наименования меньше 5 символов'},
    ];
    
    let idxAct = actions.findIndex(a => a.act == action);
    let errAct = '"' + (idxAct == -1 ? ('Неизвестное (' + action + ')') :
                                  actions[idxAct].message) + '"';

    let idxResp = responses.findIndex(r => r.resp == response);
    let errResp = '"' + (idxResp == -1 ? ('Неизвестная ('  + response + ')'):
                                      responses[idxResp].message) + '"';
    errMess.show(
        'error',
        'Ошибка выполнения действия:<br/>' +
        errAct + '<br/>' +
        'Причина:<br/>' +
        errResp
    );
    if (needExit) {
        $('.container').hide();
        $("#exit")[0].click();
    }
}
