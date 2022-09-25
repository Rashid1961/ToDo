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
* @param {string} 'list' / 'item'
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
 * Строка таблицы при отсутствии списков
 * /
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
 * Строка таблицы при отсутствии пунктов
 * /
 function noItems(){
    $("#one-item").append(
        '<tr>' +
            '<td colspan="3" id="no-items" style="font-size: 150%; text-align: center;">' +
                'В списке пока нет ни одного пункта' +
            '</td>' +
        '</tr>'
    );
}*/



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
