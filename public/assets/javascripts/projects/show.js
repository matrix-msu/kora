var Kora = Kora || {};
Kora.Projects = Kora.Projects || {};

Kora.Projects.Show = function() {

  function deleteForm(formName, fid) {
    var encode = $('<div/>').html(areYouSure).text();
    var response = confirm(encode + formName + "?");
    if (response) {
      $.ajax({
        //We manually create the link in a cheap way because the JS isn't aware of the fid until runtime
        //We pass in a blank project to the action array and then manually add the id
        url: formDestroyUrl + '/' + fid,
        type: 'DELETE',
        data: {
          "_token": CSRFToken
        },
        success: function(result) {
          location.reload();
        }
      });
    }
  }

  $(".panel-heading").on("click", function() {
    if ($(this).siblings('.collapseTest').css('display') == 'none') {
      $(this).siblings('.collapseTest').slideDown();
    } else {
      $(this).siblings('.collapseTest').slideUp();
    }
  });
}
