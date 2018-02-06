var Kora = Kora || {};
Kora.Install = Kora.Install || {};

Kora.Install.Create = function() {

    function initializeInstallToggles() {
        $('.toggle-by-name').click(function(e) {
            e.preventDefault();

            $this = $(this);
            $this.addClass('active');
            $this.siblings().removeClass('active');

            $active = $this.attr("href");
            if($active == "#database") {
                $('.database-section').removeClass('hidden');
                $('.admin-section').addClass('hidden');
                $('.mail-section').addClass('hidden');
                $('.recaptcha-section').addClass('hidden');
                $('.base-section').addClass('hidden');
            } else if($active == "#admin") {
                $('.database-section').addClass('hidden');
                $('.admin-section').removeClass('hidden');
                $('.mail-section').addClass('hidden');
                $('.recaptcha-section').addClass('hidden');
                $('.base-section').addClass('hidden');
            } else if($active == "#mail") {
                $('.database-section').addClass('hidden');
                $('.admin-section').addClass('hidden');
                $('.mail-section').removeClass('hidden');
                $('.recaptcha-section').addClass('hidden');
                $('.base-section').addClass('hidden');
            } else if($active == "#recaptcha") {
                $('.database-section').addClass('hidden');
                $('.admin-section').addClass('hidden');
                $('.mail-section').addClass('hidden');
                $('.recaptcha-section').removeClass('hidden');
                $('.base-section').addClass('hidden');
            } else if($active == "#base") {
                $('.database-section').addClass('hidden');
                $('.admin-section').addClass('hidden');
                $('.mail-section').addClass('hidden');
                $('.recaptcha-section').addClass('hidden');
                $('.base-section').removeClass('hidden');
            } else {
                $('.database-section').removeClass('hidden');
                $('.admin-section').addClass('hidden');
                $('.mail-section').addClass('hidden');
                $('.recaptcha-section').addClass('hidden');
                $('.base-section').addClass('hidden');
            }
        });
    }

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

    initializeInstallToggles();
}

