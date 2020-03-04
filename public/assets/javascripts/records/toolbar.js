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

      let rids = window.localStorage.getItem('selectedRecords');
      let $form = $('.export-multiple-js');

      if(rids) {
        rids = rids.split(',');
        $form.append('<input type="hidden" name="rids" value="' + rids + '">');
      }

      Kora.Modal.open($modal);
    });

    $('.export-mult-begin-files-js').click(function(e) {
      e.preventDefault();
      $exportDiv = $(this);
      $exportDivTitle = $('.export-mult-records-title-js');

      $exportDiv.addClass('disabled');
      $exportDivTitle.text("Generating zip file...");

      startURL = $exportDiv.attr('startURL');
      endURL = $exportDiv.attr('endURL');
      token = $exportDiv.attr('token');

      let rids = window.localStorage.getItem('selectedRecords');

      //Ajax call to prep zip
      $.ajax({
        url: startURL,
        type: 'POST',
        data: {
          "_token": token,
          "rids": rids
        },
        success: function (data) {
          //Change text back
          $exportDiv.removeClass('disabled');
          $exportDivTitle.text("Export Record Files");
          //Set page to download URL
          document.location.href = endURL;
        },
        error: function (error,status,err) {
          hide_loader();

          $exportDiv.removeClass('disabled');
          $exportDivTitle.text("Something went wrong :(");

          if(err=="Gateway Time-out") {
            $exportDivTitle.text("Request timed out :(");
            $('.export-mult-files-desc-js').text("Zip took too long to generate. Please use the php artisan command for exporting record files. If you do not have permission to run this command, please contact your administrator.");
          } else if(typeof error.responseJSON == 'undefined') {
            $exportDivTitle.text("Error creating zip :(");
            $('.export-mult-files-desc-js').text("Unable to create the zip. Please contact your administrator for more information. You may still export all form records in the formats of JSON or XML.");
          } else if(error.responseJSON.message == 'no_record_files') {
            $exportDivTitle.text("No record files :(");
            $('.export-mult-files-desc-js').text("There are no record files in this Form. You may still export all form records in the formats of JSON or XML.");
          } else if(error.responseJSON.message == 'zip_too_big') {
            $exportDivTitle.text("Zip too big :(");
            $('.export-mult-files-desc-js').text("Zipped file is too big. Please use the php artisan command for exporting record files.  If you do not have permission to run this command, please contact your administrator.");
          }
        }
      });
    });
  }

  function recordSelect () {
    var $check = $('.record .header .check');
    var $selectAll = $('.select-all');
    var $deselectAll = $('.deselect-all');
    var selected = [];
    var currentUrl = window.location.href;

    currentUrl = currentUrl.split("/");
    currentUrl = currentUrl[currentUrl.length - 2];

    if (currentUrl == window.localStorage.getItem('prevUrl')) {
      if (window.localStorage.getItem('selectedRecords')) { // get stored values
        var push = window.localStorage.getItem('selectedRecords').split(','); // turn them into an array

        for (var i = 0; i < push.length; i++) {
          selected.push(push[i]); // push new values to existing 'selected' array (array of ALL selected records)
        }

        for (var i = 0; i < selected.length; i++) { // check if anything in /selected/ exists on this page
          var $this = $('.display-records').find('.name:contains(' + selected[i] + ')');
          $this.parents('.card').addClass('selected');
        }

        $('.selected').find('.check').addClass('checked');

        $('.toolbar').removeClass('hidden');
        $('.record-index').addClass('with-bottom');
        count = selected.length;
        $('span.count').text('(' + count + ')');
      }
    } else {
      window.localStorage.clear();
      window.localStorage.setItem('prevUrl', currentUrl);
    }

    $('span.count-all').text('('+$check.length+')');

    $selectAll.click(function(e){
      e.preventDefault();

      $('.check:not(.checked)').trigger('click');
    });

    $deselectAll.click(function(e){
      e.preventDefault();

      $('.checked').trigger('click');

      window.localStorage.removeItem('selectedRecords');
      selected = [];
      count = selected.length;

      $('.toolbar').addClass('hidden');
      $('.record-index').removeClass('with-bottom');
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
      } else {
        $(this).addClass('checked');
        $card.addClass('selected');

        var recordName = $(this).siblings('.left').find('.name').text();
        selected.push(recordName);
      }

      if (selected.length > 0) {
        window.localStorage.setItem('selectedRecords', selected);
        count = selected.length;
        $('span.count').text('(' + count + ')');
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
          
		  resetSelectAndHideToolbar();
        },
        error: function(error) {
		  resetSelectAndHideToolbar();
			
          if (error.status == 200) {
            
          } else {
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
  
  function resetSelectAndHideToolbar() {
	  $('.checked').trigger('click'); // unselect the checkboxes
	  
	  window.localStorage.removeItem('selectedRecords');
	  selected = [];
	  count = selected.length;
	  
	  $('.toolbar').addClass('hidden'); // hide the toolbar
	  $('.record-index').removeClass('with-bottom');
	}
	
  function initializeSingleRecordDelete() {
	$(".single-record-delete-js").click(function() {
	  resetSelectAndHideToolbar();
	});
  }

  initializeDeleteRecord();
  initializeExportRecords();
  initializeSingleRecordDelete();
  recordSelect();
  batchAssign();
  deleteMultiple();
  Kora.Records.Modal();
}