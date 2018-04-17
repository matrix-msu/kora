var Kora = Kora || {};
Kora.User = Kora.User || {};

Kora.User.Profile = function() {
  function initializePageNavigation() {
    $('.page-section-js').first().addClass('active');
    $('.select-section-js').first().addClass('active');

    $('.select-section-js').click(function(e) {
      e.preventDefault();

      $this = $(this);
      $this.siblings().removeClass('active');
      $this.addClass('active');
      
      $('.page-section-js').removeClass('active');      
      $active = $this.attr("href").replace('#', '');
      $('.page-section-js').each(function() {
          if ($(this).attr('id') == $active) {
            $(this).addClass('active');
          }
      });
    });
  }
  
  function initializePermissionsFilters() {
    $('.content-section-js').first().addClass('active');
    $('.select-content-section-js').first().addClass('active');
    
    $('.select-content-section-js').click(function(e) {
      e.preventDefault();

      $this = $(this);
      $this.siblings().removeClass('active');
      $this.addClass('active');
      
      $('.content-section-js').removeClass('active');      
      $active = $this.attr("href").replace('#', '');
      $('.content-section-js').each(function() {
        if ($(this).attr('id') == $active) {
          $(this).addClass('active');
        }
      });
    });
  }
  
  initializePageNavigation();
  initializePermissionsFilters();
}