var Kora = Kora || {};
Kora.Revisions = Kora.Revisions || {};

Kora.Revisions.Index = function() {
	function updateWindowLocation($param, $updatedParamValue) {
		var paramArray = [];

		// Get Existing URL Parameters
		var pageCountParam = getURLParameter('page-count');
		var orderParam = getURLParameter('order');
		var recordsParam = getURLParameter('records');
		var usersParam = getURLParameter('users');
		var datesParam = getURLParameter('dates');

		// Page Count parameter
		if ($param == 'page-count') {
			// If this is the updated param, replace the existing param
			if ($updatedParamValue != "") {
				paramArray.push('page-count='+$updatedParamValue);
			}
		} else if (pageCountParam && pageCountParam != "") {
			// Keep existing param if we are not updating it
			paramArray.push('page-count='+pageCountParam);
		}

		// Order parameter
		if ($param == 'order') {
			if ($updatedParamValue != "") {
				paramArray.push('order='+$updatedParamValue);
			}
		} else if (orderParam && orderParam != "") {
			paramArray.push('order='+orderParam);
		}

		// Records parameter
		if ($param == 'records') {
			if ($updatedParamValue != "") {
				paramArray.push('records='+$updatedParamValue);
			}
		} else if (recordsParam && recordsParam != "") {
			paramArray.push('records='+recordsParam);
		}

		// Users parameter
		if ($param == 'users') {
			if ($updatedParamValue != "") {
				paramArray.push('users='+$updatedParamValue);
			}
		} else if (usersParam && usersParam != "") {
			paramArray.push('users='+usersParam);
		}

		// Dates parameter
		if ($param == 'dates') {
			if ($updatedParamValue != "") {
				paramArray.push('dates='+$updatedParamValue);
			}
		} else if (datesParam && datesParam != "") {
			paramArray.push('dates='+datesParam);
		}

		if (paramArray.length > 0) {
				return window.location.pathname + "?" + paramArray.join('&');
		} else {
				return window.location.pathname;
		}
	}

	function initializeSelects() {
			//Most field option pages need these
			$('.single-select').chosen({
					disable_search_threshold: 4,
					width: '100%',
					allow_single_deselect: true,
			});

			$('.multi-select').chosen({
					width: '100%',
			});

			// Record filter
			var $recordsMultiSelect = $('#records-multi-select');
			var $recordsPlaceholderInput = $recordsMultiSelect.next().find('.search-field .chosen-search-input');

			$recordsMultiSelect.change(function(e, params) {
					var records = (getURLParameter('records') !== null ? getURLParameter('records').split(",") : []);

					if (params['selected']) {
							records.push(params['selected']);
					} else if (params['deselected']) {
							records.splice($.inArray(params['deselected'], records), 1);
					}

					window.location = updateWindowLocation('records', records);
			});

			// User filter
			var $usersMultiSelect = $('#users-multi-select');
			var $usersPlaceholderInput = $usersMultiSelect.next().find('.search-field .chosen-search-input');

			$usersMultiSelect.change(function(e, params) {
					var users = (getURLParameter('users') !== null ? getURLParameter('users').split(",") : []);

					if (params['selected']) {
							users.push(params['selected']);
					} else if (params['deselected']) {
							users.splice($.inArray(params['deselected'], users), 1);
					}

					window.location = updateWindowLocation('users', users);
			});

			// Date filter
			$('.date-picker-js').multiDatesPicker({
				changeMonth: true,
				changeYear: true,
				yearRange: "2000:+nn",
				prevTest: "Test",
				showAnim: "slideDown",
				separator: ",",
				onSelect: function(date) {
					window.location = updateWindowLocation('dates', $(this).val());
					console.log($(this).val());
				}
			});

			var dates = getURLParameter('dates');
			if (dates && dates != "") {
				$('.date-picker-js').multiDatesPicker('addDates', dates.split(','));
			}
	}

	function initializeOptionDropdowns() {
		$('.option-dropdown-js').chosen({
			disable_search_threshold: 10,
			width: 'auto'
		}).change(function() {
            var type = $(this).attr('id');
            if (type === 'page-count-dropdown') {
                window.location = updateWindowLocation('page-count', $(this).val());
            } else if (type === 'order-dropdown') {
                window.location = updateWindowLocation('order', $(this).val());
            }
        });
    }

    function initializeRecordSelect() {
        $('#record-select').chosen({
            disable_search_threshold: 4,
            width: '100%'
        }).change(function() {
            if ($(this).val() === "View All Records") {
                window.location = window.location.pathname.substr(0, window.location.pathname.lastIndexOf("/")) + '/recent';
                return;
            }
            var revision = $(this).val().split('-')[2];
            window.location = window.location.pathname.replace('recent', revision) + "?revisions=true";
        });
    }

    function initializePaginationShortcut() {
        $('.page-link.active').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var maxInput = $this.siblings().last().text()
            $this.html('<input class="page-input" type="number" min="1" max="'+ maxInput +'">');
            var $input = $('.page-input');
            $input.focus();
            $input.on('blur keydown', function(e) {
                if (e.key !== "Enter" && e.key !== "Tab") return;
                if ($input[0].checkValidity()) {
                    var url = window.location.toString();
                    if (url.includes('page=')) {
                        window.location = url.replace(/page=\d*/, "page="+$input.val());
                    } else {
                        var queryVar = url.includes('?') ? '&' : '?';
                        window.location = url + queryVar + "page=" + $input.val();
                    }
                }
            });
        })
    }

	function initializeToggle() {
		$('.revision-toggle-js').click(function(e) {
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
                }, function () {
                    $token.css('height', '');
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

        $('.expand-fields-js').on('click', function(e) {
            e.preventDefault();
            $('.card:not(.active) .revision-toggle-js').click();
        });

        $('.collapse-fields-js').on('click', function(e) {
            e.preventDefault();
            $('.card.active .revision-toggle-js').click();
        });
    }

    function initializeModals() {
        Kora.Modal.initialize();

        $('.restore-js').on('click', function(e) {
            e.preventDefault();

            var time = $(this).parents('.card').find('.time-js').text();
            var date = $(this).parents('.card').find('.date-js').text()
            var dateTime = moment(date + ' ' + time);
            var $modal = $('.restore-fields-modal-js');
            var url = $modal.find('.restore-fields-button-js').attr('href');
            var revision = $(this).data('revision');
            $modal.find('.date-time').text(dateTime.format('M.D.YYYY [at] h:mma'));

            $modal.find('.restore-fields-button-js').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        revision: revision
                    },
                    success: function(d) {
                        if ('modified_kid' in d) {
                          window.localStorage.setItem('message', 'Record Fields Restored!');
                        }
                        location.reload();
                    },
                    error: function(e) {
                        console.log(e);
                    }
                });
            });
            Kora.Modal.open($modal);
        });

        $('.reactivate-js').on('click', function(e) {
            e.preventDefault();

            var time = $(this).parents('.card').find('.time-js').text();
            var date = $(this).parents('.card').find('.date-js').text()
            var dateTime = moment(date + ' ' + time);
            var $modal = $('.reactivate-record-modal-js');
            var url = $modal.find('.reactivate-record-button-js').attr('href');
            var revision = $(this).data('revision');
            $modal.find('.date-time').text(dateTime.format('M.D.YYYY [at] h:mma'));

            $modal.find('.reactivate-record-button-js').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        revision: revision
                    },
                    success: function(d) {
                        window.localStorage.setItem('message', 'Record Re-Activated!');
                        location.reload();
                    },
                    error: function(e) {
                        console.log(e);
                    }
                });
            });
            Kora.Modal.open($modal);
        });
    }

		initializeSelects()
    initializeOptionDropdowns();
    initializeRecordSelect();
    initializePaginationShortcut();
    initializeToggle();
    initializeModals();
    Kora.Records.Modal();
}

Kora.Revisions.Index();
