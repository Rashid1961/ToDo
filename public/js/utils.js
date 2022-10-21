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

// Параметры вывода сообщений об ошибках
var errMess = $.notification.init({
        time:  30,  // время отображения\скрытия (мсек)
        delay: 3000 // сколько будет висеть сообщение (мсек)
    });
/* ------------------------------------------------------------- */

/** 
 * Формирование строки таблицы при отсутствии данных (списков / пунктов)
 * 
 * @param {String} 'list' / 'item'
 */
function noData(param) {
    if (param === 'item') {
        $('#filter-search').hide();
    }
    $("#one-" + param).append(
        '<tr>' +
            '<td colspan="3" id= "no-' + param +'s" style="font-size: 150%; text-align: center;">' +
                (param === 'list' ?
                    'У Вас нет ни одного списка'
                :
                    'В списке нет ни одного пункта'
                ) +
            '</td>' +
        '</tr>'
    );
}

/**
 * Формирование ячейки строки таблицы с preview одного списка / пункта
 * 
 * @param {Array}   arrData - массив списков (lists) или пунктов (items)
 * @param {Integer} idxArr  - индекс массива, указанного в качестве первого параметра
 * @param {Integer} idList  - id списка, для которого формируются данные
 * @param {Integer} idItem  - id тега, для которого формируются данные (0 - для списка)
 * @param {Integer} hrefRet - ссылка для возврата из просмотра полноформатного изображения
 */
function tdPreview(arrData, idxArr, idList, idItem, hrefRet) {
    return  '<td style="text-align: center; width: 170px;">' + 
                // Ссылка на полноформатное изображение
                '<a id="preview-' + (idItem === 0 ? 'list' : 'item') + '-' + idxArr + '"' +
                    ' href="/Images/showImage?' +
                        '&idList=' + idList +
                        '&idItem=' + idItem +
                        '&imgPath=' + arrData[idxArr].image + 
                        '&titleImg='  + arrData[idxArr].title.replace(' ', '%20') +
                        '&hrefRet=' + hrefRet + '"' +
                    ' target="_blank"' +
                '>' +
                    // Preview
                    '<img' +
                        ' src=' + arrData[idxArr].preview +
                        ' width="150px"' +
                        ' height="150px"' +
                        ' alt="Изображения нет"' +
                        ' title="Посмотреть в отдельной вкладке"' +
                    '/>' +
                '</a>' +
            '</td>';
}

/**
 * Формирование ячейки строки таблицы с наименованием одного списка (с количеством пунктов)
 * или пункта (с тегами)
 * 
 * @param {Array}   arrData - массив списков (lists) или пунктов (items)
 * @param {Integer} idxArr  - индекс массива, указанного в качестве первого параметра
 * @param {Integer} idItem  - id тега, для которого формируются данные (0 - для списка)
 * @param {Boolean} editing - редактирование (true) или отображение (false)
 */
function tdName(arrData, idxArr, idItem, editing) {
    return  '<td' +
                ' style="vertical-align: middle;"' +
                '>' +
                '<div' +    // Наименование
                    ' id="title-' + (idItem === 0 ? 'list' : 'item') + '-' + idxArr + '"' + 
                    ' class="row text-break"'+
                    ' style="margin: 0; font-size: 175%; word-break: break-word;"' +
                '>' +
                (editing === true ?
                (                                                       // Редактирование
                    '<div class="row" style="margin: 0">' + 
                        '<input' + 
                            ' id="title-' + (idItem === 0 ? 'list' : 'item') + '-new-' + idxArr + '"' + 
                            ' type="text"' +
                            ' style="margin: 0; width: 100%"' +
                            ' value="' + arrData[idxArr].title + '"' +
                            ' minlength="5"' +
                            ' maxlength="100"' +
                            ' required' +
                        '/>' +
                        '<div style="font-size: 50%; color: #777;">' +
                            'От 5 до 100 символов' +
                        '</div>' +
                    '</div>' +
                '</div>'
                )
                :
                (                                                       // Отображение
                    arrData[idxArr].title +
                '</div>' +
                (idItem === 0 ?
                    (       // Количетсво пунктов для списков
                        '<div' +
                            ' id="number-items-list-' + idxArr + '"' +
                            ' class="row"' +
                            ' style="margin: 0; color: #777;"' +
                        '>' +
                            'Пунктов: ' + arrData[idxArr].number_items +
                        '</div>'
                    )
                :
                    (       // или  теги для пунктов
                        '<div' +
                            ' id="tags-item-' + idxArr + '"' +
                            ' class="row"' +
                            ' style="margin: 0; color: #2a5885; word-break: break-word;"' +
                        '>' +
                            arrData[idxArr].tags + 
                        '</div>'
                    )
                ))) +
            '</td>';
}

/**
 * Формирование ячейки строки таблицы с кнопками меню для одного списка или пункта
 * 
 * @param {Integer} idxArr  - индекс массива списков (lists) или пунктов (items)
 * @param {Array}   arrMenu - массив пунктов меню, каждый элемент массива имеет формат:
 *      {
 *          type:  'a' | 'button',         // Тип элемента меню: ссылка ('a') или кнопка ('button')
 *          class: 'btn ...',              // Класс кнопки, определяющий её цвет
 *          attr:  '<href>' | 'id button', // Для ссылки ('a') - значение атрибута href,
 *                                         // для кнопки ('button') - значение атрибута id (без idxArr)
 *          icon:  'fa fa-...',            // Иконка на элементе
 *          name:  '<наименование>'        // Наименование элемента
 *      }
 */
