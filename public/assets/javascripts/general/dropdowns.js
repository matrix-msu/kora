var dropdownWhiteToggle = $(".dropdown-white-toggle-js");
var dropdownWhite = $(".dropdown-white-js");
var selectedText = dropdownWhiteToggle.find("span");
var options = dropdownWhite.find("a")

//If the dropdown isn't clicked, close dropdown
$(document).click(function(event) {
  if (!$(event.target).closest('.dropdown-white-js').length &&
      !$(event.target).closest('.dropdown-white-toggle-js').length) {
    $('.dropdown-white-js').removeClass('active');

    $(dropdownWhiteToggle).find(".icon-chevron").removeClass("active");
  }
});

// Open dropdown menu
dropdownWhiteToggle.click(function(event) {
  event.preventDefault();
  
  dropdownWhite.toggleClass("active");
  $(dropdownWhiteToggle).find(".icon-chevron").toggleClass("active");
});

options.click(function() {
  selectedText.html(this.text);
});


