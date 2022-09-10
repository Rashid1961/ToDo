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

    errMess = $.notification.init({
        time:  30,  // время отображения\скрытия (мсек)
        delay: 3000 // сколько будет висеть сообщение (мсек)
    });

    tabName = $('#tabName').html();
    if (tabName === 'todo') {
        $('.container').children().hide();
        window.addEventListener('storage', (event) => {
            if (event.storageArea != localStorage) return;
            let lsKey = event.key;
            if (lsKey === 'idList' || lsKey === 'idItem') {
                let lsVal = storageGetItem(lsKey);
                changeImage(lsKey, lsVal);
                localStorage.removeItem(lsKey);
            }
        });
        showLists();
    }
    else if (tabName === 'image') {
        idList   = $('#idList').html();
        idItem    = $('#idItem').html();
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

    $('#form-lists').show();
    $('.container').show();
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

    // Обработка нажатия кнопок
    $('body').on('click', ':button', function() {
        //   С П И С К И
        let clickId = this.id;
        // Добавить список
        if (clickId === "append-list") {
            $(':button').attr('disabled', true);
            appendList();
        }
        // Развернуть список
        else if(clickId.substring(0, 12) === "expand-list-") {
            iCur = clickId.substring(12);
            expandList(lists[iCur].id);
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

        //   П У Н К Т Ы
        // Сохранить новый пункт
        else if (clickId.substring(0, 10) === "save-item-") {
            iCurI = clickId.substring(10);
            saveNewItem();
        }    
        // Не сохранять новый пункт
        else if (clickId.substring(0, 12) === "cancel-item-") {
            iCurI = clickId.substring(12);
            cancelNewItem();
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
 *  Изменение в выводе количества пунктов
 *  при добавлении / удалении пункта
 */
 function changeNumberItems(idxArr) {
    $('#number-items-list-' + idxArr).html(
        'Пунктов: ' + lists[idxArr].number_items
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
                        'listid':    lists[iCur].id,
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
            'listid': lists[iCur].id,
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
                addOneListFromUserList(iCur);
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
    idListForItem = idList;
    let iChng = -1;
    for (let i = 0; i < lists.length; i++) {
        if (lists[i].id == idList) {
            iChng = i;
            break;
        }
    }
    if (iChng >= 0) {
        $.ajax({
            url:      '/Lists/getImgList',
            method:   'post',
            dataType: 'json',
            async:    true,
            data: {
                'listId':  lists[iChng].id,
            },
            complete: function(response){
                let newImage = response.responseJSON.image;
                let newPreview = response.responseJSON.preview;
                if (newImage.length > 0) {
                    lists[iChng].image = newImage;
                }
                if (newPreview.length > 0) {
                    lists[iChng].preview = newPreview;
                    $('#image-list-' + iChng).children('img').attr('src', lists[iChng].preview);
                }

            },
        });
    
    }
}



/* ------------------ П У Н К Т Ы   С П И С К О В ------------------ /
/** 
 * Вывод пунктов списка
 */
function expandList(listId) {
    idListForItem = listId;
    $.ajax({
        url:    '/Items/getItems',
        method: 'post',
        dataType: 'json',
        async:   false,
        data: {
            'listid': idListForItem,
        },
        success: function(response){
            items = response.items;
            tags = response.tags;
        },
    });

    $("#one-item").empty();
    $("#footer-items").empty();
    $('#form-lists').hide();
    $('#form-items').show();
    
    $("#list-name").html(lists[iCur].title);

    if (items.length == 0) {
        noItems();
    }
    else {
        $('#filter-search').show();
        $('#search-input').css("display", "none");
        $('#search-undo').css("display", "none");
        for (let i = 0; i < items.length; i++) {
            addOneItemFromItems(i);
        }
    }
    $("#items").after(
        '<div' +
            ' class="form-horizontal"' +
            ' id="footer-items"' +
            ' style="margin-bottom: 10px;"' +
        '>' +
            '<div'+
                ' class="block"' +
                ' style="text-align: center; margin: 0;"' +
            '>' +
                '<button' +
                    ' class="btn btn-success"' +
                    ' id="append-item"' + 
                    ' type="button"' +
                    ' style="display: inline; margin-right: 4;"' +
                    '>' +
                    'Добавить пункт' +
                '</button>' +
                '<button' +
                    ' class="btn btn-primary"' +
                    ' id="return-to-lists"' +
                    ' type="button"' +
                    ' style="display: inline; margin-left: 4;"' +
                '>' +
                   'Вернуться к спискам' +
                '</button>' +
            '</div>' +
        '</div>'
    );

    // Обработка нажатия кнопок
    $('#form-items').on('click', ':button', function() {
        let clickId = this.id;
        // Сформировать список фильтра
        if (clickId === 'dropdown-filter') {
            formFilter();
        }
        // Применить фильтр
        else if (clickId === 'apply-filter') {
            $(':button').attr('disabled', true);
            applyFilter();
        }
        // Сбросить фильтр
        else if (clickId === 'undo-filter') {
            undoFilter();
        }
        // Поиск по наименованию пунктов
        else if (clickId === 'search') {
            $(':button').attr('disabled', true);
            $('#search-undo').attr('disabled', false);
            $('#search-input').css("display", "block");
            $('#search-undo').css("display", "block"); 
            $('#search-input').focus();
        }
        // Отменить поиск
        else if (clickId === 'search-undo') {
            undoSearch();
        }
        // Вернуться к спискам
        else if (clickId === "return-to-lists") {
            $("#one-item").empty();
            $('#form-items').hide();
            $('#form-lists').show();
        }
        // Добавить пункт
        else if (clickId === "append-item") {
            $(':button').attr('disabled', true);
            appendItem();
        }
        // Изменить наименование
        else if(clickId.substring(0, 10) === "edit-item-") {
            iCurI = clickId.substring(10);
            $(':button').attr('disabled', true);
            changeTitleItem();
        }
        // Изменить теги
        else if(clickId.substring(0, 10) === "edit-tags-") {
            iCurI = clickId.substring(10);
            $(':button').attr('disabled', true);
            changeTagsItem();
        }
        // Удалить пункт
        else if(clickId.substring(0, 9) === "del-item-") {
            iCurI = clickId.substring(9);
            deleteItem();
        }
    });
}

/** 
 * Строка таблицы при отсутствии пунктов
 */
function noItems(){
    $('#filter-search').hide();
    $("#one-item").append(
        '<tr>' +
            '<td colspan="3" id="no-item" style="font-size: 150%; text-align: center;">' +
                'В списке пока нет ни одного пункта' +
            '</td>' +
        '</tr>'
    );
}

/** 
 * Вывод фильтра
 */
 function formFilter() {
    filterTags.id = [];
    filterTags.checked = [];
    $('#ul-filter').html('');
    let k = 0;
    for (i = 0; i < items.length; i++) {
        for (j = 0; j < items[i].ids_tag.id.length; j++) {
            if (filterTags.id.indexOf(items[i].ids_tag.id[j]) == -1 ) {
                k = filterTags.id.push(items[i].ids_tag.id[j]) - 1;
                filterTags.checked.push(false);
                $('#ul-filter').append(
                    '<li style="padding-left: 3; padding-right: 3;">' +
                        '<label class="form-check-label"  style="margin-bottom: 0">' +
                            '<input' +
                            ' type="checkbox"' +
                            ' class="form-check-input"' +
                            ' style="margin-right: 3;"' +
                            ' onchange="filterTags.checked[' + k +'] = !filterTags.checked[' + k +']">' +
                            items[i].ids_tag.name[j] +
                        '</label>' +
                    '</li>'
                );
            }
        }
    }
    if (k > 0) {
        $('#ul-filter').append(
            '<li style="padding-left: 3; padding-right: 3;">' +
                '<button' +
                    ' id="apply-filter"'+
                    ' type="button"' +
                    ' class="btn btn-block btn-primary"' +
                    'style="margin-bottom: 5;"' +
                '>' +
                    '<i class="glyphicon glyphicon-ok" style="margin-right: 5;">' +
                    '</i>' +
                    'Применить' +
                '</button>' +
            '</li>' +
            '<li style="padding-left: 3; padding-right: 3;">' +
                '<button' +
                    ' id="undo-filter"'+
                    ' type="button"' +
                    ' class="btn btn-block btn-danger"' +
                '>' +
                    '<i class="glyphicon glyphicon-remove" style="margin-right: 5;">' +
                    '</i>' +
                'Сбросить' +
                '</button>' +
            '</li>'
        )
        $('#filter').show();
    }
}

/**
 * Применение фильтра
 */
function applyFilter() {
    $(':button').attr('disabled', false);
    if (filterTags.id.length == 0) {
        return;
    }
    let showItem;
    for (i = 0; i < items.length; i++) {
        showItem = false;
        for (j = 0; j < items[i].ids_tag.id.length; j++) {
            k = filterTags.id.indexOf(items[i].ids_tag.id[j]);
            if (k >= 0) {
                if (filterTags.checked[k] === true) {
                  showItem = true;
                  break;
                }
            }
        }
        if (showItem === true) {
            $('#item-' + i).show();
        }
        else {
            $('#item-' + i).hide();
        }
    }
}

/**
 * Отменена фильтра
 */
function undoFilter() {
    for (i = 0; i < items.length; i++) {
        $('#item-' + i).show();
    }
}

/**
 * Поиск пунктов по наименованию
 */
$("#search-input").on("keyup", function() {
    var value = $(this).val();
    for (i = 0; i < items.length; i++) {
        if (items[i].title.indexOf(value) != 0) {
            $('#item-' + i).hide();
        }
        else {
            $('#item-' + i).show();
        }
    }
});

function undoSearch(){
    $('#search-input').val('');
    for (i = 0; i < items.length; i++) {
        $('#item-' + i).show();
    }
    $(':button').attr('disabled', false);
    $('#search-input').css("display", "none");
    $('#search-undo').css("display", "none");
}

/**
 *  Вывод одного пункта (существующего или нового)
 */
function addOneItemFromItems(idxArr = -1) {
    if (idxArr < 0 || idxArr >= items.length) {
        return false;
    }
    $("#one-item").append(
        '<tr id="item-' + idxArr + '">' +
            '<td style="text-align: center; width: 170px;">' + 
                '<a id="image-item-' + idxArr + '"' +
                    ' href="/Images/showImage?' +
                        '&idList=' + lists[iCur].id   +
                        '&idItem=' + items[idxArr].id +
                        '&imgPath=' + items[idxArr].image + 
                        '&titleImg='  + items[idxArr].title + '"' +
                    ' target="_blank"' +
                '>' +
                    '<img' +
                        ' src=' + items[idxArr].image +
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
                    ' id="title-item-' + idxArr + '"' + 
                    ' class="row text-break"'+
                    ' style="margin: 0; font-size: 175%; word-break: break-word;"' +
                '>' +
                    items[idxArr].title +
                '</div>' +
                '<div'+
                    ' id="tags-item-' + idxArr + '"' +
                    ' class="row"'+
                    ' style="margin: 0; color: #2a5885; word-break: break-word;"' +
                '>' +
                    items[idxArr].tags + 
                '</div>' +
            '</td>' +
            '<td' +
                ' style="text-align: right; vertical-align: middle; width: 150px;"'+
            '>' + 
                '<div class="row" style="margin: 10 10 5 10;">' +
                    '<button' +
                    ' id="edit-item-' + idxArr + '"'+
                    ' type="button"' +
                    ' class="btn btn-block btn-primary"' +
                    '>' +
                        'Изменить наименование' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 5 10;">' +
                    '<button' +
                    ' id="edit-tags-' + idxArr + '"'+
                    ' type="button"' +
                    ' class="btn btn-block btn-primary"' +
                    '>' +
                        'Изменить теги' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 10 10;">' +
                    '<button' +
                        ' id="del-item-' + idxArr + '"' +
                        ' type="button"' +
                        ' class="btn btn-block btn-danger"' +
                    '>' +
                        'Удалить пункт' +
                    '</button>' +
                '</div>' +
            '</td>' +
        '</tr>'
    );
}

/**
 * Добавление пункта
 */
function appendItem() {
    $('#footer-items').hide();
    iCurI = items.push({
        id:           -1,
        id_list:      idListForItem,
        image:        noImageList,
        preview:      noImageItemPreview,
        tags:         '',
        title:        'Новый пункт',
    }) - 1;
    $("#one-item").append(
        '<tr id="item-' + iCurI + '">' +
            '<td style="text-align: center; width: 170px;">' + 
                '<a id="image-item-' + iCurI + '"' +
                    ' href="/Images/showImage?' +
                        '&idList=' +    idListForItem   +
                        '&idItem=' +    items[iCurI].id +
                        '&imgPath=' +   items[iCurI].image + 
                        '&titleImg='  + items[iCurI].title + '"' +
                    ' target="_blank"' +
                '>' +
                    '<img' +
                        ' src=' + items[iCurI].image +
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
                '<div class="row" style="margin: 0">' + 
                    '<input' + 
                        ' id="title-item-new-' + iCurI + '"' + 
                        ' type="text"' +
                        ' style="margi: 0; width: 100%"' +
                        ' value="' + items[iCurI].title + '"' +
                        ' minlength="5"' +
                        ' maxlength="100"' +
                        ' required' +
                    '/>' +
                    '<div style="font-size: 50%; color: #777;">' +
                        'От 5 до 100 символов' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '<td' +
                ' style="text-align: right; vertical-align: middle; width: 150px;"'+
            '>' + 
                '<div class="row" style="margin: 10 10 5 10;">' +
                    '<button' +
                    ' id="save-item-' + iCurI + '"'+
                    ' type="button"' +
                    ' class="btn btn-block btn-primary"' +
                    '>' +
                        'Сохранить пункт' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 10 10;">' +
                    '<button' +
                        ' id="cancel-item-' + iCurI + '"' +
                        ' type="button"' +
                        ' class="btn btn-block btn-danger"' +
                    '>' +
                        'Не добавлять' +
                    '</button>' +
                '</div>' +
            '</td>' +
        '</tr>'
    );
    $("#title-item-new-" + iCur).focus()

    $("#title-item-new-" + iCur).keydown(function(event) {
        if (event.which == 13) {
          event.preventDefault();
        }
    });
}

/**
 * Сохранение нового пункта
 */
function saveNewItem() {
    let newTitle = $('#title-item-new-' + iCurI).val();
    let retValue = 0;
    $.ajax({
        url:      '/Items/appendItem',
        method:   'post',
        dataType: 'json',
        async:    true,
        data: {
            'listId': idListForItem,
            'title':  newTitle,
            'image':  items[iCurI].image,
        },
        complete: function(response){
            $('#item-' + iCurI).remove();
            retValue = response.responseJSON;
            if (retValue >= 0) {
                items[iCurI].id = retValue;
                items[iCurI].title = newTitle;
                items[iCurI].image = noImageItem;
                items[iCurI].preview = noImageItemPreview;
                addOneItemFromItems(iCurI);
                lists[iCur].number_items++;
                changeNumberItems(iCur);
                }
            else {
                errAction('appendItem', retValue);
            }
            if (iCurI == 0) {
                $('#no-item').remove();
                $('#filter-search').show();
                $('#search-input').css("display", "none");
                $('#search-undo').css("display", "none");
            }
            if (iCurI == 0) {
                $('#no-item').remove;
            }
            $(':button').removeAttr('disabled', false);
            $('#footer-items').show();
        },
    });
}

/**
 * Отказ от сохранения нового пункта
 */
 function cancelNewItem() {
    items.pop();
    $('#item-' + iCurI).remove();
    $('#footer-items').show();
    $(':button').removeAttr('disabled', false);
}

/**
 * Изменение наименования пункта
 */
function changeTitleItem() {
    $("#title-item-" + iCurI).html(
        '<div class="row" style="margin: 0">' + 
            '<input' +
                ' id="title-item-edit-' + iCurI + '"' +
                ' type="text"' +
                ' style="margi: 0; width: 100%"' +
                ' value="' + items[iCurI].title + '"' +
                ' minlength="5"' +
                ' maxlength="100"' +
                ' required' +
            '/>' +
            '<div style="font-size: 50%; color: #777;">' +
                 'Введите новое название (от 5 до 100 символов) и нажмите Enter (Escape - для отмены)' +
            '</div>' +
        '</div>'
    );
    $("#title-item-edit-" + iCurI).focus();

    $("#title-item-edit-" + iCurI).keydown(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            let newTitle = $('#title-item-edit-' + iCurI).val();
            let retValue = 0;
            if (items[iCurI].title === newTitle) {
                $('#title-item-' + iCurI).html(items[iCurI].title);
                $(':button').removeAttr('disabled', false);
                }
            else {
                $.ajax({
                    url:      '/Items/changeTitleItem',
                    method:   'post',
                    dataType: 'json',
                    async:    true,
                    data:  {
                        'listid':    idListForItem,
                        'itemid':    items[iCurI].id,
                        'itemtitle': newTitle,
                    },
                    complete: function(response) {
                        retValue = response.responseJSON;
                        if (retValue == 0) {
                            items[iCurI].title = newTitle;
                            $('#title-item-' + iCurI).html(items[iCurI].title);
                            $(':button').removeAttr('disabled', false);
                        }
                        else {
                            errAction('changeTitleItem', retValue);
                        }
                    }
                });
            }
    }
        else if (event.which == 27) {
            event.preventDefault();
            $('#title-item-' + iCurI).html(items[iCurI].title);
            $(':button').removeAttr('disabled', false);
        }
    });

}

/**
 * Изменение тегов пункта
 */
function changeTagsItem() {
    $("#tags-item-" + iCurI).html(
        '<div class="row" style="margin: 0">' + 
            '<input' +
                ' id="tags-item-edit-' + iCurI + '"' +
                ' type="text"' +
                ' style="margi: 0; width: 100%"' +
                ' value="' + items[iCurI].tags + '"' +
                ' required' +
            '/>' +
            '<div style="font-size: 75%; color: #777;">' +
                 'Измените теги и нажмите Enter (Escape - для отмены) <br/>' +
                 '(каждый тег должен начинаться с символа `#`)' +
            '</div>' +
        '</div>'
    );
    $("#tags-item-edit-" + iCurI).focus();

    $("#tags-item-edit-" + iCurI).keydown(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            let newTags = $('#tags-item-edit-' + iCurI).val();
            let retValue = 0;
            if (items[iCurI].tags === newTags) {
                $('#tags-item-' + iCurI).html(items[iCurI].tags);
                $(':button').removeAttr('disabled', false);
            }
            else {
                $.ajax({
                    url:      '/Items/changeTagsItem',
                    method:   'post',
                    dataType: 'json',
                    async:    true,
                    data:  {
                        'itemid': items[iCurI].id,
                        'tags':   newTags,
                    },
                    complete: function(response) {
                        retValue = response.responseJSON;
                        if (retValue == 0) {
                            items[iCurI].tags = newTags;
                            $.ajax({
                                url:    '/Items/getItems',
                                method: 'post',
                                dataType: 'json',
                                async:   false,
                                data: {
                                    'listid': idListForItem,
                                },
                                success: function(response){
                                    items = response.items;
                                    tags = response.tags;
                                },
                            });
                            $('#tags-item-' + iCurI).html(items[iCurI].tags);
                            $(':button').removeAttr('disabled', false);
                        }
                        else {
                            errAction('changeTagsItem', retValue);
                        }
                    }
                });
            }
        }
        else if (event.which == 27) {
            event.preventDefault();
            $('#tags-item-' + iCurI).html(items[iCurI].tags);
            $(':button').removeAttr('disabled', false);
        }
    });
}

/**
 * Удаление пункта
 */
function deleteItem() {
    let retValue = 0;
    $.ajax({
        url:      '/Items/deleteItem',
        method:   'post',
        dataType: 'json',
        async:    true,
        data: {
            'listid': idListForItem,
            'itemid': items[iCurI].id,
        },
        complete: function(response){
            retValue = response.responseJSON;
            if (retValue == 0) {
                items.splice(iCurI, 1);
                $('#item-' + iCurI).remove();
                lists[iCur].number_items--;
                changeNumberItems(iCur);
                if (items.length == 0) {
                    noItems();
                }
            }
            else {
                errAction('deleteItem', retValue);
            }
        },
    });
}

/**
 * Изменение preview пункта на "основной" вкладке
 * при изменении изображения на "дополнительной" вкладке
 */
function changeImageItem(idItem) {
    let iChng = -1;
    for (let i = 0; i < items.length; i++) {
        if (items[i].id == idItem) {
            iChng = i;
            break;
        }
    }
    if (iChng >= 0) {
        $.ajax({
            url:      '/Items/getImgItem',
            method:   'post',
            dataType: 'json',
            async:    true,
            data: {
                'itemId':  items[iChng].id,
            },
            complete: function(response){
                let newImage = response.responseJSON.image;
                let newPreview = response.responseJSON.preview;
                if (newImage.length > 0) {
                    items[iChng].image = newImage;
                }
                if (newPreview.length > 0) {
                    items[iChng].preview = newPreview;
                    $('#image-item-' + iChng).children('img').attr('src', items[iChng].preview);
                }

            },
        });
    
    }
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
                }
                else if (idItem == 0) {            // Изображение списка
                    noImage = noImageList;
                }
                else {                              // Изображение пункта
                    noImage = noImageItem;
                }
                $('#upload-img').attr('src', noImage);
            }
        });

    });
}

