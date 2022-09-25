/* ------------------ П У Н К Т Ы   С П И С К О В ------------------ */
var noImageItem = "/images/items/noItemImage.jpg";
var noImageItemPreview = "/images/items/preview/noItemPreview.jpg";

var items = [];
var iCurI = '';

var idList       = '';
var hrefLists    = ''
var titleList    = '';
var number_items = '';

var idItem   = '';
var imgPath  = '';
var titleImg = '';
var hrefItem = '';
var filterTags = [
    {id:      []},
    {checked: []}
];

$(document).ready(function() {
    window.addEventListener('storage', (event) => {
        if (event.storageArea != localStorage) return;
        let lsKey = event.key;
        if (lsKey === 'idItemChangeImage') {        // Изменилось изображение пункта - необходимо перерисовать preview
            let lsVal = storageGetItem(lsKey);
            changeImage(items, idList, lsVal);
            localStorage.removeItem(lsKey);
        }
    });

    idList       = $('#idList').html();
    titleList    = $('#titleList').html();
    number_items = $('#number_items').html();

    hrefItems = $(location).attr('href');
    hrefLists = hrefItems.substring(0, hrefItems.indexOf('Items/'));

    expandList(idList);
});

/** 
 * Вывод пунктов списка
 */
function expandList(idList) {
    $.ajax({
        url:    '/Items/getItems',
        method: 'post',
        dataType: 'json',
        async:   false,
        data: {
            'idList': idList,
        },
        success: function(response){
            items = response.items;
            tags = response.tags;
        },
    });

    $("#one-item").empty();
    $("#footer-items").empty();
    
    $("#list-name").html(titleList);

    if (items.length == 0) {
        noData('item');
    }
    else {
        $('#filter-search').show();
        $('#search-input').css("display", "none");
        $('#search-undo').css("display", "none");
        for (let i = 0; i < items.length; i++) {
            addOneItemFromItems(i, idList, hrefItems);
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
                    ' style="display: inline; margin-right: 4;"' +
                    ' type="button"' +
                    '>' +
                    '<i class="fa fa-plus" style="margin-right: 5;"></i>' +
                    'Добавить пункт' +
                '</button>' +
                '<a' +
                    ' class="btn btn-primary"' +
                    ' style="display: inline; margin-left: 4;"' +
                    ' type="button"' +
                    ' href="' + hrefLists + '"' +
                '>' +
                '   <i class="fa fa-reply" style="margin-right: 5;"></i>' +
                    'Вернуться к спискам' +
                '</a>' +
            '</div>' +
        '</div>'
    );

    // Обработка нажатия кнопок
    $('body').on('click', ':button', function() {
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
        }
        // Добавить новый пункт
        else if (clickId === "append-item") {
            $(':button').attr('disabled', true);
            appendItem(idList);
        }
        // Сохранить новый пункт
        else if (clickId.substring(0, 10) === "save-item-") {
            iCurI = clickId.substring(10);
            saveNewItem(idList);
        }    
        // Не сохранять новый пункт
        else if (clickId.substring(0, 12) === "cancel-item-") {
            iCurI = clickId.substring(12);
            cancelNewItem();
        }
        // Изменить наименование
        else if(clickId.substring(0, 10) === "edit-item-") {
            iCurI = clickId.substring(10);
            $(':button').attr('disabled', true);
            changeTitleItem(idList);
        }
        // Изменить теги
        else if(clickId.substring(0, 10) === "edit-tags-") {
            iCurI = clickId.substring(10);
            $(':button').attr('disabled', true);
            changeTagsItem(idList);
        }
        // Удалить пункт
        else if(clickId.substring(0, 9) === "del-item-") {
            iCurI = clickId.substring(9);
            deleteItem(idList);
        }
    });
}

/** 
 * Формирование и вывод фильтра
 */
