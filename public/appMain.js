var noImageUser = "images/users/noUserImage.jpg";
var noImageList = "images/lists/noListImage.jpg";
var noImageItem = "images/items/noItemImage.jpg";
var userLists = [];
var iCur = '';
$(document).ready(function() {
    $('.container').hide();
    $('.container').children().hide();
    $('#err-anim').hide();
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
    $('#title-list-edit-' + iCur).blur(function() {
        let newTitle = $('#title-list-edit-' + iCur).val();
        if (newTitle.length < 5) {
            return false;
        }
        if (userLists[iCur].title != newTitle) {
            let action = 'changeTitleList';
            $.ajax({
                url:      '/Lists',
                method:   'post',
                dataType: 'json',
                async:    true,
                data:  {
                    'action':    action,
                    'listid':    userLists[iCur].id,
                    'listtitle': newTitle,
                },
                success: function(response){
                    if (response == 0) {
                        userLists[iCur].title = newTitle;
                        $('#title-list-' + iCur).html(userLists[iCur].title);
                        $("#title-list-" + iCur).html(userLists[iCur].title);
                        $('#number-items-list-' + iCur).show();
                                }
                    else {
                        errAction(action, response);
                        $("#title-list-edit-" + iCur).focus();
                    }
                },
            });
        }
    });
        
    showLists();
});

/**
 *  Заполнение и отображение таблицы списков
 */
