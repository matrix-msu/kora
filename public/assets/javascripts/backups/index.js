var Kora = Kora || {};
Kora.Backups = Kora.Install || {};

Kora.Backups.Index = function() {

    var currentLabel = "";

    function initializeBackupToggles() {
        $('.toggle-by-name').click(function (e) {
            e.preventDefault();

            $this = $(this);
            $this.addClass('active');
            $this.siblings().removeClass('active');

            $active = $this.attr("href");
            if($active == "#backups") {
                $('.backups-section').removeClass('hidden');
                $('.filerestore-section').addClass('hidden');
            } else {
                $('.filerestore-section').removeClass('hidden');
                $('.backups-section').addClass('hidden');
            }
        });
    }

    function clearSearch() {
        $('.search-js .icon-cancel-js').click();
    }

    function initializeSearch() {
        var $searchInput = $('.search-js input');

        $('.search-js i, .search-js input').click(function(e) {
            e.preventDefault();

            $(this).parent().addClass('active');
            $('.search-js input').focus();
        });

        $searchInput.focusout(function() {
            if (this.value.length == 0) {
                $(this).parent().removeClass('active');
                $(this).next().removeClass('active');
            }
        });

        $searchInput.keyup(function(e) {
            if (e.keyCode === 27) {
                $(this).val('');
            }

            if (this.value.length > 0) {
                $(this).next().addClass('active');
            } else {
                $(this).next().removeClass('active');
            }
        });

        $('.search-js .icon-cancel-js').click(function() {
            $searchInput.val('').blur().parent().removeClass('active');
        });
    }

    function initializeOptionDropdowns() {
        $('.option-dropdown-js').chosen({
            disable_search_threshold: 10,
            width: 'auto'
        }).change(function() {
            window.location = window.location.pathname + "?order=" + $(this).val();
        });
    }

    function initializeModals() {
        Kora.Modal.initialize();

        $('.create-backup-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open($('.create-backup-modal-js'));
        });
    }

    function initializeToggle() {
        // Initialize card toggling
        $('.backup-toggle-js').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var $header = $this.parent().parent();
            var $token = $header.parent();
            var $content = $header.next();

            $this.children('.icon').toggleClass('active');
            $token.toggleClass('active');
            if ($token.hasClass('active')) {
                $header.addClass('active');
                $token.animate({
                    height: $token.height() + $content.outerHeight(true) + 'px'
                }, 230);
                $content.effect('slide', {
                    direction: 'up',
                    mode: 'show',
                    duration: 240
                });
            } else {
                $token.animate({
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

    function initializeDeleteBackupModal() {
        Kora.Modal.initialize();

        $('.delete-backup-open-js').click(function(e) {
            e.preventDefault();

            currentLabel = $(this).attr('backup-label');
            Kora.Modal.open($('.delete-backup-modal-js'));
        });

        $('.delete-backup-js').click(function(e) {
            $.ajax({
                url: deleteBackupUrl,
                type: 'DELETE',
                data: {
                    "_token": CSRFToken,
                    "label" : currentLabel
                },
                success: function (result) {
                    location.reload();
                }
            });
        });
    }

    function initializeRestoreBackup() {
        $('.restore-backup-js').click(function(e) {
            e.preventDefault();

            var restoreForm = $(this).parent('.restore-form-js');
            restoreForm.submit();
        });
    }

    initializeSearch();
    initializeBackupToggles();
    initializeOptionDropdowns();
    initializeModals();
    initializeToggle();
    initializeDeleteBackupModal();
    initializeRestoreBackup();
}