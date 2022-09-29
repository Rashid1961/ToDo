/* ------------------ С П И С К И ------------------ */
var noImageList = "/images/lists/noListImage.jpg";
var noImageListPreview = "/images/lists/preview/noListPreview.jpg";
var lists = [];
var iCur = '';
var hrefLists = '';
var arrListMenuShow = [             // Массив для формировария меню для каждого списка при выводе
    {
        type:  'a',
        class: 'primary',
        attr:  '/Items/expandList/',
        icon:  'fa fa-expand',
        name:  'Развернуть список',
    },
    {
        type:  'button',
        class: 'primary',
        attr:  'edit-list-',
        icon:  'fa fa-pencil',
        name:  'Изменить наименование',
    },
    {
        type:  'button',
        class: 'danger',
        attr:  '"del-list-',
        icon:  'fa fa-trash-o',
        name:  'Удалить список',
    },
];

var arrListMenuAppend = [           // Массив для формировария при добавлении списка
    {
        type:  'button',
        class: 'primary',
        attr:  'save-list-',
        icon:  'fa fa-floppy-o',
        name:  'Сохранить список',
    },
    {
        type:  'button',
        class: 'danger',
        attr:  '"cancel-list-',
        icon:  'fa fa-times',
        name:  'Не добавлять',
    },
];

$(document).ready(function() {
    // Прослушивание событий для обработки переменных из local storage
    window.addEventListener('storage', (event) => {
        if (event.storageArea != localStorage) return;
        let lsKey = event.key;
        let lsVal = storageGetItem(lsKey);
        if (lsKey === 'idListChangeImage') {            // Изменилось изображение списка - необходимо перерисовать preview
            changePreview(lists, lsVal, 0);
            localStorage.removeItem(lsKey);
        }
        else if (lsKey === 'idListChangeNumberItems') { // Изменилось количество пунктов списка - необходимо перерисовать
            changeNumberItemsInList(lsVal);
            localStorage.removeItem(lsKey);
        }
    });

    hrefLists = $(location).attr('href');

    showLists();
});

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

    // У пользователя нет списков
    if (lists.length == 0) {
        noData('list');
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
 *  Вывод одного списка (существующего или нового)
 */
function addOneListFromUserList(idxArr = -1, hrefLists) {
    if (idxArr < 0 || idxArr >= lists.length) {
        return false;
    }
    arrListMenuShow[1].attr = '/Items/expandList/' + lists[idxArr].id;
    $("#one-list").append(
        '<tr id="list-' + idxArr + '">' +
            tdPreview(lists, idxArr, lists[idxArr].id, 0, hrefLists) +  // Preview
            tdName(lists, idxArr, 0, false) +                           // Наименование
            tdMenu(idxArr, arrListMenuShow) +
        /*
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
        */
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
                    noData('list');
                }
                else {
                    iCur--;
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
            tdPreview(lists, iCur, lists[iCur].id, 0, hrefLists) +  // Preview
            tdName(lists, iCur, 0, true) +                          // Наименование
            tdMenu(idxArr, arrListMenuAppend) +
            /*
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
            */

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
                if (iCur > 0) {
                    iCur--;
                }
            }
            if (lists.length == 0) {
                noData('list');
            }
            else {
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
    if (iCur > 0) {
        iCur--;
    }
    if (lists.length == 0) {
        noData('list');
    }
    else {
        $('#no-lists').remove();
    }
    $('#append-list').show();
    $(':button').removeAttr('disabled', false);
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
