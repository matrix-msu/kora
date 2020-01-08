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

    function initializeGenListOptions () {
        let list = $('.genlist-js');
        // The actual HTML is generated and handled in Kora.Fields.Options()
        // This section just handles adding and removing the options from the multiselect list
        // Option Removed
        $('.list-option-card-js .list-option-delete-js').click(function (e) {
            e.preventDefault();

            let options = Array.from ( document.getElementById( list.attr('id') ).children )
            let opt = this.parentElement.parentElement.children[0].children[1].innerText
            let removeMe = options.find ( function ( option ) {
                return option.innerText == opt
            })
            removeMe.remove();

            list.trigger('chosen:updated');
        });

        // Option Added
        function optionAdded ( newOption ) {
            list.append('<option value="' + newOption + '" selected="selected"">' + newOption + '</option>');
            list.trigger('chosen:updated');
        }

        $('.new-list-option-js').on('keyup', function (e) {
            e.preventDefault();
            let keyCode = e.keyCode || e.which
            if ( e.keyCode == 13 )
                optionAdded ( $(this).val() )
        });

        $('.list-option-add-js').click(function (e) {
            e.preventDefault();
            optionAdded ( $('.new-list-option-js').val() )
        });

        //list reordered
        $('.list-option-card-container-js').sortable({
            // update: function () {
            //     let options = $('.list-option-card-container-js').sortable('toArray');
            //     let chosen_choices = list.siblings('.chosen-container').find('.search-choice');
            //     chosen_choices = $.map( chosen_choices, function ( val ) {
            //         return val.children[0].innerText
            //     });
            //     list.children().remove();

            //     options.forEach ( function ( option ) {
            //         if ( chosen_choices.includes(option) )
            //             list.append('<option value="' + option + '" selected="selected">' + option + '</option>');
            //         else
            //             list.append('<option value="' + option + '">' + option + '</option>');
            //     });
            //     list.trigger('chosen:updated');
            // }
        });
    }

    function initializeComboListOptions() {
        var flid, type1, type2, $comboValueDiv, $modal;

        $('.combo-list-display-js').on('click', '.delete-combo-value-js', function() {
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
            inputOne = $('#default_one_'+flid);
            val1 = inputOne.val();

            inputTwo = $('#default_two_'+flid);
            val2 = inputTwo.val();

            //TODO::COMBO_FINISH

            // if(type1=='Date'| type1=='Historical Date') {
            //     monthOne = $('#default_month_one_'+flid);
            //     dayOne = $('#default_day_one_'+flid);
            //     yearOne = $('#default_year_one_'+flid);
            //     val1 = [yearOne.val(), monthOne.val(), dayOne.val()].filter(Boolean).join('-');
            //     if(type1=='Historical Date') {
            //         $(`[id^=default_era_one_${flid}]`).each(function () {
            //             if ($(this).is(':checked')) {
            //                 eraOne = $(this);
            //             }
            //         });
            //         prefixOne = '';
            //         $(`[id^=default_prefix_one_${flid}]`).each(function () {
            //             if ($(this).is(':checked')) {
            //                 prefixOne = $(this);
            //             }
            //         });
            //     }
            // } else {
            //     inputOne = $('#default_one_'+flid);
            //     val1 = inputOne.val();
            // }
            //
            // if(type1=='Boolean') {
            //     if (inputOne.prop('checked') != true) {
            //         val1 = 0;
            //     }
            // } else if(type1=='Generated List') {
            //     var tmpName = 'default_one_'+flid+'[]';
            //     val1 = $("input[name='"+tmpName+"']")
            //         .map(function(x, elm) { return elm.value; })
            //         .get();
            // }
            //
            // if(type2=='Date' | type2=='Historical Date') {
            //     monthTwo = $('#default_month_two_'+flid);
            //     dayTwo = $('#default_day_two_'+flid);
            //     yearTwo = $('#default_year_two_'+flid);
            //     val2 = [yearTwo.val(), monthTwo.val(), dayTwo.val()].filter(Boolean).join('-');
            //     if(type2=='Historical Date') {
            //         $(`[id^=default_era_two_${flid}]`).each(function () {
            //             if ($(this).is(':checked')) {
            //                 eraTwo = $(this);
            //             }
            //         });
            //         prefixTwo = '';
            //         $(`[id^=default_prefix_two_${flid}]`).each(function () {
            //             if ($(this).is(':checked')) {
            //                 prefixTwo = $(this);
            //             }
            //         });
            //     }
            // } else {
            //     inputTwo = $('#default_two_'+flid);
            //     val2 = inputTwo.val();
            // }
            //
            // if(type2=='Boolean') {
            //     if (inputTwo.prop('checked') != true) {
            //         val2 = 0;
            //     }
            // } else if(type2=='Generated List') {
            //     var tmpName = 'default_two_'+flid+'[]';
            //     val2 = $("input[name='"+tmpName+"']")
            //         .map(function(x, elm) { return elm.value; })
            //         .get();
            // }

            if(val1==null | val2==null) {// | val1=='//'| val2=='//') { //TODO::COMBO_FINISH
                $('.combo-error-'+flid+'-js').text('Both fields must be filled out');
            } else {
                $('.combo-error-'+flid+'-js').text('');

                if($comboValueDiv.find('.combo-list-empty').length) {
                    $comboValueDiv.find('.combo-list-empty').first().remove();
                }

                div = '<div class="combo-value-item combo-value-item-js">';

                div += '<input type="hidden" name="'+flid+'_combo_one[]" value="'+val1+'">';
                div += '<span class="combo-column">'+val1+'</span>';

                div += '<input type="hidden" name="'+flid+'_combo_two[]" value="'+val2+'">';
                div += '<span class="combo-column">'+val2+'</span>';

                //TODO::COMBO_FINISH

                // if(type1=='Text' | type1=='List' | type1=='Integer' | type1=='Float' | type1=='Date' | type1=='Boolean') {
                //     div += '<input type="hidden" name="'+flid+'_combo_one[]" value="'+val1+'">';
                //     if(type1=='Boolean') {
                //         if (val1 == 1) {
                //             val1 = 'true';
                //         } else if (val1 == 0)
                //             val1 = 'false';
                //     }
                //     div += '<span class="combo-column">'+val1+'</span>';
                // } else if(type1=='Multi-Select List' | type1=='Generated List' | type1=='Associator') {
                //     div += '<input type="hidden" name="'+flid+'_combo_one[]" value='+JSON.stringify(val1)+'>';
                //     div += '<span class="combo-column">'+val1.join(' | ')+'</span>';
                // } else if (type1=='Historical Date') {
                //     dateJson = {
                //         "day":dayOne.val(),
                //         "era":eraOne.val(),
                //         "year":yearOne.val(),
                //         "prefix":prefixOne!='' ? prefixOne.val() : prefixOne,
                //         "month":monthOne.val()
                //     };
                //     div += '<input type="hidden" name="'+flid+'_combo_one[]" value='+JSON.stringify(dateJson)+'>';
                //     div += '<span class="combo-column">'+val1+'</span>';
                // }
                //
                // if(type2=='Text' | type2=='List' | type2=='Integer' | type2=='Float' | type2=='Date' | type2=='Boolean') {
                //     div += '<input type="hidden" name="'+flid+'_combo_two[]" value="'+val2+'">';
                //     if(type2=='Boolean') {
                //         if (val2 == 1)
                //             val2 = 'true';
                //         if (val2 == 0)
                //             val2 = 'false';
                //     }
                //     div += '<span class="combo-column">'+val2+'</span>';
                // } else if(type2=='Multi-Select List' | type2=='Generated List' | type2=='Associator') {
                //     div += '<input type="hidden" name="'+flid+'_combo_two[]" value='+JSON.stringify(val2)+'>';
                //     div += '<span class="combo-column">'+val2.join(' | ')+'</span>';
                // } else if (type2=='Historical Date') {
                //     dateJson = {
                //         "day":dayTwo.val(),
                //         "era":eraTwo.val(),
                //         "year":yearTwo.val(),
                //         "prefix":prefixTwo!='' ? prefixTwo.val() : prefixTwo,
                //         "month":monthTwo.val()
                //     };
                //     div += '<input type="hidden" name="'+flid+'_combo_two[]" value='+JSON.stringify(dateJson)+'>';
                //     div += '<span class="combo-column">'+val2+'</span>';
                // }

                div += '<span class="combo-delete delete-combo-value-js tooltip" tooltip="Delete Combo Value"><i class="icon icon-trash"></i></span>';

                div += '</div>';

                $comboValueDiv.find('.combo-value-item-container-js').append(div);

                //TODO::COMBO_FINISH

                inputOne.val('').trigger("chosen:updated");
                inputTwo.val('').trigger("chosen:updated");

                // if(type1=='Date' | type1=='Historical Date') {
                //     monthOne.trigger("chosen:updated"); dayOne.trigger("chosen:updated"); yearOne.trigger("chosen:updated");
                //     if(type1=='Historical Date') {
                //         eraOne.prop("checked", false);
                //         $(`#default_era_one_${flid}_ce`).prop("checked", true);
                //         $(`[id^=default_era_one_${flid}]`).each(function () {
                //             $(this).trigger("chosen:updated");
                //         });
                //         if(prefixOne!='') {
                //             prefixOne.prop("checked", false);
                //             $(`[id^=default_prefix_one_${flid}]`).each(function () {
                //                 $(this).trigger("chosen:updated");
                //             });
                //         }
                //     }
                // } else
                //     inputOne.val('').trigger("chosen:updated");
                //
                // if(type2=='Date' | type2=='Historical Date') {
                //     monthTwo.trigger("chosen:updated"); dayTwo.trigger("chosen:updated"); yearTwo.trigger("chosen:updated");
                //     if(type2=='Historical Date') {
                //         eraTwo.prop("checked", false);
                //         $(`#default_era_two_${flid}_ce`).prop("checked", true);
                //         $(`[id^=default_era_two_${flid}]`).each(function () {
                //             $(this).trigger("chosen:updated");
                //         });
                //         if(prefixTwo!='') {
                //             prefixTwo.prop("checked", false);
                //             $(`[id^=default_prefix_two_${flid}]`).each(function () {
                //                 $(this).trigger("chosen:updated");
                //             });
                //         }
                //     }
                // } else
                //     inputTwo.val('').trigger("chosen:updated");

                Kora.Modal.close();
            }
        });
    }

    function initializeDateOptions() {
        var $dateFormGroups = $('.date-input-form-group-js');
        var $dateListInputs = $dateFormGroups.find('.chosen-container');
        var scrollBarWidth = 17;

        $prefixCheckboxes = $('.prefix-check-js');
        $eraCheckboxes = $('.era-check-js');

        $prefixCheckboxes.click(function() {
            var $selected = $(this);
            flid = $selected.attr('flid');
            $isChecked = $selected.prop('checked');

            $('.prefix-check-'+flid+'-js').prop('checked', false);
            if($isChecked)
                $selected.prop('checked', true);
        });
        $eraCheckboxes.click(function() {
            var $selected = $(this);
            flid = $selected.attr('flid');

            $('.era-check-'+flid+'-js').prop('checked', false);
            $selected.prop('checked', true);

            currEra = $selected.val();
            $month = $('#month_'+flid);
            $day = $('#day_'+flid);

            // Combolist historic specific attribute
            var fnum = $(this).attr('fnum');
            if (typeof fnum !== typeof undefined && fnum !== false) {
                $month = $(`#default_month_${fnum}_${flid}`);
                $day = $(`#default_day_${fnum}_${flid}`);
            }

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

        //setTextInputWidth();

        //$(window).resize(setTextInputWidth);

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
            console.log("file upload: "+$fileUpload);
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
                                '<textarea type="text" name="' + capName + '[]" class="caption autosize-js" placeholder="Enter caption here"></textarea>' +
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
                    if(error=='InvalidFileNames'){
                        $field.addClass('error');
                        $errorMessage.text('Invalid file with illegal characters provided');
                    } else if(error=='InvalidType'){
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
            var fields = ary['fields'];
            var presetID = $('.preset-record-js').val();

            moveFiles(presetID);

            for(var flid in data) {
                value = data[flid];
                type = fields[flid]['type'];

                if(value != null) {
                    switch (type) {
                        case 'Text':
                            $('[name=' + flid + ']').val(value);
                            break;
                        case 'Rich Text':
                            CKEDITOR.instances[flid].setData(value);
                            break;
                        case 'Integer':
                            $('[name=' + flid + ']').val(value);
                            break;
                        case 'Float':
                            $('[name=' + flid + ']').val(value);
                            break;
                        case 'List':
                            $('[name=' + flid + ']').val(value).trigger("chosen:updated");
                            break;
                        case 'Multi-Select List':
                            $('#list' + flid).val(JSON.parse(value)).trigger("chosen:updated");
                            break;
                        case 'Generated List':
                            var options = JSON.parse(value);
                            var valArray = [];
                            var h = 0;
                            var selector = $("#list" + flid);

                            $('#' + flid + ' option[value!="0"]').remove();
                            for (var k = 0; k < options.length; k++) {
                                if ($("#list" + flid + " option[value='" + options[k] + "']").length > 0) {
                                    valArray[h] = options[k];
                                    h++;
                                }
                                else {
                                    selector.append($('<option/>', {
                                        value: options[k],
                                        text: options[k],
                                        selected: 'selected'
                                    }));
                                    valArray[h] = options[k];
                                    h++;
                                }
                            }
                            selector.val(valArray).trigger("chosen:updated");
                            break;
                        case 'Date':
                            var date = moment(value);
                            var month = ("0" + (date.month()+1) ).slice(-2);

                            $('[name=month_' + flid + ']').val(month).trigger("chosen:updated");
                            $('[name=day_' + flid + ']').val(date.date()).trigger("chosen:updated");
                            $('[name=year_' + flid + ']').val(date.year()).trigger("chosen:updated");
                            break;
                        case 'DateTime':
                            var date = moment(value);
                            var month = ("0" + (date.month()+1) ).slice(-2);

                            $('[name=month_' + flid + ']').val(month).trigger("chosen:updated");
                            $('[name=day_' + flid + ']').val(date.date()).trigger("chosen:updated");
                            $('[name=year_' + flid + ']').val(date.year()).trigger("chosen:updated");
                            $('[name=hour_' + flid + ']').val(date.hour()).trigger("chosen:updated");
                            $('[name=minute_' + flid + ']').val(date.minute()).trigger("chosen:updated");
                            $('[name=second_' + flid + ']').val(date.second()).trigger("chosen:updated");
                            break;
                        case 'Historical Date':
                            var date = JSON.parse(value);

                            $('[name=prefix_' + flid + ']').val(date['prefix']).trigger("chosen:updated");
                            $('[name=month_' + flid + ']').val(date['month']).trigger("chosen:updated");
                            $('[name=day_' + flid + ']').val(date['day']).trigger("chosen:updated");
                            $('[name=year_' + flid + ']').val(date['year']).trigger("chosen:updated");
                            $('[name=era_' + flid + ']').val(date['era']).trigger("chosen:updated");
                            break;
                        case 'Boolean':
                            if(value)
                                $('[name=' + flid + ']').prop('checked', true);
                            break;
                        case 'Geolocator':
                            var locations = JSON.parse(value);
                            var geoDiv = $('.geolocator-' + flid + '-js').find('.geolocator-card-container-js');
                            var viewType = fields[flid]['options']['DataView'];

                            locations.forEach(function (location, index) {
                                geoDiv.append(geoDivHTML(location,flid,viewType));
                            });

                            break;
                        case 'Associator':
                            var r, records = JSON.parse(value);

                            var selector = $('#' + flid);
                            $('#' + flid + ' option[value!="0"]').remove();

                            for (r = 0; r < records.length; r++) {
                                selector.append($('<option/>', {
                                    value: records[r],
                                    text: records[r],
                                    selected: 'selected'
                                })).trigger("chosen:updated");
                            }
                            break;
                        case 'Documents':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Document'));
                            });

                            break;
                        case 'Gallery':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Image'));
                            });

                            break;
                        case 'Playlist':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Audio'));
                            });

                            break;
                        case 'Video':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Video'));
                            });

                            break;
                        case '3D-Model':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Model File'));
                            });

                            break;
                        case 'Combo List': //TODO::COMBO_FINISH
                            var comboDiv = $('.combo-value-div-js-' + flid + ' .combo-value-item-container-js');
                            comboDiv.html('');

                            value.forEach(function (cVal, index) {
                                comboDiv.append(
                                    '<div class="combo-value-item combo-value-item-js">' +
                                    '<span class="combo-delete delete-combo-value-js tooltip" tooltip="Delete Combo Value"><i class="icon icon-trash"></i></span>' +
                                    '<input type="hidden" name="' + flid + '_combo_one[]" value="' + cVal[0] + '">' +
                                    '<span class="combo-column combo-value">' + cVal[0] + '</span>' +
                                    '<input type="hidden" name="' + flid + '_combo_two[]" value="' + cVal[1] + '">' +
                                    '<span class="combo-column combo-value">' + cVal[1] + '</span>' +
                                    '</div>'
                                );
                            });

                            break;
                    }
                }
            }
        }

        //Move files from preset to tmp directory
        function moveFiles(presetID) {
            $.ajax({
                url: moveFilesUrl,
                type: 'POST',
                data: {
                    '_token': csrfToken,
                    'presetID': presetID
                }
            });
        }

        /**
         * Generates the HTML for an geolocator's div.
         */
        function geoDivHTML(location, flid, viewType) {
            var desc = location['description'];
            var latlon = location['geometry']['location']['lat']+', '+location['geometry']['location']['lng'];
            var address = location['formatted_address'];
            var finalResult = JSON.stringify(location);

            var HTML = '<div class="card geolocator-card geolocator-card-js">';
            HTML += '<input type="hidden" class="list-option-js" name="'+flid+'[]" value="'+finalResult+'">';
            HTML += '<div class="header">';
            HTML += '<div class="left">';
            HTML += '<div class="move-actions">';
            HTML += '<a class="action move-action-js up-js" href=""><i class="icon icon-arrow-up"></i></a>';
            HTML += '<a class="action move-action-js down-js" href=""><i class="icon icon-arrow-down"></i></a>';
            HTML += '</div>';
            HTML += '<span class="title">'+desc+'</span>';
            HTML += '</div>';
            HTML += '<div class="card-toggle-wrap">';
            HTML += '<a class="geolocator-delete geolocator-delete-js tooltip" tooltip="Delete Location" href=""><i class="icon icon-trash"></i></a>';
            HTML += '</div>';
            HTML += '</div>';
            if(viewType == 'LatLon')
                HTML += '<div class="content"><p class="location"><span class="bold">LatLon:</span> '+latlon+'</p></div>';
            else if(viewType == 'Address')
                HTML += '<div class="content"><p class="location"><span class="bold">Address:</span> '+address+'</p></div>';
            HTML += '</div>';

            return HTML;
        }

        /**
         * Generates the HTML for an uploaded file's div.
         */
        function fileDivHTML(file, flid, btnName) {
            var name = file['name'];
            var caption = file['caption'];
            deleteUrl = deleteFileUrl+flid+"/"+name;

            var HTML = '<div class="card file-card file-card-js">';
            HTML += '<input type="hidden" name="'+flid+'[]" value="'+name+'">';
            HTML += '<div class="header">';
            HTML += '<div class="left">';
            HTML += '<div class="move-actions">';
            HTML += '<a class="action move-action-js up-js" href=""><i class="icon icon-arrow-up"></i></a>';
            HTML += '<a class="action move-action-js down-js" href=""><i class="icon icon-arrow-down"></i></a>';
            HTML += '</div>';
            HTML += '<span class="title">'+name+'</span>';
            HTML += '</div>';
            HTML += '<div class="card-toggle-wrap">';
            HTML += '<a href="#" class="file-delete upload-filedelete-js ml-sm tooltip" tooltip="Remove '+btnName+'" data-url="'+deleteUrl+'">';
            HTML += '<i class="icon icon-trash danger"></i>';
            HTML += '</a>';
            HTML += '</div>';
            HTML += '<textarea type="text" name="file_captions'+flid+'[]" class="caption autosize-js" placeholder="Enter caption here">'+caption+'</textarea>';
            HTML += '</div>';
            HTML += '</div>';

            return HTML;
        }
    }

    function initializeDuplicateRecord() {
        //The one that matters during execution
        $('.duplicate-check-js').click(function() {
            var duplicateDiv = $('.duplicate-record-js');
            var input = duplicateDiv.find('input').first();
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
    //initializeGenListOptions();
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
