var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};

Kora.Fields.Options = function(fieldType) {

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
    }

    // Arrows to move cards up and down
    function initializeMoveAction($cards) {
        $cards.each(function() {
            var $card = $(this);
            var $moveActions = $card.find('.move-action-js');

            $moveActions.unbind();
            $moveActions.click(function(e) {
                e.preventDefault();

                var $moveAction = $(this);
                if ($moveAction.hasClass('up-js')) {
                    var $previousForm = $card.prev();
                    if ($previousForm.length == 0) {
                        return;
                    }

                    $previousForm.css('z-index', 999)
                        .css('position', 'relative')
                        .animate({
                            top: $card.height()
                        }, 300);
                    $card.css('z-index', 1000)
                        .css('position', 'relative')
                        .animate({
                            top: '-' + $previousForm.height()
                        }, 300, function() {
                            $previousForm.css('z-index', '')
                                .css('top', '')
                                .css('position', '');
                            $card.css('z-index', '')
                                .css('top', '')
                                .css('position', '')
                                .insertBefore($previousForm);
                        });
                } else {
                    var $nextForm = $card.next();
                    if ($nextForm.length == 0) {
                        return;
                    }

                    $nextForm.css('z-index', 999)
                        .css('position', 'relative')
                        .animate({
                            top: '-' + $card.height()
                        }, 300);
                    $card.css('z-index', 1000)
                        .css('position', 'relative')
                        .animate({
                            top: $nextForm.height()
                        }, 300, function() {
                            $nextForm.css('z-index', '')
                                .css('top', '')
                                .css('position', '');
                            $card.css('z-index', '')
                                .css('top', '')
                                .css('position', '')
                                .insertAfter($nextForm);
                        });
                }
            });
        });
    }

    function initializeSelectAddition() {
        $('.chosen-search-input').on('keyup', function(e) {
            var container = $(this).parents('.chosen-container').first();

            if (e.which === 13 && (container.find('li.no-results').length > 0 || container.find('li.active-result').length == 0)) {
                var option = $("<option>").val(this.value.trim()).text(this.value.trim());

                var select = container.siblings('.modify-select').first();

                select.append(option);
                select.find(option).prop('selected', true);
                select.trigger("chosen:updated");
            }
        });
    }

    //Fields that have specific functionality will have their own initialization process

    function initializeDateOptions() {
        var $dateInputsContainers = $('.date-inputs-container-js');
        var $dateListInputs = $dateInputsContainers.find('.chosen-container');
        var scrollBarWidth = 17;

        $('.start-year-js').change(printYears);

        $('.end-year-js').change(printYears);

        setTextInputWidth();

        $(window).resize(setTextInputWidth);

        function setTextInputWidth() {
            if ($(window).outerWidth() < 1000 - scrollBarWidth) {
                // Window is small, full width Inputs
                $dateListInputs.css('width', '100%');
                $dateListInputs.css('margin-bottom', '10px');
            } else {
                // Window is large, 1/3 width Inputs
                $dateListInputs.css('width', '33%');
                $dateListInputs.css('margin-bottom', '');
            }
        }

        function printYears(){
            start = $('.start-year-js').val(); end = $('.end-year-js').val();

            if(start=='' || start < 0) {start = 0;}
            if(end == '' || end > 9999) {end = 9999;}

            val = '<option></option>';
            for(var i=start;i<+end+1;i++) {
                val += "<option value=" + i + ">" + i + "</option>";
            }

            $('.default-year-js').html(val); $('.default-year-js').trigger("chosen:updated");
        }
    }

    function initializeGeneratedListOptions() {
        var listOpt = $('.genlist-options-js');
        var listDef = $('.genlist-default-js');

        var inputOpt = listOpt.siblings('.chosen-container');
        var childCheckOpt = inputOpt.children('.chosen-drop').children('.chosen-results');
        var inputDef = listDef.siblings('.chosen-container');
        var childCheck = inputDef.children('.chosen-drop').children('.chosen-results');

        listOpt.find('option').prop('selected', true);
        listOpt.trigger("chosen:updated");

        listOpt.chosen().change(function() {
            //When option de-selected, we delete it from list
            listOpt.find('option').not(':selected').remove();
            listOpt.trigger("chosen:updated");
        });

        listOpt.bind("DOMSubtreeModified",function(){
            var options = listOpt.html();
            listDef.html(options);
            listDef.trigger("chosen:updated");
        });

        inputOpt.on('click', function () {
          if (childCheckOpt.children().length === 0) {
            childCheckOpt.append('<li class="no-results">No options to select!</li>');
          } else if (childCheckOpt.children('.active-result').length === 0 && childCheckOpt.children('.no-results').length === 0) {
            childCheckOpt.append('<li class="no-results">No more options to select!</li>');
          }
        });

        inputDef.on('click', function () {
          if (childCheck.children().length === 0) {
            childCheck.append('<li class="no-results">No options to select!</li>');
          } else if (childCheck.children('.active-result').length === 0 && childCheck.children('.no-results').length === 0) {
            childCheck.append('<li class="no-results">No more options to select!</li>');
          }
        });
    }

    function initializeList() {
        function setCardTitleWidth() {
            var $cards = $('.list-option-card-js');

            $cards.each(function() {
                var $card = $(this);
                var $value = $card.find('.title');

                var maxValueWidth = $card.outerWidth() * .75;
                $value.css('max-width', maxValueWidth);
            })
        }

        // Function to add list options and the respective cards
        function initializeListAddOption() {
            var $addButton = $('.list-option-add-js');
            var $newListOptionInput = $('.new-list-option-js');
            var $cardContainer = $('.list-option-card-container-js');

            $newListOptionInput.keypress(function(e) {
                var keycode =  (e.keyCode ? e.keyCode : e.which);
                if (keycode == '13') {
                    e.preventDefault();

                    // Enter key pressed, trigger 'add' button click
                    $addButton.click();
                }
            });

            // Add new list option card after 'add' button pressed
            $addButton.click(function(e) {
                e.preventDefault();

                var newListOption = $newListOptionInput.val();

                if(newListOption!='') {
                    // Prevent duplicate entries

                    // Create and display new card
                    var newCardHtml = '<div class="card list-option-card list-option-card-js" data-list-value="' + newListOption + '">' +
                        '<input type="hidden" class="list-option-js" name="options[]" value="' + newListOption + '">' +
                        '<div class="header">' +
                        '<div class="left">' +
                        '<div class="move-actions">' +
                        '<a class="action move-action-js up-js" href="">' +
                        '<i class="icon icon-arrow-up"></i>' +
                        '</a>' +
                        '<a class="action move-action-js down-js" href="">' +
                        '<i class="icon icon-arrow-down"></i>' +
                        '</a>' +
                        '</div>' +
                        '<span class="title">' + newListOption + '</span>' +
                        '</div>' +
                        '<div class="card-toggle-wrap">' +
                        '<a class="list-option-delete list-option-delete-js tooltip" tooltip="Delete List Option" href=""><i class="icon icon-trash"></i></a>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                    $cardContainer.append(newCardHtml);

                    // Initialize functionality for all the cards again
                    $('.move-action-js').unbind();
                    setCardTitleWidth();
                    initializeListSort();
                    initializeListOptionDelete();
                    updateListDefaultOptions();
                    Kora.Fields.TypedFieldInputs.Initialize();

                    // Clear input after everything is finished
                    $newListOptionInput.val("");
                }
            });
        }

        function initializeListSort() {
            $('.move-action-js').click(function(e) {
                e.preventDefault();

                var $this = $(this);
                var $headerInnerWrapper = $this.parent().parent();
                var $header = $headerInnerWrapper.parent();
                var $card = $header.parent();
                // $form.prev().before(current);
                if ($this.hasClass('up-js')) {
                    var $previousForm = $card.prev();
                    if ($previousForm.length == 0) {
                        return;
                    }

                    $previousForm.css('z-index', 999)
                        .css('position', 'relative')
                        .animate({
                            top: $card.height()
                        }, 300);
                    $card.css('z-index', 1000)
                        .css('position', 'relative')
                        .animate({
                            top: '-' + $previousForm.height()
                        }, 300, function() {
                            $previousForm.css('z-index', '')
                                .css('top', '')
                                .css('position', '');
                            $card.css('z-index', '')
                                .css('top', '')
                                .css('position', '')
                                .insertBefore($previousForm);
                            updateListDefaultOptions();
                        });
                } else {
                    var $nextForm = $card.next();
                    if ($nextForm.length == 0) {
                        return;
                    }

                    $nextForm.css('z-index', 999)
                        .css('position', 'relative')
                        .animate({
                            top: '-' + $card.height()
                        }, 300);
                    $card.css('z-index', 1000)
                        .css('position', 'relative')
                        .animate({
                            top: $nextForm.height()
                        }, 300, function() {
                            $nextForm.css('z-index', '')
                                .css('top', '')
                                .css('position', '');
                            $card.css('z-index', '')
                                .css('top', '')
                                .css('position', '')
                                .insertAfter($nextForm);
                            updateListDefaultOptions();
                        });
                }
            });
        }

        function initializeListOptionDelete() {
            var $listOptionCards = $('.list-option-card-js');

            $listOptionCards.each(function() {
                var $card = $(this);
                var $deleteButton = $card.find('.list-option-delete-js');

                $deleteButton.click(function(e) {
                    e.preventDefault();

                    $card.remove();

                    updateListDefaultOptions();
                });
            });
        }

        function updateListDefaultOptions() {
            var $cards = $('.list-option-card-js');
            var $listDef = $('.list-default-js');

            var optionsHtml = "";
            if ($cards.length > 0) {
                optionsHtml += '<option></option>';
                for (var i = 0; i < $cards.length; i++) {
                    var $card = $($cards[i]);
                    var option = $card.find('.list-option-js').val();
                    optionsHtml += '<option value="'+option+'">'+option+'</option>';
                }
            } else {
                optionsHtml += '<option value="" disabled>No options to select!</option>';
            }

            $listDef.html(optionsHtml);
            $listDef.trigger("chosen:updated");
        }

        setCardTitleWidth();
        initializeListAddOption();
        initializeListSort();
        initializeListOptionDelete();
        Kora.Fields.TypedFieldInputs.Initialize();
    }

    function initializeMultiSelectListOptions() {
        var listOpt = $('.mslist-options-js');
        var listDef = $('.mslist-default-js');

        var inputDef = listDef.siblings('.chosen-container').children('.chosen-choices');
        var childCheckDef
        var inputList = listOpt.siblings('.chosen-container').children('.chosen-choices');
        var childCheckList = inputList.siblings('.chosen-drop').children('.chosen-results');

        listOpt.find('option').prop('selected', true);
        listOpt.trigger("chosen:updated");

        listOpt.chosen().change(function() {
            //When option de-selected, we delete it from list
            listOpt.find('option').not(':selected').remove();
            listOpt.trigger("chosen:updated");
        });

        listOpt.bind("DOMSubtreeModified",function(){
            var options = listOpt.html();
            listDef.html(options);
            listDef.trigger("chosen:updated");
        });

        inputList.on('click', function() {
          if (childCheckList.children().length === 0) {
            childCheckList.append('<li class="no-results">No options to select!</li>');
          } else if (childCheckList.children('.active-result').length === 0 && childCheckList.children('.no-results').length === 0) {
            childCheckList.append('<li class="no-results">No more options to select!</li>');
          }
        });

        inputDef.on('click', function() {
          childCheckDef = $(this).siblings('.chosen-drop').children('.chosen-results');
          if (childCheckDef.children().length === 0) {
            childCheckDef.append('<li class="no-results">No options to select!</li>');
          } else if (childCheckDef.children('.active-result').length === 0 && childCheckDef.children('.no-results').length === 0) {
            childCheckDef.append('<li class="no-results">No more options to select!</li>');
          }
        });
    }

    function initializeScheduleOptions() {
        Kora.Modal.initialize();
        Kora.Inputs.Number();
        Kora.Fields.TypedFieldInputs.Initialize();

        // Action arrows on the cards
        initializeMoveAction($('.schedule-card-js'));

        // Drag cards to sort
        $('.schedule-card-container-js').sortable();

        // Delete card
        initializeDelete();

        $('.add-new-default-event-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open($('.schedule-add-event-modal-js'));
        });

        $('.add-new-event-js').on('click', function(e) {
            e.preventDefault();

            $('.error-message').text('');
            $('.text-input, .text-area, .cke, .chosen-container').removeClass('error');

            var nameInput = $('.event-name-js');
            var sTimeInput = $('.event-start-time-js');
            var eTimeInput = $('.event-end-time-js');

            var name = nameInput.val().trim();
            var sTime = sTimeInput.val().trim();
            var eTime = eTimeInput.val().trim();

            if(name==''|sTime==''|eTime=='') {
                if(name=='') {
                    schError = $('.event-name-js');
                    schError.addClass('error');
                    schError.siblings('.error-message').text('Event name is required');
                }

                if(sTime=='') {
                    schError = $('.event-start-time-js');
                    schError.addClass('error');
                    schError.siblings('.error-message').text('Start time is required');
                }

                if(eTime=='') {
                    schError = $('.event-end-time-js');
                    schError.addClass('error');
                    schError.siblings('.error-message').text('End time is required');
                }
            } else {
                if($('.event-allday-js').is(":checked")) {
                    sTime = sTime.split(" ")[0];
                    eTime = eTime.split(" ")[0];
                }

                if(sTime>eTime) {
                    schError = $('.event-start-time-js');
                    schError.addClass('error');
                    schError.siblings('.error-message').text('Start time can not occur before the end time');
                } else {
                    val = name + ': ' + sTime + ' - ' + eTime;


                    if(val != '') {
                        // Value is valid
                        // Create and display new event card
                        var newCardHtml = '<div class="card schedule-card schedule-card-js">' +
                            '<input type="hidden" class="list-option-js" name="default[]" value="' + val + '">' +
                            '<div class="header">' +
                            '<div class="left">' +
                            '<div class="move-actions">' +
                            '<a class="action move-action-js up-js" href="">' +
                            '<i class="icon icon-arrow-up"></i>' +
                            '</a>' +
                            '<a class="action move-action-js down-js" href="">' +
                            '<i class="icon icon-arrow-down"></i>' +
                            '</a>' +
                            '</div>' +
                            '<span class="title">' + name + '</span>' +
                            '</div>' +
                            '<div class="card-toggle-wrap">' +
                            '<a class="schedule-delete schedule-delete-js tooltip" tooltip="Delete Event" href=""><i class="icon icon-trash"></i></a>' +
                            '</div>' +
                            '</div>' +
                            '<div class="content"><p class="event-time">'+ sTime + ' - ' + eTime + '</p></div>' +
                            '</div>';

                        $('.schedule-card-container-js').append(newCardHtml);

                        // Initialize New Card
                        initializeMoveAction($('.schedule-card-js'));
                        initializeDelete();
                        Kora.Fields.TypedFieldInputs.Initialize();

                        nameInput.val('');
                        Kora.Modal.close($('.schedule-add-event-modal-js'));
                    }
                }
            }
        });

        function initializeDelete() {
            $('.schedule-card-js').each(function() {
                var $card = $(this);
                var $deleteButton = $card.find('.schedule-delete-js');

                $deleteButton.unbind();
                $deleteButton.click(function(e) {
                    e.preventDefault();

                    $card.remove();
                })
            });
        }
    }

    function initializeGeolocatorOptions() {
        Kora.Modal.initialize();

        var $geoCardContainer = $('.geolocator-card-container-js');
        var $geoCards = $geoCardContainer.find('.geolocator-card-js');

        // Action arrows on the cards
        initializeMoveAction($geoCards);

        // Drag cards to sort
        $geoCardContainer.sortable();

        // Delete card
        initializeDelete();

        // Open Geolocator modal when adding new location
        $('.add-new-default-location-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open($('.geolocator-add-location-modal-js'));
        });

        $('.location-type-js').on('change', function(e) {
            newType = $(this).val();
            if(newType=='LatLon') {
                $('.lat-lon-switch-js').removeClass('hidden');
                $('.utm-switch-js').addClass('hidden');
                $('.address-switch-js').addClass('hidden');
            } else if(newType=='UTM') {
                $('.lat-lon-switch-js').addClass('hidden');
                $('.utm-switch-js').removeClass('hidden');
                $('.address-switch-js').addClass('hidden');
            } else if(newType=='Address') {
                $('.lat-lon-switch-js').addClass('hidden');
                $('.utm-switch-js').addClass('hidden');
                $('.address-switch-js').removeClass('hidden');
            }
        });

        $('.add-new-location-js').click(function(e) {
            e.preventDefault();

            $('.error-message').text('');
            $('.text-input, .text-area, .cke, .chosen-container').removeClass('error');

            //check to see if description provided
            var desc = $('.location-desc-js').val();
            if(desc=='') {
                $geoError = $('.location-desc-js');
                $geoError.addClass('error');
                $geoError.siblings('.error-message').text('Location description required');
            } else {
                var type = $('.location-type-js').val();

                //determine if info is good for that type
                var valid = true;
                if(type == 'LatLon') {
                    var lat = $('.location-lat-js').val();
                    var lon = $('.location-lon-js').val();

                    if(lat == '') {
                        $geoError = $('.location-lat-js');
                        $geoError.addClass('error');
                        $geoError.siblings('.error-message').text('Latitude value required');
                        valid = false;
                    }

                    if(lon == '') {
                        $geoError = $('.location-lon-js');
                        $geoError.addClass('error');
                        $geoError.siblings('.error-message').text('Longitude value required');
                        valid = false;
                    }
                } else if(type == 'UTM') {
                    var zone = $('.location-zone-js').val();
                    var east = $('.location-east-js').val();
                    var north = $('.location-north-js').val();

                    if(zone == '') {
                        $geoError = $('.location-zone-js');
                        $geoError.addClass('error');
                        $geoError.siblings('.error-message').text('UTM Zone is required');
                        valid = false;
                    }

                    if(east == '') {
                        $geoError = $('.location-east-js');
                        $geoError.addClass('error');
                        $geoError.siblings('.error-message').text('UTM Easting required');
                        valid = false;
                    }

                    if(north == '') {
                        $geoError = $('.location-north-js');
                        $geoError.addClass('error');
                        $geoError.siblings('.error-message').text('UTM Northing required');
                        valid = false;
                    }
                } else if(type == 'Address') {
                    var addr = $('.location-addr-js').val();

                    if(addr == '') {
                        $geoError = $('.location-addr-js');
                        $geoError.addClass('error');
                        $geoError.siblings('.error-message').text('Location address required');
                        valid = false;
                    }
                }

                //if still valid
                if(valid) {
                    //find info for other loc types
                    if(type == 'LatLon')
                        coordinateConvert({"_token": csrfToken,type:'latlon',lat:lat,lon:lon});
                    else if(type == 'UTM')
                        coordinateConvert({"_token": csrfToken,type:'utm',zone:zone,east:east,north:north});
                    else if(type == 'Address')
                        coordinateConvert({"_token": csrfToken,type:'geo',addr:addr});

                    $('.location-lat-js').val(''); $('.location-lon-js').val('');
                    $('.location-zone-js').val(''); $('.location-east-js').val(''); $('.location-north-js').val('');
                    $('.location-addr-js').val('');
                }
            }
        });

        function coordinateConvert(data) {
            $.ajax({
                url: geoConvertUrl,
                type: 'POST',
                data: data,
                success:function(result) {
                    // Get Values
                    var desc = $('.location-desc-js').val();
                    var fullresult = '[Desc]'+desc+'[Desc]'+result;
                    var latlon = result.split('[LatLon]')[1].split(',').join(', ');
                    var utm = result.split('[UTM]')[1];
                    var addr = result.split('[Address]')[1];

                    // Create and display new geolocation card
                    var newCardHtml = '<div class="card geolocator-card geolocator-card-js">' +
                        '<input type="hidden" class="list-option-js" name="default[]" value="' + fullresult + '">' +
                        '<div class="header">' +
                        '<div class="left">' +
                        '<div class="move-actions">' +
                        '<a class="action move-action-js up-js" href="">' +
                        '<i class="icon icon-arrow-up"></i>' +
                        '</a>' +
                        '<a class="action move-action-js down-js" href="">' +
                        '<i class="icon icon-arrow-down"></i>' +
                        '</a>' +
                        '</div>' +
                        '<span class="title">' + desc + '</span>' +
                        '</div>' +
                        '<div class="card-toggle-wrap">' +
                        '<a class="geolocator-delete geolocator-delete-js tooltip" tooltip="Delete Location" href=""><i class="icon icon-trash"></i></a>' +
                        '</div>' +
                        '</div>' +
                        '<div class="content"><p class="location"><span class="bold">LatLon:</span> '+ latlon +'</p></div>' +
                        '</div>';

                    $geoCardContainer.append(newCardHtml);

                    initializeMoveAction($geoCardContainer.find('.geolocator-card-js'));
                    initializeDelete();
                    Kora.Fields.TypedFieldInputs.Initialize();

                    // Reset Modal
                    $('.location-desc-js').val('');
                    Kora.Modal.close($('.geolocator-add-location-modal-js'));
                }
            });
        }

        function initializeDelete() {
            $geoCardContainer.find('.geolocator-card-js').each(function() {
                var $card = $(this);
                var $deleteButton = $card.find('.geolocator-delete-js');

                $deleteButton.unbind();
                $deleteButton.click(function(e) {
                    e.preventDefault();

                    $card.remove();
                })
            });
        }
    }

    function initializeAssociatorOptions() {
        //Sets up association configurations
        $('.association-check-js').click(function() {
            var assocDiv = $(this).closest('.form-group').next();
            var input = assocDiv.children('select').first();
            if(this.checked) {
                assocDiv.fadeIn();
                input.prop('disabled', false).trigger("chosen:updated");
            } else {
                assocDiv.hide();
                input.prop('disabled', true).trigger("chosen:updated");
            }
        });

        $('.assoc-search-records-js').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if(keyCode === 13) {
                e.preventDefault();

                var keyword = $(this).val();
                var resultsBox = $('.assoc-select-records-js');
                //Clear old values
                resultsBox.html('');
                resultsBox.trigger("chosen:updated");

                $.ajax({
                    url: assocSearchURI,
                    type: 'POST',
                    data: {
                        "_token": csfrToken,
                        "keyword": keyword
                    },
                    success: function (result) {
                        for(var kid in result) {
                            var preview = result[kid];
                            var opt = "<option value='"+kid+"'>"+kid+": "+preview+"</option>";

                            resultsBox.append(opt);
                            resultsBox.trigger("chosen:updated");
                        }

                        resultInput = resultsBox.next().find('.chosen-search-input').first();
                        resultInput.val('');
                        resultInput.click();
                    }
                });
            }
        });

        $('.assoc-select-records-js').change(function() {
            defaultBox = $('.assoc-default-records-js');

            $(this).children('option').each(function() {
                if($(this).is(':selected')) {
                    option = $("<option/>", { value: $(this).attr("value"), text: $(this).text() });

                    defaultBox.append(option);
                    defaultBox.find(option).prop('selected', true);
                    defaultBox.trigger("chosen:updated");

                    $(this).prop("selected", false);
                }
            });

            $(this).trigger("chosen:updated");
        });
    }

    function initializeComboListOptions(){
        $('.combo-value-div-js').on('click', '.delete-combo-value-js', function() {
            parentDiv = $(this).parent();
            parentDiv.remove();
        });

        $('.combolist-add-new-list-value-modal-js').click(function(e){
            e.preventDefault();

            Kora.Modal.open($('.combolist-add-list-value-modal-js'));
        });

        $('.default-input-js').on('blur change', function(e) {
            e.preventDefault();

			$.each($('.default-input-js'), function(){
				if ($(this).val() == '' || $(this).val() == null) {
					if (!$('.add-combo-value-js').hasClass('disabled'))
						$('.add-combo-value-js').addClass('disabled');
					return false;
				} else {
					$('.add-combo-value-js').removeClass('disabled');
				}
			});
        });

        $('.add-combo-value-js').click(function() {
            if(type1=='Date') {
                monthOne = $('#month_one');
                dayOne = $('#day_one');
                yearOne = $('#year_one');
                val1 = monthOne.val()+'/'+dayOne.val()+'/'+yearOne.val();
            } else {
                inputOne = $('#default_one');
                val1 = inputOne.val();
            }

            if(type2=='Date') {
                monthTwo = $('#month_two');
                dayTwo = $('#day_two');
                yearTwo = $('#year_two');
                val2 = monthTwo.val()+'/'+dayTwo.val()+'/'+yearTwo.val();
            } else {
                inputTwo = $('#default_two');
                val2 = inputTwo.val();
            }

            defaultDiv = $('.combo-value-div-js');

            if(val1=='' | val2=='' | val1==null | val2==null | val1=='//'| val2=='//') {
                $('.combo-error-js').text('Both fields must be filled out');
            } else {
                $('.combo-error-js').text('');

                //Remove empty div if applicable
                var border = true;
                if(defaultDiv.children('.combo-list-empty').length) {
                    defaultDiv.children('.combo-list-empty').first().remove();
                    border = false;
                }

                div = '<div class="card combo-value-item-js">';

                if(type1=='Text' | type1=='List' | type1=='Number' | type1=='Date') {
                    div += '<input type="hidden" name="default_combo_one[]" value="'+val1+'">';
                    div += '<span class="combo-column">'+val1+'</span>';
                } else if(type1=='Multi-Select List' | type1=='Generated List' | type1=='Associator') {
                    div += '<input type="hidden" name="default_combo_one[]" value="'+val1.join('[!]')+'">';
                    div += '<span class="combo-column">'+val1.join(' | ')+'</span>';
                }

                if(type2=='Text' | type2=='List' | type2=='Number' | type2=='Date') {
                    div += '<input type="hidden" name="default_combo_two[]" value="'+val2+'">';
                    div += '<span class="combo-column">'+val2+'</span>';
                } else if(type2=='Multi-Select List' | type2=='Generated List' | type2=='Associator') {
                    div += '<input type="hidden" name="default_combo_two[]" value="'+val2.join('[!]')+'">';
                    div += '<span class="combo-column">'+val2.join(' | ')+'</span>';
                }

                div += '<span class="combo-delete delete-combo-value-js"><a class="quick-action delete-option delete-default-js tooltip" tooltip="Delete Default Value"><i class="icon icon-trash"></i></a></span>';

                div += '</div>';

                Kora.Modal.close();
                defaultDiv.html(defaultDiv.html()+div);
                $('.combo-value-div-js').removeClass('hidden');
                $('.combolist-add-new-list-value-modal-js').addClass('mt-xxl');

                if(type1=='Multi-Select List' | type1=='Generated List' | type1=='List' | type1=='Associator') {
                    inputOne.val('');
                    inputOne.trigger("chosen:updated");
                } else if(type1=='Date') {
                    monthOne.val(''); dayOne.val(''); yearOne.val('');
                    monthOne.trigger("chosen:updated"); dayOne.trigger("chosen:updated"); yearOne.trigger("chosen:updated");
                } else {
                    inputOne.val('');
                }

                if(type2=='Multi-Select List' | type2=='Generated List' | type2=='List' | type2=='Associator') {
                    inputTwo.val('');
                    inputTwo.trigger("chosen:updated");
                } else if(type2=='Date') {
                    monthTwo.val(''); dayTwo.val(''); yearTwo.val('');
                    monthTwo.trigger("chosen:updated"); dayTwo.trigger("chosen:updated"); yearTwo.trigger("chosen:updated");
                } else {
                    inputTwo.val('');
                }
            }
        });

        $('.combo-value-div-js').on('click', '.delete-default-js', function(e){
            e.preventDefault();

            if ($('.combo-value-div-js .card').length == 1) {
                $('.combo-value-div-js').addClass('hidden');
                $('.combolist-add-new-list-value-modal-js').removeClass('mt-xxl');
            }
        });

	    //ASSOCIATOR OPTIONS
        //Sets up association configurations
        $('.association-check-js').click(function() {
            var assocDiv = $(this).closest('.form-group').next();
            var input = assocDiv.children('select').first();
            if(this.checked) {
                assocDiv.fadeIn();
                input.prop('disabled', false).trigger("chosen:updated");
            } else {
                assocDiv.hide();
                input.prop('disabled', true).trigger("chosen:updated");
            }
        });

        $('.assoc-search-records-js').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if(keyCode === 13) {
                e.preventDefault();

                var keyword = $(this).val();
                var resultsBox = $(this).parent().next().children('.assoc-select-records-js').first();
                //Clear old values
                resultsBox.html('');
                resultsBox.trigger("chosen:updated");

                $.ajax({
                    url: assocSearchURI,
                    type: 'POST',
                    data: {
                        "_token": csrfToken,
                        "keyword": keyword
                    },
                    success: function (result) {
                        for(var kid in result) {
                            var preview = result[kid];
                            var opt = "<option value='"+kid+"'>"+kid+": "+preview+"</option>";

                            resultsBox.append(opt);
                            resultsBox.trigger("chosen:updated");
                        }
                    }
                });
            }
        });

        $('.assoc-select-records-js').change(function() {
            defaultBox = $(this).parent().siblings().first().children('.assoc-default-records-js');

            $(this).children('option').each(function() {
                if($(this).is(':selected')) {
                    option = $("<option/>", { value: $(this).attr("value"), text: $(this).text() });

                    defaultBox.append(option);
                    defaultBox.find(option).prop('selected', true);
                    defaultBox.trigger("chosen:updated");

                    $(this).prop("selected", false);
                }
            });

            $(this).trigger("chosen:updated");
        });

        //LIST OPTIONS
        function setCardTitleWidth() {
            var $cards = $('.list-option-card-js');

            $cards.each(function() {
                var $card = $(this);
                var $value = $card.find('.title');

                var maxValueWidth = $card.outerWidth() * .75;
                $value.css('max-width', maxValueWidth);
            })
        }

        // Function to add list options and the respective cards
        function initializeListAddOption(fnum) {
            var $addButton = $('.list-option-add-'+fnum+'-js');
            var $newListOptionInput = $('.new-list-option-'+fnum+'-js');
            var $cardContainer = $('.list-option-card-container-'+fnum+'-js');

            $newListOptionInput.keypress(function(e) {
                var keycode =  (e.keyCode ? e.keyCode : e.which);
                if (keycode == '13') {
                    e.preventDefault();

                    // Enter key pressed, trigger 'add' button click
                    $addButton.click();
                }
            });

            // Add new list option card after 'add' button pressed
            $addButton.click(function(e) {
                e.preventDefault();

                var newListOption = $newListOptionInput.val();

                if(newListOption!='') {
                    // Prevent duplicate entries

                    // Create and display new card
                    var newCardHtml = '<div class="card list-option-card list-option-card-js" data-list-value="' + newListOption + '">' +
                        '<input type="hidden" class="list-option-js" name="options_'+fnum+'[]" value="' + newListOption + '">' +
                        '<div class="header">' +
                        '<div class="left">' +
                        '<div class="move-actions">' +
                        '<a class="action move-action-js up-js" href="">' +
                        '<i class="icon icon-arrow-up"></i>' +
                        '</a>' +
                        '<a class="action move-action-js down-js" href="">' +
                        '<i class="icon icon-arrow-down"></i>' +
                        '</a>' +
                        '</div>' +
                        '<span class="title">' + newListOption + '</span>' +
                        '</div>' +
                        '<div class="card-toggle-wrap">' +
                        '<a class="list-option-delete list-option-delete-js" href=""><i class="icon icon-trash"></i></a>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                    $cardContainer.append(newCardHtml);

                    // Initialize functionality for all the cards again
                    $('.move-action-js').unbind();
                    setCardTitleWidth();
                    initializeListSort();
                    initializeListOptionDelete();
                    Kora.Fields.TypedFieldInputs.Initialize();

                    // Clear input after everything is finished
                    $newListOptionInput.val("");
                }
            });
        }

        function initializeListSort() {
            $('.move-action-js').click(function(e) {
                e.preventDefault();

                var $this = $(this);
                var $headerInnerWrapper = $this.parent().parent();
                var $header = $headerInnerWrapper.parent();
                var $card = $header.parent();
                // $form.prev().before(current);
                if ($this.hasClass('up-js')) {
                    var $previousForm = $card.prev();
                    if ($previousForm.length == 0) {
                        return;
                    }

                    $previousForm.css('z-index', 999)
                        .css('position', 'relative')
                        .animate({
                            top: $card.height()
                        }, 300);
                    $card.css('z-index', 1000)
                        .css('position', 'relative')
                        .animate({
                            top: '-' + $previousForm.height()
                        }, 300, function() {
                            $previousForm.css('z-index', '')
                                .css('top', '')
                                .css('position', '');
                            $card.css('z-index', '')
                                .css('top', '')
                                .css('position', '')
                                .insertBefore($previousForm);
                        });
                } else {
                    var $nextForm = $card.next();
                    if ($nextForm.length == 0) {
                        return;
                    }

                    $nextForm.css('z-index', 999)
                        .css('position', 'relative')
                        .animate({
                            top: '-' + $card.height()
                        }, 300);
                    $card.css('z-index', 1000)
                        .css('position', 'relative')
                        .animate({
                            top: $nextForm.height()
                        }, 300, function() {
                            $nextForm.css('z-index', '')
                                .css('top', '')
                                .css('position', '');
                            $card.css('z-index', '')
                                .css('top', '')
                                .css('position', '')
                                .insertAfter($nextForm);
                        });
                }
            });
        }

        function initializeListOptionDelete() {
            var $listOptionCards = $('.list-option-card-js');

            $listOptionCards.each(function() {
                var $card = $(this);
                var $deleteButton = $card.find('.list-option-delete-js');

                $deleteButton.click(function(e) {
                    e.preventDefault();

                    $card.remove();
                });
            });
        }

        setCardTitleWidth();
        initializeListAddOption('one');
        initializeListAddOption('two');
        initializeListSort();
        initializeListOptionDelete();
        Kora.Fields.TypedFieldInputs.Initialize();
    }

    function initializeTextFields() {
        var $multiLineCheck = $('.check-box-input[name="multi"]');
        var $singleLine = $('.advance-options-section-js .single-line-js');
        var $multiLine = $('.advance-options-section-js .multi-line-js');
        var $singleLineShow = $('.edit-form .single-line-js');
        var $multiLineShow = $('.edit-form .multi-line-js');

        if($multiLineCheck.is(':checked')) {
            $singleLine.addClass('hidden');
            $multiLine.removeClass('hidden');
            $singleLineShow.addClass('hidden');
            $multiLineShow.removeClass('hidden');
            var input = $singleLineShow.children('input').val();
            $multiLineShow.children('textarea').val(''+input+'');
        } else {
            $singleLineShow.removeClass('hidden');
            $multiLineShow.addClass('hidden');
            $singleLine.removeClass('hidden');
            $multiLine.addClass('hidden');
        }

        if($('.error-message.single-line').text().length > 0) {
            var erMsg = $('.error-message.single-line').text();
            $('.error-message.multi-line').text(''+erMsg+'');
            $multiLine.children('textarea').addClass('error');
        }

        $multiLineCheck.click(function () {
            if($multiLineCheck.is(':checked')) {
                $singleLine.addClass('hidden');
                $multiLine.removeClass('hidden');
                $singleLineShow.addClass('hidden');
                $multiLineShow.removeClass('hidden');
            } else {
                $singleLine.removeClass('hidden');
                $multiLine.addClass('hidden');
                $singleLineShow.removeClass('hidden');
                $multiLineShow.addClass('hidden');
            }
        });

        $multiLine.children('textarea').blur(function () {
            var input = $multiLine.children('textarea').val();
            $singleLine.children('input').val(''+input+'');
        });

        $('.error-message.single-line').bind('DOMSubtreeModified', function () {
            erMsg = $('.error-message.single-line').text();
            $('.error-message.multi-line').text(''+erMsg+'');
            $multiLine.children('textarea').addClass('error');
        });
    }

    function initializeRichTextFields() {
        $('.ckeditor-js').each(function() {
            textid = $(this).attr('id');

            CKEDITOR.replace(textid);
        });
    }

    initializeSelects();

    switch(fieldType) {
        case 'Date':
            initializeDateOptions();
            break;
        case 'Generated List':
            initializeList();
            break;
        case 'List':
            initializeList();
            break;
        case 'Geolocator':
            initializeGeolocatorOptions();
            break;
        case 'Multi-Select List':
            initializeList();
            break;
        case 'Schedule':
            initializeScheduleOptions();
            break;
        case 'Associator':
            initializeAssociatorOptions();
            break;
        case 'Combo List':
            initializeComboListOptions();
            break;
        case 'Text':
            initializeTextFields();
            break;
        case 'Rich Text':
            initializeRichTextFields();
            break;
        default:
            break;
    }
}
