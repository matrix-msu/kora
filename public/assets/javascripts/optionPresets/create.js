var Kora = Kora || {};
Kora.OptionPresets = Kora.OptionPresets || {};

Kora.OptionPresets.Create = function() {
    
    var currentPreset = -1;

    $('.single-select').chosen({
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

    function initializeOptionPresetSwitcher() {
        $('.preset-type-js').change(function() {
            var fieldType = $(this).val();
            var submitButton = $('.submit-button-js');

            submitButton.removeClass('disabled');

            switch(fieldType) {
                case 'Text':
                    openOptionPreset(['show','hide','hide','hide']);
                    enableOptionInput([null,'disabled','disabled','disabled']);
                    break;
                case 'List':
                    openOptionPreset(['hide','show','hide','hide']);
                    enableOptionInput(['disabled',null,'disabled','disabled']);
                    break;
                case 'Schedule':
                    openOptionPreset(['hide','hide','show','hide']);
                    enableOptionInput(['disabled','disabled',null,'disabled']);
                    break;
                case 'Geolocator':
                    openOptionPreset(['hide','hide','hide','show']);
                    enableOptionInput(['disabled','disabled','disabled',null]);
                    break;
                default:
                    submitButton.addClass('disabled');
                    break;
            }
        });

        function openOptionPreset(order) {
            $('.open-text-js').effect('slide', {
                direction: 'up',
                mode: order[0],
                duration: 240
            });
            $('.open-list-js').effect('slide', {
                direction: 'up',
                mode: order[1],
                duration: 240
            });
            $('.open-schedule-js').effect('slide', {
                direction: 'up',
                mode: order[2],
                duration: 240
            });
            $('.open-geolocator-js').effect('slide', {
                direction: 'up',
                mode: order[3],
                duration: 240
            });
        }

        function enableOptionInput(order) {
            var textInput = $('.open-text-js').find('.text-input').first();
            textInput.attr('disabled',order[0]);
            var listInput = $('.open-list-js').find('.modify-select').first();
            listInput.attr('disabled',order[1]);
            listInput.trigger("chosen:updated");
            var scheduleInput = $('.open-schedule-js').find('.schedule-event-js').first();
            scheduleInput.attr('disabled',order[2]);
            scheduleInput.trigger("chosen:updated");
            var geoInput = $('.open-geolocator-js').find('.geolocator-location-js').first();
            geoInput.attr('disabled',order[3]);
            geoInput.trigger("chosen:updated");
        }
    }

    function initializeSpecialInputs() {
        jQuery('.event-start-time-js').datetimepicker({
            format:'m/d/Y g:i A', inline:true, lang:'en', step: 15,
            minDate:'1900/01/31',
            maxDate:'2020/12/31'
        });

        jQuery('.event-end-time-js').datetimepicker({
            format:'m/d/Y g:i A', inline:true, lang:'en', step: 15,
            minDate:'1900/01/31',
            maxDate:'2020/12/31'
        });
    }

    function initializeScheduleOptions() {
        Kora.Modal.initialize();

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
                        //Value is good so let's add it
                        var option = $("<option>").val(val).text(val);
                        var select = $('.'+flid+'-event-js');

                        select.append(option);
                        select.find(option).prop('selected', true);
                        select.trigger("chosen:updated");

                        nameInput.val('');
                        Kora.Modal.close($('.schedule-add-event-modal-js'));
                    }
                }
            }
        });
    }

    function intializeGeolocatorOptions() {
        Kora.Modal.initialize();

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
                geoError = $('.location-desc-js');
                geoError.addClass('error');
                geoError.siblings('.error-message').text('Location description required');
            } else {
                var type = $('.location-type-js').val();

                //determine if info is good for that type
                var valid = true;
                if(type == 'LatLon') {
                    var lat = $('.location-lat-js').val();
                    var lon = $('.location-lon-js').val();

                    if(lat == '') {
                        geoError = $('.location-lat-js');
                        geoError.addClass('error');
                        geoError.siblings('.error-message').text('Latitude value required');
                        valid = false;
                    }

                    if(lon == '') {
                        geoError = $('.location-lon-js');
                        geoError.addClass('error');
                        geoError.siblings('.error-message').text('Longitude value required');
                        valid = false;
                    }
                } else if(type == 'UTM') {
                    var zone = $('.location-zone-js').val();
                    var east = $('.location-east-js').val();
                    var north = $('.location-north-js').val();

                    if(zone == '') {
                        geoError = $('.location-zone-js');
                        geoError.addClass('error');
                        geoError.siblings('.error-message').text('UTM Zone is required');
                        valid = false;
                    }

                    if(east == '') {
                        geoError = $('.location-east-js');
                        geoError.addClass('error');
                        geoError.siblings('.error-message').text('UTM Easting required');
                        valid = false;
                    }

                    if(north == '') {
                        geoError = $('.location-north-js');
                        geoError.addClass('error');
                        geoError.siblings('.error-message').text('UTM Northing required');
                        valid = false;
                    }
                } else if(type == 'Address') {
                    var addr = $('.location-addr-js').val();

                    if(addr == '') {
                        geoError = $('.location-addr-js');
                        geoError.addClass('error');
                        geoError.siblings('.error-message').text('Location address required');
                        valid = false;
                    }
                }

                //if still valid
                if(valid) {
                    //find info for other loc types
                    if(type == 'LatLon')
                        coordinateConvert({"_token": CSRFToken,type:'latlon',lat:lat,lon:lon});
                    else if(type == 'UTM')
                        coordinateConvert({"_token": CSRFToken,type:'utm',zone:zone,east:east,north:north});
                    else if(type == 'Address')
                        coordinateConvert({"_token": CSRFToken,type:'geo',addr:addr});

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
                    var desc = $('.location-desc-js').val();
                    var fullresult = '[Desc]'+desc+'[Desc]'+result;
                    var latlon = result.split('[LatLon]');
                    var utm = result.split('[UTM]');
                    var addr = result.split('[Address]');
                    var fulltext = 'Description: '+desc+' | LatLon: '+latlon[1]+' | UTM: '+utm[1]+' | Address: '+addr[1];
                    var option = $("<option/>", { value: fullresult, text: fulltext });

                    var select = $('.geolocator-location-js');
                    select.append(option);
                    select.find(option).prop('selected', true);
                    select.trigger("chosen:updated");

                    $('.location-desc-js').val('');
                    Kora.Modal.close($('.geolocator-add-location-modal-js'));
                }
            });
        }
    }

    function initializeDeletePresetModal() {
        Kora.Modal.initialize();

        $('.delete-preset-open-js').click(function(e) {
            e.preventDefault();

            currentPreset = $(this).attr('preset-id');
            Kora.Modal.open($('.delete-preset-modal-js'));
        });

        $('.delete-preset-js').click(function(e) {
            $.ajax({
                url: deletePresetURL,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "_method": 'delete',
                    "presetId": currentPreset
                },
                success: function (result) {
                    location.replace(deleteRedirect);
                }
            });
        });
    }

    function initializeValidation() {
        $('.validate-preset-js').on('click', function(e) {
            var $this = $(this);

            e.preventDefault();

            values = {};
            $.each($('.preset-form').serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(data) {
                    $('.preset-form').submit();
                },
                error: function(err) {
                    $('.error-message').text('');
                    $('.text-input, .text-area').removeClass('error');

                    $.each(err.responseJSON.errors, function(fieldName, errors) {
                        var $field = $('input[name="'+fieldName+'"]');
                        $field.addClass('error');
                        $field.siblings('.error-message').text(errors[0]);
                    });
                }
            });
        });

        $('.text-input, .text-area').on('blur', function(e) {
            var field = this.id;
            var values = {};
            values[field] = this.value;
            values['_token'] = CSRFToken;

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                error: function(err) {
                    if (err.responseJSON.errors[field] !== undefined) {
                        $('input[name="'+field+'"]').addClass('error');
                        $('input[name="'+field+'"]').siblings('.error-message').text(err.responseJSON.errors[field][0]);
                    } else {
                        $('input[name="'+field+'"]').removeClass('error');
                        $('input[name="'+field+'"]').siblings('.error-message').text('');
                    }
                }
            });
        });
    }

    initializeSelectAddition();
    initializeOptionPresetSwitcher();
    initializeSpecialInputs();
    initializeScheduleOptions();
    intializeGeolocatorOptions();
    initializeDeletePresetModal();
    initializeValidation();
}