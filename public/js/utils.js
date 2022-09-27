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
 * Строка таблицы при отсутствии данных (списков / пунктов)
 * 
 * @param {String} 'list' / 'item'
 */
function noData(param){
    if (param === 'item') {
        $('#filter-search').hide();
    }
    $("#one-" + param).append(
        '<tr>' +
            '<td colspan="3" id= "no-' + param +'s" style="font-size: 150%; text-align: center;">' +
                (param === 'list' ? 'У Вас пока нет ни одного списка' : 'В списке пока нет ни одного пункта') +
            '</td>' +
        '</tr>'
    );
}


/**
 * Формирование ячейки таблицы с preview одного списка / пункта
 * 
 * @param {Array}   arrData - массив списков (lists) или пунктов (items)
 * @param {Integer} idxArr  - индекс массива, указанного в качестве первого параметра
 * @param {Integer} idList
 * @param {Integer} idItem (0 - для списка)
 * @param {Integer} hrefRet - ссылка для возврата
 */
function tdPreview(arrData, idxArr, idList, idItem, hrefRet) {
    return  '<td style="text-align: center; width: 170px;">' + 
                '<a id="preview-' + (idItem === 0 ? 'list' : 'item') + '-' + idxArr + '"' +
                    ' href="/Images/showImage?' +
                        '&idList=' + idList +
                        '&idItem=' + idItem +
                        '&imgPath=' + arrData[idxArr].image + 
                        '&titleImg='  + arrData[idxArr].title.replace(' ', '%20') +
                        '&hrefRet=' + hrefRet + '"' +
                    ' target="_blank"' +
                '>' +
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
 * Изменение preview списка / пункта
 * при изменении изображения
 * 
 * @param {Array}   lists / items
 * @param {Integer} idList
 * @param {Integer} idItem (0 - для списка)
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