function showLists() {
    $.ajax({
        url:    '/Lists',
        method: 'post',
        dataType: 'json',
        async:   false,
        data: {
            'action': 'getLists'
        },
        success: function(response){
            userLists = response;
        },
    });

    $("#one-list").empty();
    if (userLists.length == 0) {
        noLists();
    }
    else {
        for (let i = 0; i < userLists.length; i++) {
            $("#one-list").append(
                '<tr id="list-' + i + '">' +
                    '<td style="text-align: center; width: 170px;">' + 
                        '<a id="image-list-' + i + '"' +
                            ' href="/ShowImage?whatShow=Лист&image='
                                    + userLists[i].image + '&name=' + userLists[i].title + '"' +
                            ' target="_blank"' +
                        '>' +
                            '<img' +
                                ' src=' + userLists[i].image +
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
                            ' id="title-list-' + i + '"' + 
                            ' class="row text-break"'+
                            ' style="margin: 0; font-size: 175%; word-break: break-word;"' +
                        '>' +
                            userLists[i].title +
                        '</div>' +
                        '<div'+
                            ' id="number-items-list-' + i + '"' +
                            ' class="row"'+
                            ' style="margin: 0; color: #777;"' +
                        '>' +
                            'Пунктов: ' + userLists[i].number_items +
                        '</div>' +
                    '</td>' +
                    '<td' +
                        ' style="text-align: right; vertical-align: middle; width: 150px;"'+
                    '>' + 
                        '<div class="row" style="margin: 10 10 5 10;">' +
                            '<button' +
                            ' id="expand-list-' + i + '"'+
                            ' type="button"' +
                            ' class="btn btn-block btn-primary"' +
                            '>' +
                                'Развернуть список' +
                            '</button>' +
                        '</div>' +
                        '<div class="row" style="margin: 5 10 5 10;">' +
                            '<button' +
                            ' id="edit-list-' + i + '"'+
                            ' type="button"' +
                            ' class="btn btn-block btn-primary"' +
                            '>' +
                                'Изменить наименование' +
                            '</button>' +
                        '</div>' +
                        '<div class="row" style="margin: 5 10 10 10;">' +
                            '<button' +
                                ' id="del-list-' + i + '"' +
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
           $('#number-items-list-' + iCur).hide();
           $("#title-list-" + iCur).html(
               '<div class="row" style="margin: 0">' + 
                   '<input' +
                        ' id="title-list-edit-' + iCur + '"' +
                        ' type="text"' +
                        ' style="margi: 0; width: 100%"' +
                        ' value="' + userLists[iCur].title + '"' +
                        ' minlength="5"' +
                        ' maxlength="100"' +
                        ' required' +
                        //' onblur="changeTitleList()"' +
                   '/>' +
                   '<div style="font-size: 50%; color: #777;">' +
                        'От 5 до 100 символов' +
                   '</div>' +
               '</div>'
           );
           $("#title-list-edit-" + iCur).focus();
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
 * Вывод элементов списка
 */
function expandList() {
    $('#form-lists').hide();
    $('#form-items').show();

}

/** 
 * Изменение наименования списка
 */
$('#title-list-edit-' + iCur).blur(function() {
//    10
//     alert( "Handler for .blur() called." );
//    11
//    });
//function changeTitleList() {
    let newTitle = $('#title-list-edit-' + iCur).val();
    if (newTitle.length < 5) {
        return false;
    }
    if (userLists[iCur].title != newTitle) {
        let action = 'changeTitleList';
        $.ajax({
            url:      '/Lists',
            method:   'post',
            dataType: 'json',
            async:    true,
            data:  {
                'action':    action,
                'listid':    userLists[iCur].id,
                'listtitle': newTitle,
            },
            success: function(response){
                if (response == 0) {
                    userLists[iCur].title = newTitle;
                    $('#title-list-' + iCur).html(userLists[iCur].title);
                    $("#title-list-" + iCur).html(userLists[iCur].title);
                    $('#number-items-list-' + iCur).show();
                            }
                else {
                    errAction(action, response);
                    $("#title-list-edit-" + iCur).focus();
                }
            },
            complete: function() {
//                $('#title-list-' + iCur).html(userLists[iCur].title);
            },
        });
//        $("#title-list-" + iCur).html(userLists[iCur].title);
//        $('#number-items-list-' + iCur).show();
    }
});
*/
/**
 * Удаление списка и всех его пунктов
 */
function deleteList() {
    let action = 'deleteList';
    $.ajax({
        url:      '/Lists',
        method:   'post',
        dataType: 'json',
        async:    true,
        data: {
            'action': action,
            'listid': userLists[iCur].id,
        },
        success: function(response){
            if (response == 0) {
                userLists.splice(iCur, 1);
                $('#list-' + iCur).remove();
                if (userLists.length == 0) {
                    noLists();
                }
            }
            else {
                errAction(action, response);
            }
        },
    });
}

 /**
 * Добавление списка
 */
function appendList() {
    $('#append-list').hide();
    iCur = userLists.push({
        id:           -1,
        title:        'Новый список',
        image:        noImageList,
        number_items: 0,
    }) - 1;
    $("#one-list").append(
        '<tr id="list-' + iCur + '">' +
            '<td style="text-align: center; width: 170px;">' + 
                '<a id="image-list-' + iCur + '"' +
                    ' href="/ShowImage?whatShow=Лист&image='
                    + userLists[iCur].image + '&name=' + userLists[iCur].title + '"' +
                    ' target="_blank"' +
                '>' +
                    '<img' +
                        ' src=' + userLists[iCur].image +
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
                            ' value="' + userLists[iCur].title + '"' +
                            ' minlength="5"' +
                            ' maxlength="100"' +
                            ' required' +
                            //' onchange="checkNewList()"' +
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
    $("#title-list-new-" + iCur).focus();

    // Обработка нажатия кнопок
    $(":button").click(function() {
        let clickId = this.id;
        // Сохранить новый список
        if(clickId.substring(0, 10) === "save-list-") {
            iCur = clickId.substring(10);
            saveNewList();
        }
        // Не сохранять новый список
        else if(clickId.substring(0, 12) === "cancel-list-") {
            iCur = clickId.substring(12);
            cancelNewList();
        }
    });
}

/**
 * Сохранение нового списка
 */
function saveNewList() {
    let newTitle = $('#title-list-new-' + iCur).val();
    //let checkNewList = true;
    //if (newTitle.length < 5) {
    //    alert('<5');
    //    //errAction('checkNewListTitle', -4);
    //    checkNewList = false; //return false;
    //}
    //for(let i = 0; i < userLists.length; i++) {
    //    if (!(i == iCur) && (userLists[i].title == newTitle)) {
    //        alert('Dup');
    //        //errAction('checkNewListTitle', -3);
    //        checkNewList = false; //return false;
    //        break;
    //    }
    //}
    //if (checkNewList = true) {
        let action = 'appendList';
        $.ajax({
            url:      '/Lists',
            method:   'post',
            dataType: 'json',
            async:    true,
            data: {
                'action': action,
                'title':  newTitle, // userLists[iCur].title,
                'image':  userLists[iCur].image,
            },
            success: function(response){
                if (response > 0) {
                    userLists[iCur].id = response;
                    userLists[iCur].title = newTitle;
                    $('#append-list').show();
                }
                else {
                    errAction(action, response);
                }
            },
        });
    //    $('#append-list').show();
    //}
    //else {
    //    errAction('checkNewListTitle', (newTitle.length < 5 ? -4 : -3));
    //}
}

/**
 * Отказ от сохранения нового списка
 */
function cancelNewList() {
    userLists.pop();
    $('#list-' + iCur).remove();
    $('#append-list').show();
}


/**
 * Вывод сообщений при ошибках
 */
function errAction(action, response, needExit = false) {
    let actions = [
        [
            'changeTitleList',
            'deleteList',
            'appendList',
            'checkNewListTitle',
        ],
        [
            'Изменение наименования списка',
            'Удаление списка',
            'Добавление нового списка',
            'Проверка наименования нового списка'
        ],
    ];
    let responses = [
        [
            -1,
            -2,
            -3,
            -4,
        ],
        [
            'Список не принадлежит текущему пользователю',
            'Список отсутствует в базе данных',
            'Дублирование наименования списка',
            'Длина наименования (меньше 5 символов)'
        ],
    ];
    
    let idxAction = actions[0].indexOf(action);
    let errAct = '"' + (idxAction == -1 ? ('Неизвестное (' + action + ')') :
                                  actions[1][idxAction]) + '"';
    let idxResponse = responses[0].indexOf(response);
    let errResp = '"' + (idxResponse == -1 ? ('Неизвестная ('  + response + ')'):
                                      responses[1][idxResponse]) + '"';

    /*
    $('#err-msg').html(
      'Ошибка выполнения действия:<br/>' +
      errAct + '<br/>' +
      'Причина:<br/>' +
      errResp
    );


    $('#err-anim').show();
    $(":button").click(function() {
        let clickId = this.id;
        // Кнопка "Продолжить" в сообщении об ошибке
        if(clickId === "err-btn") {
            if (needExit) {
                $('.container').hide();
                $("#exit")[0].click();
            }
            $('#err-anim').hide();
        }
    });
    */
    $.notification.show(
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
    // alert(
        // 'Ошибка выполнения действия:\n' +
        // errAct +'"\n' +
        // 'Причина:\n' +
        // errResp
    // );
}