var Kora = Kora || {};
Kora.Install = Kora.Install || {};

Kora.Install.Create = function() {

    $('.single-select').chosen({
        width: '100%',
    });

    $('.install-input-js').change(function() {
        if (this.checked) {
            $('.install-select-container-js').animate({
                height: 75
            }, function() {
                $('.install-select-js').fadeIn();
            });
        } else {
            $('.install-select-js').fadeOut(function() {
                $('.install-select-container-js').animate({
                    height: 0
                });
            });
        }
    });

    $('#install_submit').on('click', function() {
        //gather the form data
        var data = $('#install_form').serialize();

        //make ajax call to save env
        $.ajax({
            type: "POST",
            url: installPartOneURL,
            data: data,
            success: function(data) {
                //Submit the form
                $('#install_form').submit();
            }
        });
    });

    var navbar = document.getElementsByClassName("navigation")[0];
    var languageSelector = document.getElementsByClassName("navigation-item")[0];

    navbar.className += " install-nav";
    languageSelector.style.display = 'none';

}

