var noImageList = "/images/lists/noListImage.jpg";
var noImageListPreview = "/images/lists/preview/noListPreview.jpg";
var lists = [];
var iCur = '';
var hrefLists = '';

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
    window.addEventListener('storage', (event) => {
        if (event.storageArea != localStorage) return;
        let lsKey = event.key;
        let lsVal = storageGetItem(lsKey);
        if (lsKey === 'idListChangeImage') {
            // let lsVal = storageGetItem(lsKey);
            changeImageList(lsVal);
            localStorage.removeItem(lsKey);
        }
        else if (lsKey === 'idListChangeNumberItems') {
            changeNumberItemsInList(lsVal);
            localStorage.removeItem(lsKey);
        }
    });

    hrefLists = $(location).attr('href');

    showLists();
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
            addOneListFromUserList(i, hrefLists);
        }
    }
    $("#lists").after(
        '<div'+
            ' class="block"' +
            ' style="text-align: center; margin: 0;"' +
        '>' +
            '<button id="append-list" type="button" class="btn btn-success">' +
            '<i class="fa fa-plus" style="margin-right: 5;"></i>' +
            'Добавить список' +
            '</button>' +
        '</div>'
    );

    // Обработка нажатия кнопок
    $('body').on('click', ':button', function() {
        //   С П И С К И
        let clickId = this.id;
        // Добавить список
        if (clickId === "append-list") {
            $(':button').attr('disabled', true);
            appendList();
        }
        // Изменить наименование
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
            '<td colspan="3" id= "no-lists" style="font-size: 150%; text-align: center;">' +
                'У Вас пока нет ни одного списка' +
            '</td>' +
        '</tr>'
    );
}

/**
 *  Вывод одного списка (существующего или нового)
 */
