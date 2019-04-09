var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Create = function() {

    $('.single-select').chosen({
        allow_single_deselect: true,
        disable_search_threshold: 4,
        width: '100%',
    });

    $('.multi-select').chosen({
        width: '100%',
    });

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

    function initializeSpecialInputs() {
        $('.ckeditor-js').each(function() {
            textid = $(this).attr('id');

            CKEDITOR.replace(textid);
        });
    }

    function intializeAssociatorOptions() {
        $('.assoc-search-records-js').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if(keyCode === 13) {
                e.preventDefault();

                var keyword = $(this).val();
                var combo = $(this).data('combo');
                var assocSearchURI = $(this).attr('search-url');
                var resultsBox = $(this).parent().next().children('.assoc-select-records-js').first();
                //Clear old values
                resultsBox.html('');
                resultsBox.trigger("chosen:updated");

                var data = {
                    "_token": csrfToken,
                    "keyword": keyword
                };

                if (combo) {
                    data['combo'] = combo;
                }

                $.ajax({
                    url: assocSearchURI,
                    type: 'POST',
                    data: data,
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
    }

    function initializeComboListOptions(){
        var flid, type1, type2, $comboValueDiv, $modal;

        $('.combo-list-display').on('click', '.delete-combo-value-js', function() {
            parentDiv = $(this).parent();
            parentDiv.remove();
        });

        $('.open-combo-value-modal-js').click(function(e) {
          flid = $(this).attr('flid');
          type1 = $(this).attr('typeOne');
          type2 = $(this).attr('typeTwo');

          $comboValueDiv = $('.combo-value-div-js-'+flid);
          var $modal = $comboValueDiv.find('.combo-list-modal-js');

          Kora.Modal.close();
          Kora.Modal.open($modal);
        });

        $('.add-combo-value-js').click(function() {
            if(type1=='Date') {
                monthOne = $('#default_month_one_'+flid);
                dayOne = $('#default_day_one_'+flid);
                yearOne = $('#default_year_one_'+flid);
                val1 = yearOne.val()+'-'+monthOne.val()+'-'+dayOne.val();
            } else {
                inputOne = $('#default_one_'+flid);
                val1 = inputOne.val();
            }

            if(type2=='Date') {
                monthTwo = $('#default_month_two_'+flid);
                dayTwo = $('#default_day_two_'+flid);
                yearTwo = $('#default_year_two_'+flid);
                val2 = yearTwo.val()+'-'+monthTwo.val()+'-'+dayTwo.val();
            } else {
                inputTwo = $('#default_two_'+flid);
                val2 = inputTwo.val();
            }

            if(val1=='' | val2=='' | val1==null | val2==null | val1=='//'| val2=='//') {
                $('.combo-error-'+flid+'-js').text('Both fields must be filled out');
            } else {
                $('.combo-error-'+flid+'-js').text('');

                if($comboValueDiv.find('.combo-list-empty').length) {
                    $comboValueDiv.find('.combo-list-empty').first().remove();
                }

                div = '<div class="combo-value-item combo-value-item-js">';

                if(type1=='Text' | type1=='List' | type1=='Integer' | type1=='Float' | type1=='Date') {
                    div += '<input type="hidden" name="'+flid+'_combo_one[]" value="'+val1+'">';
                    div += '<span class="combo-column">'+val1+'</span>';
                } else if(type1=='Multi-Select List' | type1=='Generated List' | type1=='Associator') {
                    div += '<input type="hidden" name="'+flid+'_combo_one[]" value='+JSON.stringify(val1)+'>';
                    div += '<span class="combo-column">'+val1.join(' | ')+'</span>';
                }

                if(type2=='Text' | type2=='List' | type2=='Integer' | type2=='Float' | type2=='Date') {
                    div += '<input type="hidden" name="'+flid+'_combo_two[]" value="'+val2+'">';
                    div += '<span class="combo-column">'+val2+'</span>';
                } else if(type2=='Multi-Select List' | type2=='Generated List' | type2=='Associator') {
                    div += '<input type="hidden" name="'+flid+'_combo_two[]" value='+JSON.stringify(val2)+'>';
                    div += '<span class="combo-column">'+val2.join(' | ')+'</span>';
                }

                div += '<span class="combo-delete delete-combo-value-js tooltip" tooltip="Delete Combo Value"><i class="icon icon-trash"></i></span>';

                div += '</div>';

                $comboValueDiv.find('.combo-value-item-container-js').append(div);

                if(type1=='Multi-Select List' | type1=='Generated List' | type1=='List' | type1=='Associator') {
                    inputOne.val('');
                    inputOne.trigger("chosen:updated");
                } else if(type1=='Date') {
                    monthOne.trigger("chosen:updated"); dayOne.trigger("chosen:updated"); yearOne.trigger("chosen:updated");
                } else {
                    inputOne.val('');
                }

                if(type2=='Multi-Select List' | type2=='Generated List' | type2=='List' | type2=='Associator') {
                    inputTwo.val('');
                    inputTwo.trigger("chosen:updated");
                } else if(type2=='Date') {
                    monthTwo.trigger("chosen:updated"); dayTwo.trigger("chosen:updated"); yearTwo.trigger("chosen:updated");
                } else {
                    inputTwo.val('');
                }

                Kora.Modal.close();
            }
        });
    }

    function initializeDateOptions() {
        var $dateFormGroups = $('.date-input-form-group-js');
        var $dateListInputs = $dateFormGroups.find('.chosen-container');
        var scrollBarWidth = 17;

        $eraCheckboxes = $('.era-check-js');

        $eraCheckboxes.click(function() {
            var $selected = $(this);
            flid = $selected.attr('flid');

            $('.era-check-'+flid+'-js').prop('checked', false);
            $selected.prop('checked', true);

            currEra = $selected.val();
            $month = $('#month_'+flid);
            $day = $('#day_'+flid);

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

        setTextInputWidth();

        $(window).resize(setTextInputWidth);

        function setTextInputWidth() {
            if ($(window).outerWidth() < 1175 - scrollBarWidth) {
                // Window is small, full width Inputs
                $dateListInputs.css('width', '100%');
                $dateListInputs.css('margin-bottom', '10px');
            } else {
                // Window is large, 1/3 width Inputs
                $dateListInputs.css('width', '33%');
                $dateListInputs.css('margin-bottom', '');
            }
        }
    }

    function intializeGeolocatorOptions() {
        Kora.Modal.initialize();
        var flid = '';
        var geoListDisplay = '';

        var $geoCardContainers = $('.geolocator-card-container-js');
        var $geoCards = $geoCardContainers.find('.geolocator-card-js');
        var $newLocationButtons = $('.add-new-default-location-js');

        // Action arrows on the cards
        initializeMoveAction($geoCards);

        // Drag cards to sort
        $geoCardContainers.sortable();

        // Delete card
        initializeDelete();

        $newLocationButtons.click(function(e) {
            e.preventDefault();

            flid = $(this).attr('flid');
            geoListDisplay = $(this).attr('display-type');

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
                        '<input type="hidden" class="list-option-js" name="'+flid+'[]" value="' + finalResult + '">' +
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

                    var $geoCardContainer = $('.geolocator-'+flid+'-js').find('.geolocator-card-container-js');
                    $geoCardContainer.append(newCardHtml);

                    initializeMoveAction($geoCardContainer.find('.geolocator-card-js'));
                    initializeDelete();
                    Kora.Fields.TypedFieldInputs.Initialize();

                    $('.location-desc-js').val('');
                    Kora.Modal.close($('.geolocator-add-location-modal-js'));
                }
            });
        }

        function initializeDelete() {
            $geoCardContainers.find('.geolocator-card-js').each(function() {
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

    function intializeFileUploaderOptions() {
        var $fileUploads = $('.kora-file-upload-js');
        var $fileCardsContainer = $fileUploads.parent().find('.file-cards-container-js');
        //We will capture the current field when we start to upload. That way when we do upload, it's guarenteed to be that Field ID
        var lastClickedFlid = 0;

        // Prevents upload to whole web page
        $(document).bind('drop dragover', function (e) {
            e.preventDefault();
        });

        $fileUploads.each(function() {
            var $fileUpload = $(this);

            $('#'+$fileUpload.attr('id')).fileupload({
                dataType: 'json',
                dropZone: $('#'+$fileUpload.attr('id')).parent(),
                singleFileUploads: false,
                done: function (e, data) {
                    var $uploadInput = $(this);
                    lastClickedFlid = $uploadInput.attr('flid');
                    console.log(lastClickedFlid);
                    inputName = 'file'+lastClickedFlid;
                    capName = 'file_captions'+lastClickedFlid;
                    fileDiv = ".filenames-"+lastClickedFlid+"-js";

                    var $formGroup = $uploadInput.parent('.form-group');

                    // Tooltip text
                    var tooltip = "Remove Document";
                    if ($formGroup.hasClass('gallery-input-form-group')) {
                        tooltip = "Remove Image";
                    } else if ($formGroup.hasClass('video-input-form-group')) {
                        tooltip = "Remove Video";
                    } else if ($formGroup.hasClass('audio-input-form-group')) {
                        tooltip = "Remove Audio";
                    } else if ($formGroup.hasClass('3d-model-input-form-group')) {
                        tooltip = "Remove 3D Model";
                    }

                    $uploadInput.removeClass('error');
                    $uploadInput.siblings('.error-message').text('');
                    $.each(data.result[inputName], function (index, file) {
                        if(file.error == "" || !file.hasOwnProperty('error')) {
                            // Add caption only if input is a gallery
                            var captionHtml = '';
                            if ($formGroup.hasClass('gallery-input-form-group')) {
                                captionHtml = '<textarea type="text" name="' + capName + '[]" class="caption autosize-js" placeholder="Enter caption here"></textarea>';
                            }
                            // File card html
                            var fileCardHtml = '<div class="card file-card file-card-js">' +
                                '<input type="hidden" name="' + lastClickedFlid + '[]" value ="' + file.name + '">' +
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
                                '<span class="title">' + file.name + '</span>' +
                                '</div>' +
                                '<div class="card-toggle-wrap">' +
                                '<a href="#" class="file-delete upload-filedelete-js ml-sm tooltip" tooltip="'+tooltip+'" data-url="' + file.deleteUrl + '">' +
                                '<i class="icon icon-trash danger"></i>' +
                                '</a>' +
                                '</div>' +
                                captionHtml +
                                '</div>' +
                                '</div>';
                            console.log(fileCardHtml);

                            // Add file card to list of cards
                            $formGroup.find(fileDiv).append(fileCardHtml);

                            // Change directions text
                            $formGroup.find('.directions-empty-js').removeClass('active');
                            $formGroup.find('.directions-not-empty-js').addClass('active');

                            // Reinitialize inputs
                            Kora.Fields.TypedFieldInputs.Initialize();
                            Kora.Inputs.Textarea();
                        } else {
                            $field.addClass('error');
                            $field.siblings('.error-message').text(file.error);
                            return false;
                        }
                    });

                    //Reset progress bar
                    var progressBar = '.progress-bar-'+lastClickedFlid+'-js';
                    $formGroup.find(progressBar).css(
                        {"width": 0, "height": 0, "margin-top": 0}
                    );
                },
                fail: function (e,data){
                    var $uploadInput = $(this);
                    var $errorMessage = $uploadInput.siblings('.error-message');

                    var error = data.jqXHR['responseText'];
                    lastClickedFlid = $uploadInput.attr('flid');

                    var $field = $uploadInput.siblings('#'+lastClickedFlid);

                    $field.removeClass('error');
                    $field.siblings('.error-message').text('');
                    if(error=='InvalidType'){
                        $field.addClass('error');
                        $errorMessage.text('Invalid file type provided');
                    } else if(error=='TooManyFiles'){
                        $field.addClass('error');
                        $errorMessage.text('Max file limit was reached');
                    } else if(error=='MaxSizeReached'){
                        $field.addClass('error');
                        $errorMessage.text('One or more uploaded files is bigger than limit');
                    } else {
                        $field.addClass('error');
                        $errorMessage.text('Error uploading file');
                    }
                },
                progressall: function (e, data) {
                    var $uploadInput = $(this);
                    var $formGroup = $uploadInput.parent();
                    var progressBar = '.progress-bar-'+lastClickedFlid+'-js';
                    var progress = parseInt(data.loaded / data.total * 100, 10);

                    $formGroup.find(progressBar).css(
                        {"width": progress + '%', "height": '18px', "margin-top": '10px'}
                    );
                }
            });
        });

        $fileCardsContainer.on('click', '.upload-filedelete-js', function(e) {
            e.preventDefault();

            var $fileCard = $(this).parent().parent().parent('.file-card-js');
            var $container = $fileCard.parent();

            $.ajax({
                url: $(this).attr('data-url'),
                type: 'POST',
                dataType: 'json',
                data: {
                    "_token": csrfToken,
                    "_method": 'delete'
                },
                success: function (data) {
                    $fileCard.remove();

                    // Change directions text
                    if ($fileCardsContainer.children().length > 0) {
                        $container.siblings('.directions-empty-js').removeClass('active');
                        $container.siblings('.directions-not-empty-js').addClass('active');
                    } else {
                        $container.siblings('.directions-empty-js').addClass('active');
                        $container.siblings('.directions-not-empty-js').removeClass('active');
                    }
                }
            });
        });

        // Move file card up
        $fileCardsContainer.on('click', '.up-js', function(e) {
            e.preventDefault();

            var $fileCard = $(this).parent().parent().parent().parent('.file-card-js');

            if ($fileCard.prev('.file-card-js').length == 1) {
                var $prevCard = $fileCard.prev('.file-card-js');

                $fileCard.insertBefore($prevCard);
            }
        });

        // Move file card down
        $fileCardsContainer.on('click', '.down-js', function(e) {
            e.preventDefault();

            var $fileCard = $(this).parent().parent().parent().parent('.file-card-js');

            if ($fileCard.next('.file-card-js').length == 1) {
                var $nextCard = $fileCard.next('.file-card-js');

                $fileCard.insertAfter($nextCard);
            }
        });

        // Drag file cards to reorder
        $fileCardsContainer.sortable();

        Kora.Fields.TypedFieldInputs.Initialize();
        Kora.Inputs.Textarea();
    }

    function initializePageNavigation() {
        $('.page-section-js').first().removeClass('hidden');
        $('.toggle-by-name').first().addClass('active');

        $('.toggle-by-name').click(function(e) {
            e.preventDefault();

            $this = $(this);
            $this.addClass('active');
            $this.siblings().removeClass('active');

            var pageNumber = $this.parent().children().index(this);
            var $pageLinks = $('.pagination .pages .page-link');

            $pageLinks.removeClass('active');
            $($pageLinks.get(pageNumber)).addClass('active');

            if (pageNumber === 0) {
                $('.previous.page').addClass('disabled');
            } else {
                $('.previous.page').removeClass('disabled');
            }

            if (pageNumber === $pageLinks.length - 1) {
                $('.next.page').addClass('disabled');
            } else {
                $('.next.page').removeClass('disabled');
            }

            $active = $this.attr("href");
            $('.page-section-js').each(function() {
                if($(this).attr('id') == $active)
                    $(this).removeClass('hidden');
                else
                    $(this).addClass('hidden');
            });
        });

        $('.page-link').click(function(e) {
            e.preventDefault();

            var pageNumber = $(this).text() - 1;
            $('.toggle-by-name').get(pageNumber).click();
        });

        $('.pagination .page').click(function(e) {
            e.preventDefault();

            var pageNumber = $('.pagination .pages .page-link.active').index();

            if ($(this).hasClass('next')) {
                $('.toggle-by-name').get(pageNumber + 1).click();
            } else if ($(this).hasClass('previous')) {
                $('.toggle-by-name').get(pageNumber - 1).click();
            }
        })
    }

    function initializeRecordPresets() {
        $('.preset-check-js').click(function() {
            var presetDiv = $('.preset-record-div-js');
            if(this.checked) {
                presetDiv.fadeIn();
            } else {
                presetDiv.hide();
                //CLEAR FIELDS
                $('.preset-clear-text-js').each(function(){ $(this).val(''); });
                $('.preset-clear-chosen-js').each(function(){ $(this).val(''); $(this).trigger("chosen:updated"); });
                $('.preset-clear-file-js').html('');
                $('.preset-clear-combo-js').each(function(){ $('.combo-value-item-js',this).remove(); });
            }
        });

        $('.preset-record-js').change(function() {
            $.ajax({
                url: getPresetDataUrl,
                type: 'POST',
                data: {
                    '_token': csrfToken,
                        'id': $(this).val()
                }, success: function(response) {
                    putArray(response);
                }
            });
        });

        function putArray(ary) {
            var data = ary['data'];
            var fields = ary['fields']
            var presetID = $('.preset-record-js').val();

            var i;
            for(var flid in data) {
                value = data[flid];
                type = fields[flid]['type'];

                if(value != null) {
                    switch (type) {
                        //TODO:: modular?
                        case 'Text':
                            $('[name=' + flid + ']').val(value);
                            break;
                        // case 'Rich Text':
                        //     CKEDITOR.instances[flid].setData(field['rawtext']);
                        //     break;
                        // case 'Number':
                        //     $('[name=' + flid + ']').val(field['number']);
                        //     break;
                        // case 'List':
                        //     $('[name=' + flid + ']').val(field['option']).trigger("chosen:updated");
                        //     break;
                        // case 'Multi-Select List':
                        //     $('#list' + flid).val(field['options']).trigger("chosen:updated");
                        //     break;
                        // case 'Generated List':
                        //     var options = field['options'];
                        //     var valArray = [];
                        //     var h = 0;
                        //     var selector = $("#list" + flid);
                        //     for (var k = 0; k < options.length; k++) {
                        //         if ($("#list" + flid + " option[value='" + options[k] + "']").length > 0) {
                        //             valArray[h] = options[k];
                        //             h++;
                        //         }
                        //         else {
                        //             selector.append($('<option/>', {
                        //                 value: options[k],
                        //                 text: options[k],
                        //                 selected: 'selected'
                        //             }));
                        //             valArray[h] = options[k];
                        //             h++;
                        //         }
                        //     }
                        //     selector.val(valArray).trigger("chosen:updated");
                        //     break;
                        // case 'Date':
                        //     var date = field['data'];
                        //
                        //     if (date['circa'])
                        //         $('[name=circa_' + flid + ']').prop('checked', true);
                        //     $('[name=month_' + flid + ']').val(date['month']).trigger("chosen:updated");
                        //     $('[name=day_' + flid + ']').val(date['day']).trigger("chosen:updated");
                        //     $('[name=year_' + flid + ']').val(date['year']).trigger("chosen:updated");
                        //     $('[name=era_' + flid + ']').val(date['era']).trigger("chosen:updated");
                        //     break;
                        // case 'Schedule':
                        //     var j, events = field['events'];
                        //     var selector = $('.' + flid + '-event-js');
                        //     $('.' + flid + '-event-js option[value!="0"]').remove();
                        //
                        //     for (j = 0; j < events.length; j++) {
                        //         selector.append($('<option/>', {
                        //             value: events[j],
                        //             text: events[j],
                        //             selected: 'selected'
                        //         })).trigger("chosen:updated");
                        //     }
                        //     break;
                        // case 'Geolocator':
                        //     var l, locations = field['locations'];
                        //     var selector = $('.' + flid + '-location-js');
                        //     $('.' + flid + '-location-js option[value!="0"]').remove();
                        //
                        //     for (l = 0; l < locations.length; l++) {
                        //         selector.append($('<option/>', {
                        //             value: locations[l],
                        //             text: locations[l],
                        //             selected: 'selected'
                        //         })).trigger("chosen:updated");
                        //     }
                        //     break;
                        // case 'Combo List':
                        //     var p, combos = field['combolists'];
                        //     var selector = $('.combo-value-div-js-' + flid);
                        //
                        //     // Empty defaults, we need to do this as the preset may have done so.
                        //     // However if it hasn't, the defaults will be in the preset so this is safe.
                        //     selector.find('.combo-value-item-js').each(function () {
                        //         $(this).remove();
                        //     });
                        //     selector.find('.combo-list-empty').each(function () {
                        //         $(this).remove();
                        //     });
                        //
                        //     for (p = 0; p < combos.length; p++) {
                        //         var rawData = combos[p];
                        //
                        //         var field1RawData = rawData.split('[!f1!]')[1];
                        //         var field2RawData = rawData.split('[!f2!]')[1];
                        //
                        //         var field1ToPrint = field1RawData.split('[!]');
                        //         var field2ToPrint = field2RawData.split('[!]');
                        //
                        //         var html = '<div class="combo-value-item-js">';
                        //
                        //         if (field1ToPrint.length == 1) {
                        //             html += '<input type="hidden" name="' + flid + '_combo_one[]" value="' + field1ToPrint + '">';
                        //             html += '<span class="combo-column">' + field1ToPrint + '</span>';
                        //         } else {
                        //             html += '<input type="hidden" name="' + flid + '_combo_one[]" value="' + field1ToPrint.join('[!]') + '">';
                        //             html += '<span class="combo-column">' + field1ToPrint.join(' | ') + '</span>';
                        //         }
                        //
                        //         if (field2ToPrint.length == 1) {
                        //             html += '<input type="hidden" name="' + flid + '_combo_two[]" value="' + field2ToPrint + '">';
                        //             html += '<span class="combo-column">' + field2ToPrint + '</span>';
                        //         } else {
                        //             html += '<input type="hidden" name="' + flid + '_combo_two[]" value="' + field2ToPrint.join('[!]') + '">';
                        //             html += '<span class="combo-column">' + field2ToPrint.join(' | ') + '</span>';
                        //         }
                        //
                        //         html += '<span class="combo-delete delete-combo-value-js"><a class="underline-middle-hover">[X]</a></span>';
                        //
                        //         html += '</div>';
                        //
                        //         selector.children('.combo-list-display').first().append(html);
                        //     }
                        //     break;
                        // case 'Documents':
                        //     applyFilePreset(field['documents'], presetID, flid);
                        //     break;
                        // case 'Gallery':
                        //     applyGalleryPreset(field, presetID, flid);
                        //     break;
                        // case 'Playlist':
                        //     applyFilePreset(field['audio'], presetID, flid);
                        //     break;
                        // case 'Video':
                        //     applyFilePreset(field['video'], presetID, flid);
                        //     break;
                        // case '3D-Model':
                        //     applyFilePreset(field['model'], presetID, flid);
                        //     break;
                        // case 'Associator':
                        //     var r, records = field['records'];
                        //     console.log(field['records']);
                        //     var selector = $('#' + flid);
                        //     $('#' + flid + ' option[value!="0"]').remove();
                        //
                        //     for (r = 0; r < records.length; r++) {
                        //         selector.append($('<option/>', {
                        //             value: records[r],
                        //             text: records[r],
                        //             selected: 'selected'
                        //         })).trigger("chosen:updated");
                        //     }
                        //     break;
                    }
                }
            }
        }

        /**
         * Applies the preset for a file type field
         */
        function applyFilePreset(typeIndex, presetID, flid) { //TODO::CASTLE
            var filenames = $(".filenames-"+flid+"-js");
            filenames.empty();

            if (!typeIndex) { /* Do nothing. */ }
            else {
                moveFiles(presetID, flid, userID);

                for (var z = 0; z < typeIndex.length; z++) {
                    filename = typeIndex[z].split('[Name]')[1];
                    filenames.append(fileDivHTML(filename, flid, userID));
                }
            }
        }

        /**
         * Applies the preset for a file type field
         */
        function applyGalleryPreset(field, presetID, flid) { //TODO::CASTLE
            var filenames = $(".filenames-"+flid+"-js");
            filenames.empty();

            var typeIndex = field["images"];
            var captions = field["captions"];

            if (!typeIndex) { /* Do nothing. */ }
            else {
                moveFiles(presetID, flid, userID);

                for (var z = 0; z < typeIndex.length; z++) {
                    filename = typeIndex[z].split('[Name]')[1];
                    filenames.append(galDivHTML(filename, captions[z], flid, userID));
                }
            }
        }

        /**
         * Generates the HTML for an uploaded file's div.
         */
        function fileDivHTML(filename, flid, userID) { //TODO::CASTLE
            // Build the delete file url.
            var deleteUrl = baseFileUrl;
            deleteUrl += 'f' + flid + 'u' + userID + '/' + myUrlEncode(filename);

            var HTML = '<div class="card file-card file-card-js">';
            HTML += '<input type="hidden" name="file'+flid+'[]" value ="'+filename+'">';
            HTML += '<div class="header"><div class="left"><div class="move-actions">';
            HTML += '<a class="action move-action-js up-js" href=""><i class="icon icon-arrow-up"></i></a>';
            HTML += '<a class="action move-action-js down-js" href=""><i class="icon icon-arrow-down"></i></a></div>';
            HTML += '<span class="title">'+filename+'</span></div>';
            HTML += '<div class="card-toggle-wrap">';
            HTML += '<a href="#" class="file-delete upload-filedelete-js ml-sm tooltip" tooltip="Remove Image" data-url="'+deleteUrl+'">';
            HTML += '<i class="icon icon-trash danger"></i></a></div>';
            HTML += '</div></div>';

            return HTML;
        }

        /**
         * Generates the HTML for an uploaded file's div with the gallery captions.
         */
        function galDivHTML(filename, caption, flid, userID) { //TODO::CASTLE
            // Build the delete file url.
            var deleteUrl = baseFileUrl;
            deleteUrl += 'f' + flid + 'u' + userID + '/' + myUrlEncode(filename);

            var HTML = '<div class="card file-card file-card-js">';
            HTML += '<input type="hidden" name="file'+flid+'[]" value ="'+filename+'">';
            HTML += '<div class="header"><div class="left"><div class="move-actions">';
            HTML += '<a class="action move-action-js up-js" href=""><i class="icon icon-arrow-up"></i></a>';
            HTML += '<a class="action move-action-js down-js" href=""><i class="icon icon-arrow-down"></i></a></div>';
            HTML += '<span class="title">'+filename+'</span></div>';
            HTML += '<div class="card-toggle-wrap">';
            HTML += '<a href="#" class="file-delete upload-filedelete-js ml-sm tooltip" tooltip="Remove Image" data-url="'+deleteUrl+'">';
            HTML += '<i class="icon icon-trash danger"></i></a></div>';
            HTML += '<textarea type="text" name="file_captions'+flid+'[]" class="caption autosize-js" placeholder="Enter caption here">'+caption+'</textarea>';
            HTML += '</div></div>';

            return HTML;
        }

        /**
         * Encodes a string for a url.
         *
         * Javascript's encode function wasn't playing nice with our system so I wrote this based off of
         * a post on the PHP.net user contributions on the urlencode() page davis dot pexioto at gmail dot com
         */
        function myUrlEncode(to_encode) { //TODO::CASTLE
            // Build array of characters that need to be replaced.
            var replace = ['!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?",
                "%", "#", "[", "]"];

            // Build array of the replacements for the characters listed above.
            var entities = ['%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B',
                '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'];

            // Replace them in the string!
            for(var i = 0; i < entities.length; i++) {
                to_encode = to_encode.replace(replace[i], entities[i]);
            }

            return to_encode;
        }

        //Move files from preset directory to tmp directory
        function moveFiles(presetID, flid, userID) { //TODO::CASTLE
            $.ajax({
                url: moveFilesUrl,
                type: 'POST',
                data: {
                    '_token': csrfToken,
                    'presetID': presetID,
                    'flid': flid,
                    'userID': userID
                }
            });
        }
    }

    function initializeDuplicateRecord() {
        //The one that matters during execution
        $('.duplicate-check-js').click(function() {
            var duplicateDiv = $('.duplicate-record-js');
            var input = duplicateDiv.children('input').first();
            if(this.checked) {
                duplicateDiv.fadeIn();
                input.removeAttr('disabled');
            } else {
                duplicateDiv.hide();
                input.attr('disabled','disabled');
            }
        });
    }

    function initializeNewRecordPreset() {
        //The one that matters during execution
        $('.newRecPre-check-js').click(function() {
            var newRecPreDiv = $('.newRecPre-record-js');
            var input = newRecPreDiv.children('input').first();
            if(this.checked) {
                newRecPreDiv.fadeIn();
                input.removeAttr('disabled');
            } else {
                newRecPreDiv.hide();
                input.attr('disabled','disabled');
            }
        });
    }

    function multiSelectPlaceholders () {
      var inputDef = $('.chosen-container-multi').children('.chosen-choices');

      inputDef.on('click', function() {
        var childCheck = $(this).siblings('.chosen-drop').children('.chosen-results');
        if (childCheck.children().length === 0) {
          childCheck.append('<li class="no-results">No options to select!</li>');
        } else if (childCheck.children('.active-result').length === 0 && childCheck.children('.no-results').length === 0) {
          childCheck.append('<li class="no-results">No more options to select!</li>');
        }
      });

      inputDef.children('.search-field').children('input').blur(function() {
        var childCheck = inputDef.siblings('.chosen-drop').children('.chosen-results');
        if (childCheck.children('.no-results').length > 0) {
          childCheck.children('.no-results').remove();
        }
      });
    }

    initializeSelectAddition();
    initializeSpecialInputs();
    intializeAssociatorOptions();
    initializeComboListOptions();
    initializeDateOptions();
    intializeGeolocatorOptions();
    intializeFileUploaderOptions();
    initializePageNavigation();
    initializeRecordPresets();
    initializeDuplicateRecord();
    initializeNewRecordPreset();
    Kora.Records.Modal();
    multiSelectPlaceholders();
    Kora.Inputs.Number();
}
