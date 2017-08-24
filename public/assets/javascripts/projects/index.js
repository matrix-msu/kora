window.onload = function() {
  $('.sort-options-js a').click(function(e) {
    e.preventDefault();

    $('.sort-options-js a').removeClass('active');
    $(this).addClass('active');
  });

  $('.search-js i, .search-js input').click(function(e) {
    e.preventDefault();

    $(this).parent().addClass('active');
    $('.search-js input').focus();
  });

  $('.search-js input').focusout(function() {
    if (this.value.length == 0) {
      $(this).parent().removeClass('active');
      $(this).next().removeClass('active');
    }
  });

  $('.search-js input').keyup(function(e) {
    if (e.keyCode === 27) {
      $(this).val('');
    }

    if (this.value.length > 0) {
      $(this).next().addClass('active');
    } else {
      $(this).next().removeClass('active');
    }
  });

  $('.search-js .icon-cancel-js').click(function() {
    var $search = $('.search-js input');
    $search.val('').blur().parent().removeClass('active');
  });

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

  $(".project-selection-js").sortable({
    helper: 'clone',
    revert: true,
    containment: ".projects"
  });

  $('.move-action-js').click(function(e) {
    e.preventDefault();

    var $this = $(this);
    var $headerInnerWrapper = $this.parent().parent();
    var $header = $headerInnerWrapper.parent();
    var $project = $header.parent();
    // $project.prev().before(current);
    if ($this.hasClass('up-js')) {
      var $previousProject = $project.prev();
      if ($previousProject.length == 0) {
        return;
      }

      $previousProject.css('z-index', 999)
        .css('position', 'relative')
        .animate({
          top: $project.height()
        }, 300);
      $project.css('z-index', 1000)
        .css('position', 'relative')
        .animate({
          top: '-' + $previousProject.height()
        }, 300, function() {
          $previousProject.css('z-index', '')
            .css('top', '')
            .css('position', '');
          $project.css('z-index', '')
            .css('top', '')
            .css('position', '')
            .insertBefore($previousProject);
        });
    } else {
      var $nextProject = $project.next();
      if ($nextProject.length == 0) {
        return;
      }

      $nextProject.css('z-index', 999)
        .css('position', 'relative')
        .animate({
          top: '-' + $project.height()
        }, 300);
      $project.css('z-index', 1000)
        .css('position', 'relative')
        .animate({
          top: $nextProject.height()
        }, 300, function() {
          $nextProject.css('z-index', '')
            .css('top', '')
            .css('position', '');
          $project.css('z-index', '')
            .css('top', '')
            .css('position', '')
            .insertAfter($nextProject);
        });
    }


  });

  $('#myButtonDown').click(function() {
    var current = $('.markedLi');
    current.next().after(current);
  });
}
