
$('.inner-wrap .duplicate-record-special-js .text-input').on('blur', function () {
  var a = $('.inner-wrap .duplicate-record-special-js').children('.text-input').val();
  $('form .duplicate-record-js .text-input').val('' + a + '');
});