var Kora = Kora || {};
Kora.User = Kora.User || {};

Kora.User.Profile = function() {
  function initializePageNavigation() {
    $('.page-section-js').first().removeClass('hidden');
    $('.toggle-by-name').first().addClass('active');

    $('.toggle-by-name').click(function(e) {
      e.preventDefault();

      $this = $(this);
      $this.addClass('active');
      $this.siblings().removeClass('active');

      $active = $this.attr("href").replace('#', '');
      $('.page-section-js').each(function() {
          if($(this).attr('id') == $active) {
            $(this).removeClass('hidden');
          } else {
            $(this).addClass('hidden');
          }
      });
    });
  }
  
  initializePageNavigation();
}