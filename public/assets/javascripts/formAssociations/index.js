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
        var element = '<div class="association association-js card" id="' + response.form.fid + '">';
        element += '<div class="header">';
        element += '<div class="left pl-m">';
        element += '<a class="title association-toggle-by-name-js" href="#">';
        element += '<span class="name name-js">' + response.form.name + '</span>';
        element += '</a>';
        element += '</div>';
        element += '<div class="card-toggle-wrap">'
        element += '<a href="#" class="card-toggle association-toggle-js">'
        element += '<i class="icon icon-chevron"></i>'
        element += '</a></div></div>'
        element += '<div class="content content-js">'
        element += '<div class="description">'
        element += '<p>' + response.form.description + '</p>'
        element += '</div>'
        element += '<div class="footer">'
        element += '<a class="quick-action trash-container delete-permission-association-js left" href="#" data-form="' + response.form.fid + '">'
        element += '<i class="icon icon-trash"></i>'
        element += '</a>'
        element += '</div></div></div>'
        $('.permission-association-js').append(element);
        initializePermissionsToggles();
        Kora.Modal.close();
      }
    });
  }

  function initializePermissionsToggles() {
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

    $('.association-toggle-by-name-js').click(function(e) {
      e.preventDefault();

      var $this = $(this);
      var $cardToggle = $this.parent().next();
      $cardToggle.children().click();
    });

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
      $newPermissionsModal.find('.single-select').chosen({
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

      $('.add-association-submit-js').click(submitAssociation());
      
      Kora.Modal.open($newPermissionsModal);
    });
  }

  function initializeRequestPermissionModal() {
    $('.request-permission-js').click(function(e) {
      e.preventDefault();

      $requestPermissionsModal = $('.request-permission-modal-js');
      $requestPermissionsModal.find('.single-select').chosen({
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

      $('.request-association-submit-js').click(submitAssociation());

      Kora.Modal.open($requestPermissionsModal);
    });
  }

  Kora.Modal.initialize();
  initializePermissionsToggles();
  initializeNewPermissionModal();
  initializeRequestPermissionModal();
}