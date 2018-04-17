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

  function initializeProjectCards() {
    // Initialize Custom Sort
    $('.project-toggle-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $header = $this.parent().parent();
      var $project = $header.parent();
      var $content = $header.next();

      $this.children().toggleClass('active');
      $project.toggleClass('active');
      if ($project.hasClass('active')) {
        $header.addClass('active');
        $project.animate({
          height: $project.height() + $content.outerHeight(true) + 'px'
        }, 230);
        $content.effect('slide', {
          direction: 'up',
          mode: 'show',
          duration: 240
        });
      } else {
        $project.animate({
          height: '58px'
        }, 230, function() {
          $header.hasClass('active') ? $header.removeClass('active') : null;
          $content.hasClass('active') ? $content.removeClass('active') : null;
        });
        $content.effect('slide', {
          direction: 'up',
          mode: 'hide',
          duration: 240
        });
      }

    });
  }
  
  initializePageNavigation();
  initializePermissionsFilters();
  initializeProjectCards();
}