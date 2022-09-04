var elemId   = '';
var whatShow = '';
var imgPath  = '';
var elemName = '';
var file     = '';
var url      = '';
$(document).ready(function() {
    elemId   = $('#elemId').html();
    whatShow = $('#whatShow').html();
    imgPath  = $('#imgPath').html();
    elemName = $('#elemName').html();
    url      = '/Images/uploadImage'

    showImage();
});

/** 
 * Вывод изображения
 */
function showImage() {
    // Нажата кнопка "Изменить изображение"
    $('#change-img').click(function() {
        $('#select-file-form').removeClass('hide');
        $("#select-file-input").focus();
    });

    // Файл выбран (input) - выводим для просмотра
    $('#select-file-input').on('change', function(){
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
    $('#select-file-submit').click(function() {

        $('.form-with-image').on('submit', function(e) {
            e.preventDefault();
        
            let $form = $(e.currentTarget);
            $.ajax({
                url:         $form.attr('action'),
                type:        $form.attr('method'),
                dataType:    'json',
                cache:       false,
                contentType: false,
                processData: false,
                data :       new FormData($form[0]),
                success : function(result) {
                    $('#select-file-form').addClass('hide');
                }
            });
        });        
    });

}