function formFilter() {
    filterTags.id = [];         // Массив id тегов для фильтра
    filterTags.checked = [];    // Массив checkbox'ов для выбора тегов
    $('#ul-filter').html('');
    let k = -1;                 // текущий индекс массивов filterTags.id и filterTags.checked
    for (i = 0; i < items.length; i++) {
        if ('ids_tag' in items[0]) {
            for (j = 0; j < items[i].ids_tag.id.length; j++) {
                if (filterTags.id.indexOf(items[i].ids_tag.id[j]) == -1 ) {
                    k = filterTags.id.push(items[i].ids_tag.id[j]) - 1;
                    filterTags.checked.push(false);
                    $('#ul-filter').append(
                        '<li style="padding-left: 5; padding-right: 3;">' +
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
    }
    if (k >= 0) {
        $('#ul-filter').append(
            '<li style="padding-left: 3; padding-right: 3;">' +
                '<button' +
                    ' class="btn btn-block btn-primary"' +
                    ' id="apply-filter"'+
                    ' style="margin-bottom: 5; text-align: left;"' +
                    ' type="button"' +
                '>' +
                    '<i class="fa fa-check" style="margin-right: 5;">' +
                    '</i>' +
                    'Применить' +
                '</button>' +
            '</li>' +
            '<li style="padding-left: 3; padding-right: 3;">' +
                '<button' +
                    ' class="btn btn-block btn-danger"' +
                    ' id="undo-filter"'+
                    ' style="text-align: left;"' +
                    ' type="button"' +
                '>' +
                    '<i class="fa fa-times" style="margin-right: 5;">' +
                    '</i>' +
                'Сбросить' +
                '</button>' +
            '</li>'
        )
    }
    else {
        $('#ul-filter').append(
            '<li style="padding-left: 3; padding-right: 3;">' +
                '<button' +
                    ' class="btn btn-block btn-danger"' +
                    ' id="undo-filter"'+
                    ' style="text-align: left;"' +
                    ' type="button"' +
                '>' +
                    '<i class="fa fa-times" style="margin-right: 5;">' +
                    '</i>' +
                'Тегов ещё нет' +
                '</button>' +
            '</li>'
        )
    }
    $('#filter').show();
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
        if (items[i].title.includes(value)) {
            $('#item-' + i).show();
        }
        else {
            $('#item-' + i).hide();
        }
    }
});

/**
 * Отменена поиска
 */
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
function addOneItemFromItems(idxArr = -1, idList, hrefItems) {
    if (idxArr < 0 || idxArr >= items.length) {
        return false;
    }
    $("#one-item").append(
        '<tr id="item-' + idxArr + '">' +
            '<td style="text-align: center; width: 170px;">' + 
                '<a id="image-item-' + idxArr + '"' +
                    ' href="/Images/showImage?' +
                        '&idList=' + idList   +
                        '&idItem=' + items[idxArr].id +
                        '&imgPath=' + items[idxArr].image + 
                        '&titleImg='  + items[idxArr].title.replace(' ', '%20') +
                        '&hrefRet=' + hrefItems + '"' +
                    ' target="_blank"' +
                '>' +
                    '<img' +
                        ' src=' + items[idxArr].preview +
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
                        ' class="btn btn-block btn-primary"' +
                        ' id="edit-item-' + idxArr + '"'+
                        ' style="text-align: left"' +
                        ' type="button"' +
                    '>' +
                        '<i class="fa fa-pencil" style="margin-right: 5;"></i>' +
                        'Изменить наименование' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 5 10;">' +
                    '<button' +
                        ' class="btn btn-block btn-primary"' +
                        ' id="edit-tags-' + idxArr + '"'+
                        ' style="text-align: left"' +
                        ' type="button"' +
                    '>' +
                        '<i class="fa fa-slack" style="margin-right: 5;"></i>' +
                        'Изменить теги' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 10 10;">' +
                    '<button' +
                        ' class="btn btn-block btn-danger"' +
                        ' id="del-item-' + idxArr + '"' +
                        ' style="text-align: left"' +
                        ' type="button"' +
                    '>' +
                        '<i class="fa fa-trash-o" style="margin-right: 5;"></i>' +
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
function appendItem(idList) {
    $('#footer-items').hide();
    iCurI = items.push({
        id:           -1,
        id_list:      idList,
        image:        noImageItem,
        preview:      noImageItemPreview,
        tags:         '',
        title:        'Новый пункт',
    }) - 1;
    $("#one-item").append(
        '<tr id="item-' + iCurI + '">' +
            '<td style="text-align: center; width: 170px;">' + 
                '<a id="image-item-' + iCurI + '"' +
                    ' href="/Images/showImage?' +
                        '&idList=' +    idList   +
                        '&idItem=' +    items[iCurI].id +
                        '&imgPath=' +   items[iCurI].image + 
                        '&titleImg='  + items[iCurI].title.replace(' ', '%20') +
                        '&hrefRet=' + hrefItems + '"' +
                    ' target="_blank"' +
                '>' +
                    '<img' +
                        ' src=' + items[iCurI].preview +
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
                        ' class="btn btn-block btn-primary"' +
                        ' id="save-item-' + iCurI + '"'+
                        ' style="text-align: left"' +
                        ' type="button"' +
                    '>' +
                        '<i class="fa fa-floppy-o" style="margin-right: 5;"></i>' +
                        'Сохранить пункт' +
                    '</button>' +
                '</div>' +
                '<div class="row" style="margin: 5 10 10 10;">' +
                    '<button' +
                        ' class="btn btn-block btn-danger"' +
                        ' id="cancel-item-' + iCurI + '"' +
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
    $("#title-item-new-" + iCurI).focus()

    $("#title-item-new-" + iCurI).keydown(function(event) {
        if (event.which == 13) {
          event.preventDefault();
        }
    });
}

/**
 * Сохранение нового пункта
 */
function saveNewItem(idList) {
    let newTitle = $('#title-item-new-' + iCurI).val();
    let retValue = 0;
    $.ajax({
        url:      '/Items/appendItem',
        method:   'post',
        dataType: 'json',
        async:    true,
        data: {
            'idList': idList,
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
                addOneItemFromItems(iCurI, idList, $(location).attr('href'));
                changeNumberItems(idList, ++number_items);
            }
            else {
                errAction('appendItem', retValue);
            }
            if (iCurI == 0) {
                $('#no-items').remove();
                $('#filter-search').show();
                $('#search-input').css("display", "none");
                $('#search-undo').css("display", "none");
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
function changeTitleItem(idList) {
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
                        'idList':    idList,
                        'idItem':    items[iCurI].id,
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
function changeTagsItem(idList) {
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
                        'idItem': items[iCurI].id,
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
                                    'idList': idList,
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
function deleteItem(idList) {
    let retValue = 0;
    $.ajax({
        url:      '/Items/deleteItem',
        method:   'post',
        dataType: 'json',
        async:    true,
        data: {
            'idList': idList,
            'idItem': items[iCurI].id,
        },
        complete: function(response){
            retValue = response.responseJSON;
            if (retValue == 0) {
                items.splice(iCurI, 1);
                $('#item-' + iCurI).remove();
                
                changeNumberItems(idList, --number_items);
                if (items.length == 0) {
                    noData('item');
                }
            }
            else {
                errAction('deleteItem', retValue);
            }
        },
    });
}

/**
 *  Изменение количества пунктов в списке при добавлении / удалении списка
 *  для изменения в перечне списков
 */
function changeNumberItems(idList, counter) {
    storageSetItem("idListChangeNumberItems", idList + ':' + counter);
}
