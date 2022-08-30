# jQuery.notification

jQuery скрипт для отображения сообщений

#### Пример использования: 
*простое использование*
<pre>$.notification.show('some_class','some_text'); // показать сообщение
$.notification.hide('some_class','some_text'); // скрыть сообщение</pre>

*установка параметров*
<pre>var noti = $.notification.init({
    time:300, // время отображения\скрытия (мсек)
    delay:10000 // сколько будет весеть сообщение (мсек)
});
noti.show('some_class','some_text');
noti.hide('some_class','some_text');</pre>

#### CSS пример
*более расширеный пример можно взять в demo*
<pre>.notification_block{
    display: none;
    position: fixed;
    z-index: 1000;
    top: 20px;
    right: 20px;
    width: 320px;
    padding: 15px 30px 15px 60px;
    color: #fff;
}</pre>