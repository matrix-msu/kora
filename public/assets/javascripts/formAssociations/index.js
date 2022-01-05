var Kora = Kora || {};
Kora.FormAssociations = Kora.FormAssociations || {};

Kora.FormAssociations.Index = function() {
  var self = Kora.FormAssociations.Index;

  self.createPermissions = function(assocfid) {
    $.ajax({
      url: createAssociationPath,
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "assocfid": assocfid
      },
      success: function(response) {
        window.localStorage.setItem('message', "Form Association Successfully Created!");
        window.location.reload();
      }
    });
  }

  self.deleteAssociated = function(assocfid) {
    $.ajax({
      url: destroyAssociationPath,
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "_method": 'delete',
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
      type: 'POST',
      data: {
        "_token": CSRFToken,
        "_method": 'delete',
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

      $this.children('.icon-chevron').toggleClass('active');
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
            $('.new-assoc-error-js').text('');
            self.createPermissions(assocFormID);

            setTimeout(function(){
      				$('.trash-container.delete-permission-association-js').attr('tooltip', 'Remove Form Association');
      				$('.trash-container.delete-permission-association-js').addClass('tooltip');
      			}, 1000);

      			window.localStorage.setItem('message', 'Form Association Successfully Created!');
      			showNotification();
          } else {
            $('.new-assoc-error-js').text('Please select a form');
          }
        }
      }

      $('.add-association-submit-js').unbind('click');
      $('.add-association-submit-js').click(submitAssociation());

      Kora.Modal.open($newPermissionsModal);
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

  function showNotification() {
    var $noteBody = $('.notification');
    var $note = $('.note').children('p');
    var $noteDesc = $('.note').children('span');

    var message = window.localStorage.getItem('message');

    if (message) {
      $note.text(message);
      window.localStorage.clear();
    }

    setTimeout(function(){
      if ($note.text() != '') {
        if ($noteDesc.text() != '') {
          $noteDesc.addClass('note-description');
          $note.addClass('with-description');
        }

        $noteBody.removeClass('dismiss');
        $('.welcome-body').addClass('with-notification');
      }
    }, 200);

    $('.toggle-notification-js').click(function(e) {
      e.preventDefault();

      //$noteBody.addClass('dismiss');
      $('.welcome-body').removeClass('with-notification');
    });
  }

  Kora.Modal.initialize();
  initializePermissionsToggles();
  initializeNewPermissionModal();
  initializeDeletePermissionModal();
}