function addOneListFromUserList(idxArr = -1, hrefLists) {
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
                        '&titleImg='  + lists[idxArr].title.replace(' ', '%20') +
                        '&hrefRet=' + hrefLists + '"' +
                    ' target="_blank"' +
                '>' +
                    '<img' +
                        ' src=' + lists[idxArr].preview +
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
                    '<a' +
                    ' class="btn btn-block btn-primary"' +
                    ' style="text-align: left"' +
                    ' type="button"' +
                    ' href="/Items/expandList/' + lists[idxArr].id + '"' +
                    '>' +
                    '<i class="fa fa-expand" style="margin-right: 5;"></i>' +
                        'Развернуть список' +
                    '</a>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 5 10;">' +
                    '<button' +
                    ' class="btn btn-block btn-primary"' +
                    ' id="edit-list-' + idxArr + '"' +
                    ' style="text-align: left"' +
                    ' type="button"' +
                    '>' +
                    '<i class="fa fa-pencil" style="margin-right: 5;"></i>' +
                        'Изменить наименование' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 10 10;">' +
                    '<button' +
                        ' class="btn btn-block btn-danger"' +
                        ' id="del-list-' + idxArr + '"' +
                        ' style="text-align: left"' +
                        ' type="button"' +
                    '>' +
                    '<i class="fa fa-trash-o" style="margin-right: 5;"></i>' +
                        'Удалить список' +
                    '</button>' +
                '</div>' +
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
                 'Введите новое название (от 5 до 100 символов) и нажмите Enter (Escape - для отмены)' +
            '</div>' +
        '</div>'
    );
    $("#title-list-edit-" + iCur).focus();

    $("#title-list-edit-" + iCur).keydown(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            let newTitle = $('#title-list-edit-' + iCur).val();
            let retValue = 0;
            if (lists[iCur].title === newTitle) {
                $('#title-list-' + iCur).html(lists[iCur].title);
                $('#number-items-list-' + iCur).show();
                $(':button').removeAttr('disabled', false);
            }
            else {
                $.ajax({
                    url:      '/Lists/changeTitleList',
                    method:   'post',
                    dataType: 'json',
                    async:    true,
                    data:  {
                        'idList':    lists[iCur].id,
                        'listtitle': newTitle,
                    },
                    complete: function(response) {
                        retValue = response.responseJSON;
                        if (retValue == 0) {
                            lists[iCur].title = newTitle;
                            $('#title-list-' + iCur).html(lists[iCur].title);
                            $('#number-items-list-' + iCur).show();
                            $(':button').removeAttr('disabled', false);
                        }
                        else {
                            errAction('changeTitleList', retValue);
                        }
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
    let retValue = 0;
    $.ajax({
        url:      '/Lists/deleteList',
        method:   'post',
        dataType: 'json',
        async:    true,
        data: {
            'idList': lists[iCur].id,
        },
        complete: function(response){
            retValue = response.responseJSON;
            if (retValue == 0) {
                lists.splice(iCur, 1);
                $('#list-' + iCur).remove();
                if (lists.length == 0) {
                    noLists();
                }
            }
            else {
                errAction('deleteList', retValue);
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
        preview:      noImageListPreview,
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
                    '&titleImg='  + lists[iCur].title.replace(' ', '%20') +
                    '&hrefRet=' + hrefLists + '"' +
                    ' target="_blank"' +
                '>' +
                    '<img' +
                        ' src=' + lists[iCur].preview +
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
                        ' class="btn btn-block btn-primary"' +
                        ' id="save-list-' + iCur + '"'+
                        ' style="text-align: left"' +
                        ' type="button"' +
                    '>' +
                        '<i class="fa fa-floppy-o" style="margin-right: 5;"></i>' +
                        'Сохранить список' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 10 10;">' +
                    '<button' +
                        ' class="btn btn-block btn-danger"' +
                        ' id="cancel-list-' + iCur + '"' +
                        ' style="text-align: left"' +
                        ' type="button"' +
                    '>' +
                        '<i class="fa fa-times" style="margin-right: 5;"></i>' +
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
    let retValue = 0;
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
            retValue = response.responseJSON;
            $('#list-' + iCur).remove();
            if (retValue > 0) {
                lists[iCur].id = retValue;
                lists[iCur].title = newTitle;
                lists[iCur].image = noImageList;
                lists[iCur].preview = noImageListPreview;
                addOneListFromUserList(iCur, hrefLists);
            }
            else {
                errAction('appendList', retValue);
            }
            if (iCur == 0) {
                $('#no-lists').remove();
            }
            $(':button').removeAttr('disabled', false);
            $('#append-list').show();
        },
    });
}

/**
 * Отказ от сохранения нового списка
 */
function cancelNewList() {
    lists.pop();
    $('#list-' + iCur).remove();
    $('#append-list').show();
    $(':button').removeAttr('disabled', false);
}

/**
 * Изменение preview списка на "основной" вкладке
 * при изменении изображения на "дополнительной" вкладке
 */
function changeImageList(idList) {
    //let iChng = -1;
    for (let i = 0; i < lists.length; i++) {
        if (lists[i].id == idList) {
            //iChng = i;
            $.ajax({
                url:      '/Lists/getImgList',
                method:   'post',
                dataType: 'json',
                async:    true,
                data: {
                    'idList':  lists[i].id,
                },
                complete: function(response){
                    let newImage = response.responseJSON.image;
                    let newPreview = response.responseJSON.preview;
                    if (newImage.length > 0) {
                        lists[i].image = newImage;
                        $('#image-list-' + i).attr('href',
                            '/Images/showImage?' +
                            '&idList=' +   lists[i].id +
                            '&idItem=' + '0' +
                            '&imgPath=' + lists[i].image + 
                            '&titleImg='  + lists[i].title.replace(' ', '%20')
                        );
                    }
                    if (newPreview.length > 0) {
                        lists[i].preview = newPreview;
                        $('#image-list-' + i).children('img').attr('src', lists[i].preview);
                    }
                },
            });
            break;
        }
    }
}

/**
 *  Изменение в выводе количества пунктов
 *  при добавлении / удалении пункта
 */
 function changeNumberItemsInList(params) {
    let idxSeparator = params.indexOf(':');
    let idListChange = params.substring(0, idxSeparator);
    let counter = params.substring(idxSeparator + 1);
    for (let i = 0; i < lists.length; i++) {
        if (lists[i].id = idListChange) {
            lists[i].number_items = counter;
            $('#number-items-list-' + i).html(
                'Пунктов: ' + lists[i].number_items
            );
            break;
        }
    }
}
