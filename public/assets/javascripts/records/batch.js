var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Batch = function() {

    $('.single-select').chosen({
        allow_single_deselect: true,
        width: '100%',
    });

    $('.multi-select').chosen({
        width: '100%',
    });

    function initializeSelectAddition() {
        $('.chosen-search-input').on('keyup', function(e) {
            var container = $(this).parents('.chosen-container').first();

            if (e.which === 13 && container.find('li.no-results').length > 0) {
                var option = $("<option>").val(this.value).text(this.value);

                var select = container.siblings('.modify-select').first();

                select.append(option);
                select.find(option).prop('selected', true);
                select.trigger("chosen:updated");
            }
        });
    }

    function initializeSelectBatchField() {
        $('.field-to-batch-js').on('change', function(e) {
            var flid = $(this).val();

            //MAKE BUTTON WORK
            $('.batch-submit-js').removeClass('disabled');

            $('.batch-field-section-js').each(function() {
               var divID = $(this).attr('id');

                if(divID=='batch_'+flid)
                    $(this).removeClass('hidden');
                else
                    $(this).addClass('hidden');
            });
        });
    }

    function initializeSpecialInputs() {
        $('.ckeditor-js').each(function() {
            textid = $(this).attr('id');

            CKEDITOR.replace(textid);
        });

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

    function intializeAssociatorOptions() {
        $('.assoc-search-records-js').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if(keyCode === 13) {
                e.preventDefault();

                var keyword = $(this).val();
                var assocSearchURI = $(this).attr('search-url');
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

                        resultInput = resultsBox.next().find('.chosen-search-input').first();
                        resultInput.val('');
                        resultInput.click();
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
    }

    function initializeComboListOptions(){
        $('.combo-list-display').on('click', '.delete-combo-value-js', function() {
            parentDiv = $(this).parent();
            parentDiv.remove();
        });

        $('.add-combo-value-js').click(function() {
            flid = $(this).attr('flid');
            inputOne = $('#default_one_'+flid);
            inputTwo = $('#default_two_'+flid);

            val1 = inputOne.val();
            val2 = inputTwo.val();
            type1 = inputOne.closest('.combo-list-input-one').attr('cfType');
            type2 = inputTwo.closest('.combo-list-input-two').attr('cfType');

            defaultDiv = $('.combo-value-div-js-'+flid);

            if(val1=='' | val2=='' | val1==null | val2==null){
                //TODO::Error out
                console.log(val1);
                console.log(val2);
                console.log('Both fields must be filled out');
            } else {
                //Remove empty div if applicable
                if(defaultDiv.children('.combo-list-empty').first())
                    defaultDiv.children('.combo-list-empty').first().remove();

                div = '<div class="combo-value-item-js">';

                if(type1=='Text' | type1=='List' | type1=='Number') {
                    div += '<input type="hidden" name="'+flid+'_combo_one[]" value="'+val1+'">';
                    div += '<span class="combo-column">'+val1+'</span>';
                } else if(type1=='Multi-Select List' | type1=='Generated List' | type1=='Associator') {
                    div += '<input type="hidden" name="'+flid+'_combo_one[]" value="'+val1.join('[!]')+'">';
                    div += '<span class="combo-column">'+val1.join(' | ')+'</span>';
                }

                if(type2=='Text' | type2=='List' | type2=='Number') {
                    div += '<input type="hidden" name="'+flid+'_combo_two[]" value="'+val2+'">';
                    div += '<span class="combo-column">'+val2+'</span>';
                } else if(type2=='Multi-Select List' | type2=='Generated List' | type2=='Associator') {
                    div += '<input type="hidden" name="'+flid+'_combo_two[]" value="'+val2.join('[!]')+'">';
                    div += '<span class="combo-column">'+val2.join(' | ')+'</span>';
                }

                div += '<span class="combo-delete delete-combo-value-js"><a class="underline-middle-hover">[X]</a></span>';

                div += '</div>';

                defaultDiv.children('.combo-list-display').first().append(div);

                inputOne.val('');
                if(type1=='Multi-Select List' | type1=='Generated List' | type1=='List' | type1=='Associator')
                    inputOne.trigger("chosen:updated");

                inputTwo.val('');
                if(type2=='Multi-Select List' | type2=='Generated List' | type2=='List' | type2=='Associator')
                    inputTwo.trigger("chosen:updated");
            }
        });
    }

    function initializeScheduleOptions() {
        Kora.Modal.initialize();

        var flid = '';
        var start_year = 1900;
        var end_year = 2020;

        $('.add-new-default-event-js').click(function(e) {
            e.preventDefault();

            flid = $(this).attr('flid');
            start_year = $(this).attr('start'); //TODO::do this
            end_year = $(this).attr('end'); //TODO::do this

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

        var flid = '';

        $('.add-new-default-location-js').click(function(e) {
            e.preventDefault();

            flid = $(this).attr('flid');

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
                    var desc = $('.location-desc-js').val();
                    var fullresult = '[Desc]'+desc+'[Desc]'+result;
                    var latlon = result.split('[LatLon]');
                    var utm = result.split('[UTM]');
                    var addr = result.split('[Address]');
                    var fulltext = 'Description: '+desc+' | LatLon: '+latlon[1]+' | UTM: '+utm[1]+' | Address: '+addr[1];
                    var option = $("<option/>", { value: fullresult, text: fulltext });

                    var select = $('.'+flid+'-location-js');
                    select.append(option);
                    select.find(option).prop('selected', true);
                    select.trigger("chosen:updated");

                    $('.location-desc-js').val('');
                    Kora.Modal.close($('.geolocator-add-location-modal-js'));
                }
            });
        }
    }

    function intializeFileUploaderOptions() {
        //We will capture the current field when we start to upload. That way when we do upload, it's guarenteed to be that Field ID
        var lastClickedFlid = 0;

        $('.kora-file-button-js').click(function(e){
            e.preventDefault();

            lastClickedFlid = $(this).attr('flid');

            fileUploader = $(this).next().trigger('click');
        });

        $('.kora-file-upload-js').fileupload({
            dataType: 'json',
            singleFileUploads: false,
            done: function (e, data) {
                inputName = 'file'+lastClickedFlid;
                fileDiv = ".filenames-"+lastClickedFlid+"-js";

                var $field = $('#'+lastClickedFlid);
                $field.removeClass('error');
                $field.siblings('.error-message').text('');
                $.each(data.result[inputName], function (index, file) {
                    if(file.error == "") {
                        var del = '<div class="form-group mt-xxs uploaded-file">';
                        del += '<input type="hidden" name="'+inputName+'[]" value ="'+file.name+'">';
                        del += '<a href="#" class="upload-fileup-js">';
                        del += '<i class="icon icon-arrow-up"></i></a>';
                        del += '<a href="#" class="upload-filedown-js">';
                        del += '<i class="icon icon-arrow-down"></i></a>';
                        del += '<span class="ml-sm">' + file.name + '</span>';
                        del += '<a href="#" class="upload-filedelete-js ml-sm" data-url="' + file.deleteUrl + '">';
                        del += '<i class="icon icon-trash danger"></i></a></div>';
                        $(fileDiv).append(del);
                    } else {
                        $field.addClass('error');
                        $field.siblings('.error-message').text(file.error);
                        return false;
                    }
                });

                //Reset progress bar
                var progressBar = '.progress-bar-'+lastClickedFlid+'-js';
                $(progressBar).css(
                    {"width": 0, "height": 0, "margin-top": 0}
                );
            },
            fail: function (e,data){
                var error = data.jqXHR['responseText'];

                var $field = $('#'+lastClickedFlid);
                $field.removeClass('error');
                $field.siblings('.error-message').text('');
                if(error=='InvalidType'){
                    $field.addClass('error');
                    $field.siblings('.error-message').text('Invalid file type provided');
                } else if(error=='TooManyFiles'){
                    $field.addClass('error');
                    $field.siblings('.error-message').text('Max file limit was reached');
                } else if(error=='MaxSizeReached'){
                    $field.addClass('error');
                    $field.siblings('.error-message').text('One or more uploaded files is bigger than limit');
                }
            },
            progressall: function (e, data) {
                var progressBar = '.progress-bar-'+lastClickedFlid+'-js';
                var progress = parseInt(data.loaded / data.total * 100, 10);

                $(progressBar).css(
                    {"width": progress + '%', "height": '18px', "margin-top": '10px'}
                );
            }
        });

        $('.filenames').on('click', '.upload-filedelete-js', function(e) {
            e.preventDefault();

            var div = $(this).parent('.uploaded-file');
            $.ajax({
                url: $(this).attr('data-url'),
                type: 'POST',
                dataType: 'json',
                data: {
                    "_token": csrfToken,
                    "_method": 'delete'
                },
                success: function (data) {
                    div.remove();
                }
            });
        });

        $('.filenames').on('click', '.upload-fileup-js', function(e) {
            e.preventDefault();

            fileDiv = $(this).parent('.uploaded-file');

            if(fileDiv.prev('.uploaded-file').length==1){
                prevDiv = fileDiv.prev('.uploaded-file');

                fileDiv.insertBefore(prevDiv);
            }
        });

        $('.filenames').on('click', '.upload-filedown-js', function(e) {
            e.preventDefault();

            fileDiv = $(this).parent('.uploaded-file');

            if(fileDiv.next('.uploaded-file').length==1){
                nextDiv = fileDiv.next('.uploaded-file');

                fileDiv.insertAfter(nextDiv);
            }
        });
    }

    function initializeRecordValidation() {
        $('.batch-submit-js').click(function(e) {
            var $this = $(this);

            e.preventDefault();

            values = {};
            $.each($('.batch-form').serializeArray(), function(i, field) {
                if(field.name in values)
                    if(Array.isArray(values[field.name]))
                        values[field.name].push(field.value);
                    else
                        values[field.name] = [values[field.name], field.value];
                else
                    values[field.name] = field.value;
            });
            values['_method'] = 'POST';

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(err) {
                    $('.error-message').text('');
                    $('.text-input, .text-area, .cke, .chosen-container').removeClass('error');

                    if(err.errors.length==0) {
                        $('.batch-form').submit();
                    } else {
                        $.each(err.errors, function(fieldName, error) {
                            var $field = $('#'+fieldName);
                            $field.addClass('error');
                            $field.siblings('.error-message').text(error);
                        });
                    }
                }
            });
        });
    }

    initializeSelectAddition();
    initializeSelectBatchField();
    initializeSpecialInputs();
    intializeAssociatorOptions();
    initializeComboListOptions();
    initializeScheduleOptions();
    intializeGeolocatorOptions();
    intializeFileUploaderOptions();
    initializeRecordValidation();
}
