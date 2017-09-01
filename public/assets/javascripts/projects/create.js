var Kora = Kora || {};
Kora.Projects = Kora.Projects || {};

Kora.Projects.Create = function() {

  $('.multi-select').chosen({
    width: '100%',
  });

  $('form').on('submit', function() {
    debugger;
    e.preventDefault();
    console.log($(this).serialize())
    return false;
  })
}
