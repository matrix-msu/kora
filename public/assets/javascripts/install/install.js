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
        if(active=="#recaptcha")
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
        } else if(active == "#admin") {
            $('.admin-link').addClass('active');

            $('.database-section').addClass('hidden');
            $('.admin-section').removeClass('hidden');
            $('.mail-section').addClass('hidden');
            $('.recaptcha-section').addClass('hidden');
        } else if(active == "#mail") {
            $('.mail-link').addClass('active');

            $('.database-section').addClass('hidden');
            $('.admin-section').addClass('hidden');
            $('.mail-section').removeClass('hidden');
            $('.recaptcha-section').addClass('hidden');
        } else if(active == "#recaptcha") {
            $('.recaptcha-link').addClass('active');

            $('.database-section').addClass('hidden');
            $('.admin-section').addClass('hidden');
            $('.mail-section').addClass('hidden');
            $('.recaptcha-section').removeClass('hidden');
        } else {
            $('.database-link').addClass('active');

            $('.database-section').removeClass('hidden');
            $('.admin-section').addClass('hidden');
            $('.mail-section').addClass('hidden');
            $('.recaptcha-section').addClass('hidden');
        }
    }

    $('.single-select').chosen({
        width: '100%',
    });

    var form = $('#install_form');
    var photoInput = $(".profile-input");
    var photoButton = $(".profile-label");
    var picCont = $(".profile-label .icon-user-cont");
    var photoFilename = $(".filename");
    var photoInstruction = $(".instruction");
    var photoDroppedFile = false;

    //Resets file input
    function resetFileInput() {
        photoInput.replaceWith(photoInput.val('').clone(true));
        photoFilename.html("Add a photo to help others identify you");
        picCont.html("<i class='icon icon-user'></i>");
        photoInstruction.removeClass("photo-selected");
        photoDroppedFile = false;
    }

    function newProfilePic(type, pic, name) {
        photoFilename.html(name + "<span class='remove-photo remove ml-xs'><i class='icon icon-cancel'></i></span>");
        photoInstruction.addClass("photo-selected");
        picCont.html("<img src='"+pic+"' alt='Profile Picture'>");
        photoDroppedFile = pic;
        $(".remove-photo").click(function(event) {
            event.preventDefault();
            resetFileInput();
        });
    }

    // Check for Drag and Drop Support on the browser
    var isAdvancedUpload = function() {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();

    //We're basically replicating what profile pic does, just for 3 file inputs on a single page
    function initializeFileUpload() {
        // When hovering over input, hitting enter or space opens the menu
        photoButton.keydown(function(event) {
            if( event.keyCode == 13 || event.keyCode == 32 )
                photoInput.focus();
        });

        // Clicking input opens menu
        photoButton.click(function(event) { photoInput.focus(); });

        // For clicking on input to select an image
        photoInput.change(function(event) {
            event.preventDefault();

            if (this.files && this.files[0]) {
                var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
                var reader = new FileReader();
                reader.onload = function (e) { newProfilePic("profile",e.target.result, name); };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Drag and Drop
    // detect and disable if we are on Safari
    if (isAdvancedUpload && window.safari == undefined && navigator.vendor != 'Apple Computer, Inc.') {
        photoButton.addClass('has-advanced-upload');

        photoButton.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) { e.preventDefault(); e.stopPropagation(); })
            .on('dragover dragenter', function() { photoButton.addClass('is-dragover'); })
            .on('dragleave dragend drop', function() { photoButton.removeClass('is-dragover'); })
            .on('drop', function(e) {
                e.stopPropagation();
                e.preventDefault();

                photoDroppedFile = e.originalEvent.dataTransfer.files[0];
                var reader = new FileReader();
                reader.onload = function (e) {
                    newProfilePic('profile', e.target.result, photoDroppedFile.name);
                    photoDroppedFile = e.target.result;
                };
                reader.readAsDataURL(photoDroppedFile);
            });
    }

    $('#install_submit').on('click', function() {
        //gather the form data
        if (!photoDroppedFile && photoInput.val() != '') { // if there was no dropped photo and if no photo was added by default
            var data = form.serialize();
        } else {
            var data = new FormData(form[0]);
            data.delete('user_profile');
            if (photoDroppedFile) {
                data.append('user_profile', photoDroppedFile);
            } else {
                data.append('user_profile', photoInput[0].files[0]);
            }
        }

        //make ajax call to save env
        $.ajax({
            type: "POST",
            url: installPartOneURL,
            data: data,
            contentType: false,
            processData: false,
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

    initializeFileUpload();
    initializeInstallToggles();
}

