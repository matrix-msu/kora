
$('.inner-wrap .duplicate-record-special-js .text-input').on('blur', function () {
  var a = $('.inner-wrap .duplicate-record-special-js').children('.text-input').val();
  $('form .duplicate-record-js .text-input').val('' + a + '');
});

$(window).scroll(function () {
  console.log($(window).scrollTop())
  if ($(window).scrollTop() >= 210) {
    $('.duplicate-record-special-js').addClass('fixed');
    $('.content-sections').css('padding-top', '148px');
  } else {
    $('.duplicate-record-special-js').removeClass('fixed');
    $('.content-sections').css('padding-top', '');
  }
});