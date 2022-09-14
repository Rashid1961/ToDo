var errMess = $.notification.init({
        time:  30,  // время отображения\скрытия (мсек)
        delay: 3000 // сколько будет висеть сообщение (мсек)
    });
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