/* ------------------ О Б Щ Е Е ------------------ /
/**
 * Вывод сообщений при ошибках
 */
function errAction(action, response, needExit = false) {
    let actions = [
        {act: 'changeTitleList',   message: 'Изменение наименования списка',},
        {act: 'deleteList',        message: 'Удаление списка',},
        {act: 'appendList',        message: 'Добавление нового списка',},
        {act: 'checkNewListTitle', message: 'Проверка наименования нового списка',},
        {act: 'appendItem',        message: 'Добавление нового пункта',},
        {act: 'changeTitleItem',   message: 'Изменение наименования пункта',},
        {act: 'deleteItem',        message: 'Удаление пункта',},
        {act: 'changeTagsItem',    message: 'Изменение тегов пункта',},
    ];
    let responses = [
        {resp: '-1', message: 'Список не принадлежит текущему пользователю',},
        {resp: '-2', message: 'Список отсутствует в базе данных',},
        {resp: '-3', message: 'Дублирование наименования',},
        {resp: '-4', message: 'Длина наименования меньше 5 символов',},
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


/**
 * Изменение preview на "основной" вкладке
 * при изменении изображения на "дополнительной" вкладке 
 */
function changeImage(lsKey, lsVal) {
    if (lsKey === 'idList') {
        changeImageList(lsVal);
    }
    else if (lsKey === 'idItem') {
        changeImageItem(lsVal);
    }
}

/**
 * Записать значение в localStorage
 */
function storageSetItem(key, value) {
    const localStorage = window.localStorage;
    if (!localStorage) {
        return false;
    }
    localStorage.setItem(key, JSON.stringify(value));
}

/**
 * Получить значение из localStorage
 */
function storageGetItem(key, defaultValue = null) {
    const localStorage = window.localStorage;
    if (!localStorage) {
        return defaultValue;
    }
    const value = localStorage.getItem(key);
    if (value === null) {
        return defaultValue;
    }
    return JSON.parse(value);
}

/**
 * Удалить ключ из localStorage
 */
function storageDelItem(key) {
    const localStorage = window.localStorage;
    if (!localStorage) {
        return defaultValue;
    }
    const value = localStorage.removeItem(key);
    if (value === null) {
        return defaultValue;
    }
    return JSON.parse(value);
}
