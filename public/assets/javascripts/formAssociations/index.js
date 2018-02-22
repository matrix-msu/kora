var Kora = Kora || {};
Kora.FormAssociations = Kora.FormAssociations || {};

Kora.FormAssociations.Index = function() {
  var self = Kora.FormAssociations.Index;

  /**
   * Request association permissions for another group
   * 
   * @param rfid {int} The form id being requested.
   */
  self.requestPermissions = function(rfid) {
    $.ajax({
      url: requestAssociationPath,
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "rfid": rfid
      },
      success: function() {
        Kora.Modal.close();
      }
    });
  }

  self.createPermissions = function(assocfid) {
    $.ajax({
      url: createAssociationPath,
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "assocfid": assocfid
      },
      success: function(response) {
        var element = $('<div></div>').addClass('association association-js card').attr('id', response.form.fid);
        var header = $('<div></div>').addClass('header');
        var title = $('<div></div>').addClass('left pl-m');
        var titleLink = $('<a></a>').addClass('title association-toggle-by-name-js').attr('href', '#');
        var titleSpan = $('<span></span>').addClass('name name-js').text(response.form.name);
        var cardToggle = $('<div></div>').addClass('card-toggle-wrap');
        var cardToggleLink = $('<a></a>').addClass('card-toggle association-toggle-js').attr('href', '#')
        cardToggleLink.append($('<i></i>').addClass('icon icon-chevron'));
        var content = $('<div></div>').addClass('content content-js');
        content.append($('<div></div>').addClass('description').append($('<p></p>').text(response.form.description)));
        var footer = $('<div></div>').addClass('footer');
        footer.append($('<a></a>').addClass('quick-action trash-container delete-permission-association-js left').attr('href', '#').attr('data-form', response.form.fid).attr('data-reverse', 'false').append($('<i></i>').addClass('icon icon-trash')));
        element.append(header.append(title.append(titleLink.append(titleSpan))).append(cardToggle.append(cardToggleLink)));
        content.append(footer);
        element.append(content);
        $('.permission-association-js.create').append(element);
        initializePermissionsToggles();
        initializeDeletePermissionModal();
        $('#new-form option[value='+response.form.fid+']').remove();
        $('.create-description-js').removeClass('hidden');
        Kora.Modal.close();
      }
    });
  }

  self.deleteAssociated = function(assocfid) {
    $.ajax({
      url: destroyAssociationPath,
      type: 'DELETE',
      data: {
        "_token": CSRFToken,
        "assocfid": assocfid
      },
      success: function(response) {
        if ($('.permission-association-js.create .card').size() === 1) {
          $('.create-description-js').addClass('hidden');
        }
        $('#new-form').append($('<option></option>').attr('value', response.assocfid).text(response.name));
        Kora.Modal.close();
        $('.create-section #create-'+response.assocfid).fadeOut(1000, function() {
          $(this).remove();
        });
      }
    });
  }

  self.deleteAssociatedReverse = function(assocfid) {
    $.ajax({
      url: destroyReverseAssociationPath,
      type: 'DELETE',
      data: {
        "_token": CSRFToken,
        "assocfid": assocfid
      },
      success: function(response) {
        if ($('.permission-association-js.request .card').size() === 1) {
          $('.request-description-js').addClass('hidden');
        }
        $('#request-form').append($('<option></option>').attr('value', response.assocfid).text(response.name));
        Kora.Modal.close();
        $('.request-section #request-'+response.assocfid).fadeOut(1000, function() {
          $(this).remove();
        });
      }
    });
  }

  function initializePermissionsToggles() {
    $('.toggle-by-name').unbind('click');
    $('.toggle-by-name').click(function(e) {
      e.preventDefault();
      
      $this = $(this);
      $this.addClass('active');
      $this.siblings().removeClass('active');
      
      $active = $this.attr("href");
      if ($active == "#request") {
        $('.request-section').removeClass('hidden');
        $('.create-section').addClass('hidden');
      } else {
        $('.create-section').removeClass('hidden');
        $('.request-section').addClass('hidden');
      }
    });

    $('.association-toggle-by-name-js').unbind('click');
    $('.association-toggle-by-name-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $cardToggle = $this.parent().next();
      $cardToggle.children().click();
    });

    $('.association-toggle-js').unbind('click');
    $('.association-toggle-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $header = $this.parent().parent();
      var $form = $header.parent();
      var $content = $header.next();

      $this.children().toggleClass('active');
      $form.toggleClass('active');
      if ($form.hasClass('active')) {
        $header.addClass('active');
        $form.animate({
          height: $form.height() + $content.outerHeight(true) + 'px'
        }, 230);
        $content.effect('slide', {
          direction: 'up',
          mode: 'show',
          duration: 240
        });
      } else {
        $form.animate({
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

  function initializeNewPermissionModal() {
    $('.new-permission-js').click(function(e) {
      e.preventDefault();

      $newPermissionsModal = $('.new-permission-modal-js');
      $newPermissionsModal.find('.single-select').val('').trigger('chosen:updated').chosen({
        width: '100%'
      });

      var submitAssociation = function() {
        return function(e) {
          e.preventDefault();
          var assocFormID = $(this).siblings('.form-group').children('select').val();
          if (assocFormID !== "") {
            self.createPermissions(assocFormID);
          } else {
            // inform user that the field is required
          }
        }
      }

      $('.add-association-submit-js').unbind('click');
      $('.add-association-submit-js').click(submitAssociation());
      
      Kora.Modal.open($newPermissionsModal);
    });
  }

  function initializeRequestPermissionModal() {
    $('.request-permission-js').click(function(e) {
      e.preventDefault();

      $requestPermissionsModal = $('.request-permission-modal-js');
      $requestPermissionsModal.find('.single-select').val('').trigger('chosen:updated').chosen({
        width: '100%'
      });

      var submitAssociation = function() {
        return function(e) {
          e.preventDefault();
          var rfid = $(this).siblings('.form-group').children('select').val();
          if (rfid !== "") {
            self.requestPermissions(rfid);
          } else {
            // inform user that the field is required
          }
        }
      }

      $('.request-association-submit-js').unbind('click');
      $('.request-association-submit-js').click(submitAssociation());

      Kora.Modal.open($requestPermissionsModal);
    });
  }

  function initializeDeletePermissionModal() {
    $('.delete-permission-association-js').unbind('click');
    $('.delete-permission-association-js').click(function (e) {
      e.preventDefault();

      Kora.Modal.open($('.delete-permission-association-modal-js'));

      var assocfid = $(this).data('form');
      var reverse = $(this).data('reverse');
      var deleteAssociation = function(assocfid, reverse) {
        return function(e) {
          e.preventDefault();
          if (reverse) {
            self.deleteAssociatedReverse(assocfid);
          } else {
            self.deleteAssociated(assocfid);
          }
        }
      }

      $('.permissions-delete-btn-js').unbind('click');
      $('.permissions-delete-btn-js').click(deleteAssociation(assocfid, reverse));
    });
  }

  Kora.Modal.initialize();
  initializePermissionsToggles();
  initializeNewPermissionModal();
  initializeRequestPermissionModal();
  initializeDeletePermissionModal();
}