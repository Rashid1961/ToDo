var elemId   = '';
var whatShow = '';
var imgPath  = '';
var elemName = '';
var file     ='';
$(document).ready(function() {
    elemId   = $('#elemId').html();
    whatShow = $('#whatShow').html();
    imgPath  = $('#imgPath').html();
    elemName = $('#elemName').html();
    showImage();
});

/** 
 * Вывод изображения
 */
function showImage() {
    // Нажата кнопка "Изменить изображение"
    $('#change-img').click(function() {
        $('#select-file').css('display', 'block');
    });

    // Файл выбран (input) - выводим для просмотра
    $('#local-file').on('change', function(){
        var files = this.files;
        if (files && files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#upload-img').attr('src', e.target.result);
                imgPreview.src = e.target.result;
            }
            reader.readAsDataURL(files[0]);
        }
        $('#select-file').css('display', 'none');
    });

}
