var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Toolbar = function() {
  var count

  function initializeDeleteRecord() {
    Kora.Modal.initialize();

    $('.delete-multiple-records-js').click(function (e) {
      e.preventDefault();

      var $modal = $('.delete-multiple-records-modal-js');
      var selectedRIDs = [];

      $('.selected').each(function(){
        var $rid = $(this).find('.delete-record-js').attr('rid');
        selectedRIDs.push($rid);
      });

      $('.record-ids').append('<input name="rid" type="hidden" value="'+ selectedRIDs +'" />');

      Kora.Modal.open($modal);
    });
  }

  function initializeExportRecords () {
    Kora.Modal.initialize();

    $('.export-mult-records-js').click(function (e) {
      e.preventDefault();

      var $modal = $('.export-mult-records-modal-js');

      var selectedRIDs = [];

      $('.selected').each(function(){
        var rid = $(this).find('.delete-record-js').attr('rid');
        selectedRIDs.push(rid);
      });

      var _href = $('.export-multiple-js').attr('href');
      var eq = _href.indexOf('=');
      eq = eq + 1;
      _href = _href.substring(0, eq);
      $('.export-multiple-js').attr('href', _href + selectedRIDs);

      Kora.Modal.open($modal);
    });
  }

  function recordSelect () {
    var $check = $('.check');
    var $selectAll = $('.select-all');
    var $deselectAll = $('.deselect-all');
    var selected = [];

    $('span.count-all').text('('+$check.length+')');

    $selectAll.click(function(e){
      e.preventDefault();

      $('.check:not(.checked)').trigger('click');
    });

    $deselectAll.click(function(e){
      e.preventDefault();

      $('.checked').trigger('click');
    });

    $check.click(function (e) {
      e.preventDefault();

      var $card = $(this).parent().parent('.card');

      if ($(this).hasClass('checked')) {
        $(this).removeClass('checked');
        $card.removeClass('selected');

        var removeThisRec = $(this).siblings('.left').find('.name').text();
        var index = selected.indexOf(removeThisRec);

        selected.splice(index, 1);
        count = selected.length;
        $('span.count').text('(' + count + ')');
      } else {
        $(this).addClass('checked');
        $card.addClass('selected');

        var recordName = $(this).siblings('.left').find('.name').text();
        selected.push(recordName);
        count = selected.length;
        $('span.count').text('(' + count + ')');
      }

      if (selected.length > 0) {
        $('.toolbar').removeClass('hidden');
        $('.record-index').addClass('with-bottom');
      } else {
        $('.toolbar').addClass('hidden');
        $('.record-index').removeClass('with-bottom');
      }
    });
  }

  function batchAssign () {
    $('.batch-assign').click(function(){
      window.localStorage.setItem('count', count);
    });
  }

  function deleteMultiple () {
    $('.delete-multiple-js').click(function (e) {
      e.preventDefault();

      var $form = $('.delete-multiple-records-form-js');
      var values = $form.serializeArray();

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: values,
        success: function(data) {
          $form.submit();
          //console.log('success!');
        },
        error: function(error) {
          if (error.status == 200) {
            //location.reload();
            console.log(error);
            console.log(error.status);
          } else {
            console.log(error);
            var responseJson = error.responseJSON;
            $.each(responseJson, function() {
              console.log('error: ' + this[0]);
              if (typeof this[0] == 'object') {
                console.log(this[0]);
              }
            });
          }
        }
      });
    });
  }

  initializeDeleteRecord();
  initializeExportRecords();
  recordSelect();
  batchAssign();
  deleteMultiple();
  Kora.Records.Modal();
}