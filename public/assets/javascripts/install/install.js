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
            activatePage($active);
        });

        $('.previous-page-js').click(function(e) {
            e.preventDefault();

            var activeLink = $('.page-link.active').first();
            var prevLink = activeLink.prev();

            var pageVal = prevLink.attr("href");
            activatePage(pageVal);
        });

        $('.next-page-js').click(function(e) {
            e.preventDefault();

            var activeLink = $('.page-link.active').first();
            var nextLink = activeLink.next();

            var pageVal = nextLink.attr("href");
            activatePage(pageVal);
        });

        $('.page-link').click(function(e) {
            e.preventDefault();

            var pageVal = $(this).attr("href");
            activatePage(pageVal);
        });
    }

    function activatePage(active) {
        //Remove actives
        $('.page-link').removeClass('active');
        $('.toggle-by-name').removeClass('active');

        //Set states of Prev/Next buttons
        if(active=="#database")
            $('.previous.page').addClass('disabled');
        else
            $('.previous.page').removeClass('disabled');
        if(active=="#base")
            $('.next.page').addClass('disabled');
        else
            $('.next.page').removeClass('disabled');

        //Set actives
        if(active == "#database") {
            $('.database-link').addClass('active');

            $('.database-section').removeClass('hidden');
            $('.admin-section').addClass('hidden');
            $('.mail-section').addClass('hidden');
            $('.recaptcha-section').addClass('hidden');
            $('.base-section').addClass('hidden');
        } else if(active == "#admin") {
            $('.admin-link').addClass('active');

            $('.database-section').addClass('hidden');
            $('.admin-section').removeClass('hidden');
            $('.mail-section').addClass('hidden');
            $('.recaptcha-section').addClass('hidden');
            $('.base-section').addClass('hidden');
        } else if(active == "#mail") {
            $('.mail-link').addClass('active');

            $('.database-section').addClass('hidden');
            $('.admin-section').addClass('hidden');
            $('.mail-section').removeClass('hidden');
            $('.recaptcha-section').addClass('hidden');
            $('.base-section').addClass('hidden');
        } else if(active == "#recaptcha") {
            $('.recaptcha-link').addClass('active');

            $('.database-section').addClass('hidden');
            $('.admin-section').addClass('hidden');
            $('.mail-section').addClass('hidden');
            $('.recaptcha-section').removeClass('hidden');
            $('.base-section').addClass('hidden');
        } else if(active == "#base") {
            $('.base-link').addClass('active');

            $('.database-section').addClass('hidden');
            $('.admin-section').addClass('hidden');
            $('.mail-section').addClass('hidden');
            $('.recaptcha-section').addClass('hidden');
            $('.base-section').removeClass('hidden');
        } else {
            $('.database-link').addClass('active');

            $('.database-section').removeClass('hidden');
            $('.admin-section').addClass('hidden');
            $('.mail-section').addClass('hidden');
            $('.recaptcha-section').addClass('hidden');
            $('.base-section').addClass('hidden');
        }
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