function tdMenu(idxArr, arrMenu) {
    let retVal =
        '<td' +
            ' style="text-align: right; vertical-align: middle; width: 150px;"'+
        '>'; 
    for (let i = 0; i < arrMenu.length; i++) {
        retVal +=
            '<div class="row" style="margin: ' + 
                (i == 0 ?                  '10 10  5 10' :            // Первый элемент меню
                (i == arrMenu.length - 1 ?  '5 10 10 10' :            // Последний элемент меню
                                            '5 10  5 10')) + ';"' +   // Средние элементы меню
            '>' +
                '<' + arrMenu[i].type +
                    ' class="' + arrMenu[i].class + '"' +
                    ' style="text-align: left"' +
                    ' type="button"' +
                    (arrMenu[i].type === 'a' ?
                    (' href="' + arrMenu[i].attr + '"')
                    :
                    (' id="' + arrMenu[i].attr + idxArr + '"')) +
                '>' +
                '<i class="' + arrMenu[i].icon + '" style="margin-right: 5;"></i>' +
                    arrMenu[i].name +
                '</' + arrMenu[i].type + '>' +
            '</div>';
    }
    retVal +=
        '</td>';
    return retVal;
}

/**
 * Изменение preview списка или пункта при изменении изображения
 * 
 * @param {Array}   lists  - массив списков (lists) или пунктов (items)
 * @param {Integer} idList - id списка, для которого формируются данные
 * @param {Integer} idItem - id тега, для которого формируются данные (0 - для списка)
 */
function changePreview(arrData, idList, idItem) {
    let idSearch    = idItem === 0 ? idList              : idItem;
    let url         = idItem === 0 ? '/Lists/getImgList' : '/Items/getImgItem';
    let data        = idItem === 0 ? {'idList': idList}  : {'idItem': idItem};
    let htmlTagName = idItem === 0 ? '#preview-list-'      : '#preview-item-'; 

    for (let i = 0; i < arrData.length; i++) {
        if (arrData[i].id == idSearch) {
            $.ajax({
                url:      url,
                method:   'post',
                dataType: 'json',
                async:    true,
                data:     data,
                complete: function(response){
                    let newImage = response.responseJSON.image;
                    let newPreview = response.responseJSON.preview;
                    if (newImage.length > 0) {
                        arrData[i].image = newImage;
                        $(htmlTagName + i).attr('href',
                            '/Images/showImage?' +
                            '&idList=' +  idList +
                            '&idItem=' +  (idItem === 0 ? '0' : idItem) +
                            '&imgPath=' + newImage + 
                            '&titleImg='  + arrData[i].title.replace(' ', '%20')
                        );
                    }
                    if (newPreview.length > 0) {
                        arrData[i].preview = newPreview;
                        $(htmlTagName + i).children('img').attr('src', newPreview);
                    }
                },
            });
            break;
        }
    }
}



/** 
 * Формирование и вывод списка пользователей
 * для выбора по нажатию кнопки "Поделиться"
 * 
 * @param {Integer} idList - id списка, для которого формируются данные
 * @param {Integer} idItem - id тега, для которого формируются данные (0 - для списка)
*/
 function usersForShare() {
    users.id = [];         // Массив id тегов пользователей
    users.checked = [];    // Массив checkbox'ов для выбора пользователей
    $('#ul-filter').html('');
    let k = -1;                 // текущий индекс массивов users.id и users.checked
    for (i = 0; i < items.length; i++) {
        if ('ids_tag' in items[0]) {
            for (j = 0; j < items[i].ids_tag.id.length; j++) {
                if (filterTags.id.indexOf(items[i].ids_tag.id[j]) == -1 ) {
                    k = filterTags.id.push(items[i].ids_tag.id[j]) - 1;
                    filterTags.checked.push(selectedIdTags.indexOf(filterTags.id[k]) >= 0);
                    $('#ul-filter').append(
                        '<li style="padding-left: 5; padding-right: 3;">' +
                            '<label class="form-check-label"  style="margin-bottom: 0">' +
                                '<input' +
                                ' type="checkbox"' +
                                ' class="form-check-input"' +
                                ' style="margin-right: 3;"' +
                                (filterTags.checked[k] ? ' checked' : '') +
                                ' onchange="filterTags.checked[' + k + '] = !filterTags.checked[' + k + ']">' +
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
 * Вывод сообщений при ошибках
 * 
 * @param {String}           action   - действие, при котором возникла ошибка
 * @param {String | Integer} response - код ошибки
 * @param {Boolean}          needExit - необходимость завершить работу (true) или продолжить (false)
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
        {act: 'selectImage',       message: 'Сохранение изображения',},
    ];
    let responses = [
        {resp: '-1', message: 'Список не принадлежит текущему пользователю',},
        {resp: '-2', message: 'Список отсутствует в базе данных',},
        {resp: '-3', message: 'Дублирование наименования',},
        {resp: '-4', message: 'Длина наименования меньше 5 символов',},
        {resp: '-5', message: 'Файл не выбран',},
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
 * Записать значение в localStorage
 * 
 * key, value - пара ключ - значение
 */
function storageSetItem(key, value) {
    const localStorage = window.localStorage;
    if (!localStorage) {
        return false;
    }
    localStorage.setItem(key, JSON.stringify(value));
}

/**
 * Получить значение из localStorage по ключу
 */
function storageGetItem(key) {
    const localStorage = window.localStorage;
    if (!localStorage) {
        return null;
    }
    const value = localStorage.getItem(key);
    if (value === null) {
        return null;
    }
    return JSON.parse(value);
}

/**
 * Удалить ключ (и значение, соответственно) из localStorage
 */
function storageDelItem(key) {
    const localStorage = window.localStorage;
    if (localStorage) {
        localStorage.removeItem(key);
    }
}
