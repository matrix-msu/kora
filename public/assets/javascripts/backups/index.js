var Kora = Kora || {};
Kora.Backups = Kora.Install || {};

Kora.Backups.Index = function() {

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

    initializeSearch();
    initializeBackupToggles();
    initializeOptionDropdowns();
    initializeModals();
}