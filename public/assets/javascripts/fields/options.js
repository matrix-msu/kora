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
        Kora.Modal.initialize();

        var $start = $('.start-year-js');
        var $end = $('.end-year-js');
        var $default = $('.default-year-js');
        var $startCheck = $('.current-year-js[data-current-year-id="start"]');
        var $endCheck = $('.current-year-js[data-current-year-id="end"]');
        var $changeDefaultYearModal = $('.change-default-year-modal-js');
        var $continueButton = $changeDefaultYearModal.find('.change-default-year-js');
        var $closeModalButton = $changeDefaultYearModal.find('.modal-toggle-js');

        var oldStartVal = $start.val();
        var oldEndVal = $end.val();
        var oldStartCheck = $startCheck.is(':checked');
        var oldStartCheck = $endCheck.is(':checked');
        var currentYear = new Date().getFullYear();
        var scrollBarWidth = 17;

        $prefixCheckboxes = $('.prefix-check-js');
        $eraCheckboxes = $('.era-check-js');

        $prefixCheckboxes.click(function() {
            var $selected = $(this);
            $isChecked = $selected.prop('checked');

            $prefixCheckboxes.prop('checked', false);
            if($isChecked)
                $selected.prop('checked', true);
        });
        $eraCheckboxes.click(function() {
            var $selected = $(this);

            $eraCheckboxes.prop('checked', false);
            $selected.prop('checked', true);

            currEra = $selected.val();
            $month = $('#default_month');
            $day = $('#default_day');

            if(currEra=='BP' | currEra=='KYA BP') {
                $month.attr('disabled','disabled');
                $day.attr('disabled','disabled');
                $month.trigger("chosen:updated");
                $day.trigger("chosen:updated");
            } else {
                $month.removeAttr('disabled');
                $day.removeAttr('disabled');
                $month.trigger("chosen:updated");
                $day.trigger("chosen:updated");
            }
        });

        // Setting year value to the current year
        var $currentYearCheckboxes = $('.current-year-js');
        setCurrentYearInput($currentYearCheckboxes);

        $currentYearCheckboxes.click(function() {
            setCurrentYearInput($(this));
        });

        // Clicking arrows on numbers sets Default Year options
        $('.arrow-js').click(function() {
            printYears();
        });

        // Changing start and end dates sets Default Year options
        $start.change(printYears);
        $end.change(printYears);

        function printYears() {
            var start = $start.val();
            var end = $end.val();
            var defaultYear = $default.children("option:selected").val();

            // Set start and end years
            if (start == '' || start < 0) {start = 1;}
            if (start == 0) {start = currentYear}
            if (end == '' || end > 9999) {end = 9999;}
            if (end == 0) {end = currentYear}

            // Switch start and end if necessary
            if (start > end) {
                pivot = start;
                start = end;
                end = pivot;
            }

            if (defaultYear != "" &&
                ((defaultYear != 0 && (defaultYear < start || defaultYear > end)) ||
                (defaultYear == 0 && (currentYear < start || currentYear > end)))) {
                // User must approve of clearing default date if set outside range of dates
                Kora.Modal.open($changeDefaultYearModal);

                $continueButton.unbind();
                $continueButton.click(function(e) {
                    e.preventDefault();
                    createOptions();
                    Kora.Modal.close($changeDefaultYearModal);
                });

                $closeModalButton.unbind();
                $closeModalButton.click(function(e) {
                    e.preventDefault();

                    // Reset Values
                    $start.val(oldStartVal);
                    $startCheck.prop('checked', oldStartCheck);
                    $start.prop('disabled', oldStartCheck);
                    if (oldStartCheck) {
                        $start.siblings('.num-arrows-js').hide();
                    } else {
                        $start.siblings('.num-arrows-js').show();
                    }

                    $end.val(oldEndVal);
                    $endCheck.prop('checked', oldEndCheck);
                    $end.prop('disabled', oldEndCheck);
                    if (oldEndCheck) {
                        $end.siblings('.num-arrows-js').hide();
                    } else {
                        $end.siblings('.num-arrows-js').show();
                    }

                    Kora.Modal.close($changeDefaultYearModal);
                })
            } else {
                createOptions();
            }

            function createOptions() {
                // New options between start and end years
                var val = '<option></option>';

                if (defaultYear != "" && defaultYear == 0  && currentYear >= start && currentYear <= end) {
                    val += '<option value="0" selected>Current Year</option>';
                } else {
                    val += '<option value="0">Current Year</option>';
                }

                for (var i=start;i<+end+1;i++) {
                    if (i == defaultYear) {
                        val += "<option value=" + i + " selected>" + i + "</option>";
                    } else {
                        val += "<option value=" + i + ">" + i + "</option>";
                    }
                }

                oldStartVal = $start.val();
                oldEndVal = $end.val();
                oldStartCheck = $startCheck.is(':checked');
                oldEndCheck = $endCheck.is(':checked');

                $default.html(val); $default.trigger("chosen:updated");
            }
        }

        // Clicking on a 'Current Year' checkbox
        function setCurrentYearInput($sel) {
            $sel.each(function() {
                var $selected = $(this);
                var $yearInput = $('[data-current-year-id="'+$selected.data('current-year-id')+'"]').not('[type="checkbox"]');
                var $yearInputHidden = $yearInput.siblings('.hidden-current-year-js');
                var $arrows = $yearInput.siblings('.num-arrows-js');

                if ($selected.is(":checked")) {
                    // Current Year now selected
                    // Set input to current year
                    $yearInput.val(new Date().getFullYear());

                    // Disable input, enable hidden current year input
                    $yearInputHidden.prop('disabled', false);
                    $yearInput.prop('disabled', true);
                    $arrows.hide();
                } else {
                    // Current Year now unselected
                    // Enable input, disable hidden current year input
                    $yearInputHidden.prop('disabled', true);
                    $yearInput.prop('disabled', false);
                    $arrows.show();
                }
            });

            printYears();
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

    function initializeList(listType = '') {
        function setCardTitleWidth() {
            $(window).load(function() {
                var $cards = $('.list-option-card-js');

                $cards.each(function() {
                    var $card = $(this);
                    var $value = $card.find('.title');

                    var maxValueWidth = $card.outerWidth() * .75;
                    $value.css('max-width', maxValueWidth);
                })
            });
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

                //Splits options up by comma, but ignores commas inside of double quotes
                var newListOptions = $newListOptionInput.val().split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/);

                if(newListOptions !== undefined && newListOptions.length > 0) {
                    // Prevent duplicate entries

                    // If generated list, name of hidden input needs to be field name
                    var optionName = "options[]";
                    if (listType == 'GenList') {
                      optionName = $newListOptionInput.data('flid') + "[]";
                    }

                    //Foreach option
                    for(newOpt in newListOptions) {
                        //Trim whitespace, and remove surrounding quotes
                        newListOption = newListOptions[newOpt].replace (/(^")|("$)/g, '').trim();

                        // Create and display new card
                        var newCardHtml = '<div class="card list-option-card list-option-card-js" data-list-value="' + newListOption + '">' +
                            '<input type="hidden" class="list-option-js" name="' + optionName + '" value="' + newListOption + '">' +
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
                    }

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
            $('.list-option-card-container').sortable();

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

        function initializeMassListOptions() {
            $('.list-option-mass-copy-js').click(function(e) {
                e.preventDefault();

                var $cards = $('.list-option-card-js');
                var returnArray = [];

                if($cards.length > 0) {
                    for (var i = 0; i < $cards.length; i++) {
                        var $card = $($cards[i]);
                        var option = $card.find('.list-option-js').val();

                        if(option.includes(','))
                            option = '"'+option+'"';

                        returnArray.push(option);
                    }
                }

                var returnString = returnArray.join();

                //Send to clipboard
                copyToClipboard(returnString);
            });

            $('.list-option-mass-delete-js').click(function(e) { //TODO::MASSLISTMODAL
                e.preventDefault();

                var $cards = $('.list-option-card-js');

                if($cards.length > 0) {
                    for (var i = 0; i < $cards.length; i++) {
                        var $card = $($cards[i]);

                        $card.remove();
                    }
                }
            });

            function copyToClipboard(stringToCopy) {
                // Create a dummy input to copy the string array inside it
                var dummy = document.createElement("input");
                // Add it to the document
                document.body.appendChild(dummy);
                // Set its ID
                dummy.setAttribute("id", "copy_to_clipboard");
                // Output the array into it
                document.getElementById("copy_to_clipboard").value=stringToCopy;
                // Select it
                dummy.select();
                // Copy its contents
                document.execCommand("copy");
                // Remove it as its not needed anymore
                document.body.removeChild(dummy);
            }
        }

        setCardTitleWidth();
        initializeListAddOption();
        initializeListSort();
        initializeListOptionDelete();
        initializeMassListOptions();
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
                else if(type == 'Address')
                    coordinateConvert({"_token": csrfToken,type:'geo',addr:addr});

                $('.location-lat-js').val(''); $('.location-lon-js').val('');
                $('.location-addr-js').val('');
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
                    result['description'] = desc;
                    var latlon = result['geometry']['location']['lat']+', '+result['geometry']['location']['lng'];
                    var addr = result['formatted_address'];

                    finalResult = JSON.stringify(result).replace(/"/g, '&quot;');

                    // Create and display new geolocation card
                    var newCardHtml = '<div class="card geolocator-card geolocator-card-js">' +
                        '<input type="hidden" class="list-option-js" name="default[]" value="' + finalResult + '">' +
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
                        '</div></div>' +
                        '<div class="content">';

                    if(geoListDisplay=='LatLon')
                        newCardHtml += '<p class="location"><span class="bold">Lat Long:</span> '+ latlon +'</p>' + '</div></div>';
                    else if(geoListDisplay=='Address')
                        newCardHtml += '<p class="location"><span class="bold">Address:</span> '+ addr +'</p>' + '</div></div>';

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
            if (keyCode === 13) {
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
                        var opts = '';
                        for(var kid in result) {
                            var preview = result[kid];
                            opts += "<option value='"+kid+"'>"+kid+": "+preview+"</option>";
                        }

                        // Wait until all options are added to html string until we update chosen
                        resultsBox.append(opts);
                        resultsBox.trigger("chosen:updated");

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
            if(type1=='Date' | type1=='Historical Date') {
                monthOne = $('#default_month_one');
                dayOne = $('#default_day_one');
                yearOne = $('#default_year_one');
                if(type1=='Historical Date') {
                    $('[id^=default_era_one]').each(function () {
                        if ($(this).is(':checked')) {
                            eraOne = $(this);
                        }
                    });
                    prefixOne = '';
                    $(`[id^=default_prefix_one]`).each(function () {
                        if ($(this).is(':checked')) {
                            prefixOne = $(this);
                        }
                    });
                }
                val1 = [monthOne.val(), dayOne.val(), yearOne.val(), prefixOne!='' ? prefixOne.val() : '', eraOne.val()].filter(Boolean).join('/');
            } else {
                inputOne = $('#default_one');
                val1 = inputOne.val();
            }

            if(type1=='Boolean') {
                if (inputOne.prop('checked') != true) {
                    val1 = 0;
                }
            }

            if(type2=='Date' | type2=='Historical Date') {
                monthTwo = $('#default_month_two');
                dayTwo = $('#default_day_two');
                yearTwo = $('#default_year_two');
                if(type2=='Historical Date') {
                    $('[id^=default_era_two]').each(function () {
                        if ($(this).is(':checked')) {
                            eraTwo = $(this);
                        }
                    });
                    prefixTwo = '';
                    $(`[id^=default_prefix_two]`).each(function () {
                        if ($(this).is(':checked')) {
                            prefixTwo = $(this);
                        }
                    });
                }
                val2 = [monthTwo.val(), dayTwo.val(), yearTwo.val(), prefixTwo!='' ? prefixTwo.val() : '', eraTwo.val()].filter(Boolean).join('/');
            } else {
                inputTwo = $('#default_two');
                val2 = inputTwo.val();
            }

            if(type2=='Boolean') {
                if (inputTwo.prop('checked') != true) {
                    val2 = 0;
                }
            }

            defaultDiv = $('.combo-value-div-js');

            if(val1==null | val2==null | val1=='//'| val2=='//') {
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

                if(type1=='Text' | type1=='List' | type1=='Integer' | type1=='Float' | type1=='Boolean') {
                    div += '<input type="hidden" name="default_combo_one[]" value="'+val1+'">';
                    if(type1=='Boolean') {
                        if (val1 == 1) {
                            val1 = 'true';
                        } else if (val1 == 0)
                            val1 = 'false';
                    }
                    div += '<span class="combo-column">'+val1+'</span>';
                } else if(type1=='Date' | type1=='Historical Date') {
                    div += '<input type="hidden" name="default_day_combo_one[]" value="'+dayOne.val()+'">';
                    div += '<input type="hidden" name="default_month_combo_one[]" value="'+monthOne.val()+'">';
                    div += '<input type="hidden" name="default_year_combo_one[]" value="'+yearOne.val()+'">';
                    if(type1=='Historical Date') {
                        div += '<input type="hidden" name="default_prefix_combo_one[]" value="';
                        div += prefixOne!='' ? prefixOne.val() : '';
                        div += '">';
                        div += '<input type="hidden" name="default_era_combo_one[]" value="'+eraOne.val()+'">';
                    }
                    div += '<span class="combo-column">'+val1+'</span>';
                } else if(type1=='Multi-Select List' | type1=='Generated List' | type1=='Associator') {
                    div += '<input type="hidden" name="default_combo_one[]" value='+JSON.stringify(val1)+'>';
                    div += '<span class="combo-column">'+val1.join(' | ')+'</span>';
                }

                if(type2=='Text' | type2=='List' | type2=='Integer' | type2=='Float' | type2=='Boolean') {
                    div += '<input type="hidden" name="default_combo_two[]" value="'+val2+'">';
                    if(type2=='Boolean') {
                        if (val2 == 1)
                            val2 = 'true';
                        if (val2 == 0)
                            val2 = 'false';
                    }
                    div += '<span class="combo-column">'+val2+'</span>';
                } else if(type2=='Date' | type2=='Historical Date') {
                    div += '<input type="hidden" name="default_day_combo_two[]" value="'+dayTwo.val()+'">';
                    div += '<input type="hidden" name="default_month_combo_two[]" value="'+monthTwo.val()+'">';
                    div += '<input type="hidden" name="default_year_combo_two[]" value="'+yearTwo.val()+'">';
                    if(type2=='Historical Date') {
                        div += '<input type="hidden" name="default_prefix_combo_two[]" value="';
                        div += prefixTwo!='' ? prefixTwo.val() : '';
                        div += '">';
                        div += '<input type="hidden" name="default_era_combo_two[]" value="'+eraTwo.val()+'">';
                    }
                    div += '<span class="combo-column">'+val2+'</span>';
                } else if(type2=='Multi-Select List' | type2=='Generated List' | type2=='Associator') {
                    div += '<input type="hidden" name="default_combo_two[]" value='+JSON.stringify(val2)+'>';
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
                } else if(type1=='Date' | type1=='Historical Date') {
                    monthOne.val(''); dayOne.val(''); yearOne.val('');
                    monthOne.trigger("chosen:updated"); dayOne.trigger("chosen:updated"); yearOne.trigger("chosen:updated");
                    if(type1=='Historical Date') {
                        eraOne.prop("checked", false);
                        $("#default_era_one_ce").prop("checked", true);
                        $('[id^=default_era_one]').each(function () {
                            $(this).trigger("chosen:updated");
                        });
                        if(prefixOne!='') {
                            prefixOne.prop("checked", false);
                            $(`[id^=default_prefix_one_${flid}]`).each(function () {
                                $(this).trigger("chosen:updated");
                            });
                        }
                    }
                } else {
                    inputOne.val('');
                }

                if(type2=='Multi-Select List' | type2=='Generated List' | type2=='List' | type2=='Associator') {
                    inputTwo.val('');
                    inputTwo.trigger("chosen:updated");
                } else if(type2=='Date' | type2=='Historical Date') {
                    monthTwo.val(''); dayTwo.val(''); yearTwo.val('');
                    monthTwo.trigger("chosen:updated"); dayTwo.trigger("chosen:updated"); yearTwo.trigger("chosen:updated");
                    if(type2=='Historical Date') {
                        eraTwo.prop("checked", false);
                        $("#default_era_two_ce").prop("checked", true);
                        $('[id^=default_era_two]').each(function () {
                            $(this).trigger("chosen:updated");
                        });
                        if(prefixTwo!='') {
                            prefixTwo.prop("checked", false);
                            $(`[id^=default_prefix_two_${flid}]`).each(function () {
                                $(this).trigger("chosen:updated");
                            });
                        }
                    }
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
                var combo = $(this).data('combo');
                var resultsBox = $(this).parent().next().children('.assoc-select-records-js').first();
                //Clear old values
                resultsBox.html('');
                resultsBox.trigger("chosen:updated");

                $.ajax({
                    url: assocSearchURI,
                    type: 'POST',
                    data: {
                        "_token": csrfToken,
                        "keyword": keyword,
                        "combo": combo
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
            defaultBox = $(this).parent().next().children('.assoc-default-records-js');

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

                //Splits options up by comma, but ignores commas inside of double quotes
                var newListOptions = $newListOptionInput.val().split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/);

                if(newListOptions !== undefined && newListOptions.length > 0) {
                    // Prevent duplicate entries

                    for(newOpt in newListOptions) {
                        //Trim whitespace, and remove surrounding quotes
                        newListOption = newListOptions[newOpt].replace (/(^")|("$)/g, '').trim();

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
                    }

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

        function initializeMassListOptions(fnum) {
            $('.list-option-mass-copy-'+fnum+'-js').click(function(e) {
                e.preventDefault();

                var $cards = $('.list-option-card-container-'+fnum+'-js .list-option-card-js');
                var returnArray = [];

                if($cards.length > 0) {
                    for (var i = 0; i < $cards.length; i++) {
                        var $card = $($cards[i]);
                        var option = $card.find('.list-option-js').val();

                        if(option.includes(','))
                            option = '"'+option+'"';

                        returnArray.push(option);
                    }
                }

                var returnString = returnArray.join();

                //Send to clipboard
                copyToClipboard(returnString);
            });

            $('.list-option-mass-delete-'+fnum+'-js').click(function(e) { //TODO::MASSLISTMODAL
                e.preventDefault();

                var $cards = $('.list-option-card-container-'+fnum+'-js .list-option-card-js');

                if($cards.length > 0) {
                    for (var i = 0; i < $cards.length; i++) {
                        var $card = $($cards[i]);

                        $card.remove();
                    }
                }
            });

            function copyToClipboard(stringToCopy) {
                // Create a dummy input to copy the string array inside it
                var dummy = document.createElement("input");
                // Add it to the document
                document.body.appendChild(dummy);
                // Set its ID
                dummy.setAttribute("id", "copy_to_clipboard");
                // Output the array into it
                document.getElementById("copy_to_clipboard").value=stringToCopy;
                // Select it
                dummy.select();
                // Copy its contents
                document.execCommand("copy");
                // Remove it as its not needed anymore
                document.body.removeChild(dummy);
            }
        }

        function initializeDateOptions() {
            $eraCheckboxes = $('.era-check-js');
            $prefixCheckboxes = $('.prefix-check-js');

            $prefixCheckboxes.click(function() {
                var $selected = $(this);
                $isChecked = $selected.prop('checked');

                $prefixCheckboxes.prop('checked', false);
                if($isChecked)
                    $selected.prop('checked', true);
            });
            $eraCheckboxes.click(function() {
                var $selected = $(this);

                $eraCheckboxes.prop('checked', false);
                $selected.prop('checked', true);

                currEra = $selected.val();
                $month = $('[id^=default_month_]');
                $day = $('[id^=default_day_]');

                if(currEra=='BP' | currEra=='KYA BP') {
                    $month.attr('disabled','disabled').val('');
                    $day.attr('disabled','disabled').val('');
                    $month.trigger("chosen:updated");
                    $day.trigger("chosen:updated");
                } else {
                    $month.removeAttr('disabled');
                    $day.removeAttr('disabled');
                    $month.trigger("chosen:updated");
                    $day.trigger("chosen:updated");
                }
            });
        }

        setCardTitleWidth();
        initializeListAddOption('one');
        initializeListAddOption('two');
        initializeListSort();
        initializeListOptionDelete();
        initializeMassListOptions('one');
        initializeMassListOptions('two');
        initializeDateOptions();
        Kora.Fields.TypedFieldInputs.Initialize();
    }

    function initializeTextFields() {
        var $multiLineCheck = $('.check-box-input[name="multi"]');
        var $singleLine = $('.advance-options-section-js .single-line-js');
        var $multiLine = $('.advance-options-section-js .multi-line-js');
        var $singleLineShow = $('.edit-form .single-line-js');
        var $multiLineShow = $('.edit-form .multi-line-js');

        if($multiLineCheck.is(':checked')) {
            $('.text-default-js').attr('disabled','disabled');
            $('.text-area-default-js').removeAttr('disabled');

            $singleLine.addClass('hidden');
            $multiLine.removeClass('hidden');
            $singleLineShow.addClass('hidden');
            $multiLineShow.removeClass('hidden');
            var input = $singleLineShow.children('input').val();
            $multiLineShow.children('textarea').val(''+input+'');
        } else {
            $('.text-default-js').removeAttr('disabled');
            $('.text-area-default-js').attr('disabled','disabled');

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
                $('.text-default-js').attr('disabled','disabled');
                $('.text-area-default-js').removeAttr('disabled');

                $singleLine.addClass('hidden');
                $multiLine.removeClass('hidden');
                $singleLineShow.addClass('hidden');
                $multiLineShow.removeClass('hidden');
            } else {
                $('.text-default-js').removeAttr('disabled');
                $('.text-area-default-js').attr('disabled','disabled');

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
            initializeList('GenList');
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
