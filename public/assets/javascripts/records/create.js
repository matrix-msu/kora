var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Create = function() {

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
            type1 = $(this).attr('typeOne');
            type2 = $(this).attr('typeTwo');

            if(type1=='Date') {
                monthOne = $('#month_one_'+flid);
                dayOne = $('#day_one_'+flid);
                yearOne = $('#year_one_'+flid);
                val1 = monthOne.val()+'/'+dayOne.val()+'/'+yearOne.val();
            } else {
                inputOne = $('#default_one_'+flid);
                val1 = inputOne.val();
            }

            if(type2=='Date') {
                monthTwo = $('#month_two_'+flid);
                dayTwo = $('#day_two_'+flid);
                yearTwo = $('#year_two_'+flid);
                val2 = monthTwo.val()+'/'+dayTwo.val()+'/'+yearTwo.val();
            } else {
                inputTwo = $('#default_two_'+flid);
                val2 = inputTwo.val();
            }

            defaultDiv = $('.combo-value-div-js-'+flid);

            if(val1=='' | val2=='' | val1==null | val2==null){
                //TODO::Error out
                console.log(val1);
                console.log(val2);
                console.log('Both fields must be filled out');
            } else {
                //Remove empty div if applicable
                if(defaultDiv.find('.combo-list-empty').first())
                    defaultDiv.find('.combo-list-empty').first().remove();

                div = '<div class="combo-value-item-js">';

                if(type1=='Text' | type1=='List' | type1=='Number' | type1=='Date') {
                    div += '<input type="hidden" name="'+flid+'_combo_one[]" value="'+val1+'">';
                    div += '<span class="combo-column">'+val1+'</span>';
                } else if(type1=='Multi-Select List' | type1=='Generated List' | type1=='Associator') {
                    div += '<input type="hidden" name="'+flid+'_combo_one[]" value="'+val1.join('[!]')+'">';
                    div += '<span class="combo-column">'+val1.join(' | ')+'</span>';
                }

                if(type2=='Text' | type2=='List' | type2=='Number' | type2=='Date') {
                    div += '<input type="hidden" name="'+flid+'_combo_two[]" value="'+val2+'">';
                    div += '<span class="combo-column">'+val2+'</span>';
                } else if(type2=='Multi-Select List' | type2=='Generated List' | type2=='Associator') {
                    div += '<input type="hidden" name="'+flid+'_combo_two[]" value="'+val2.join('[!]')+'">';
                    div += '<span class="combo-column">'+val2.join(' | ')+'</span>';
                }

                div += '<span class="combo-delete delete-combo-value-js"><a class="underline-middle-hover">[X]</a></span>';

                div += '</div>';

                defaultDiv.children('.combo-list-display').first().append(div);

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

            var nameInput = $('.event-name-js');
            var sTimeInput = $('.event-start-time-js');
            var eTimeInput = $('.event-end-time-js');

            var name = nameInput.val().trim();
            var sTime = sTimeInput.val().trim();
            var eTime = eTimeInput.val().trim();

            if(name==''|sTime==''|eTime=='') {
                //TODO::show error
            } else {
                if($('.event-allday-js').is(":checked")) {
                    sTime = sTime.split(" ")[0];
                    eTime = eTime.split(" ")[0];
                }

                if(sTime>eTime) {
                    //TODO::show error
                }else {
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

            //check to see if description provided
            var desc = $('.location-desc-js').val();
            if(desc=='') {
                //TODO::show error
            } else {
                var type = $('.location-type-js').val();

                //determine if info is good for that type
                var valid = true;
                if(type == 'LatLon') {
                    var lat = $('.location-lat-js').val();
                    var lon = $('.location-lon-js').val();

                    if(lat == '' | lon == '') {
                        //TODO::show error
                        valid = false;
                    }
                } else if(type == 'UTM') {
                    var zone = $('.location-zone-js').val();
                    var east = $('.location-east-js').val();
                    var north = $('.location-north-js').val();

                    if(zone == '' | east == '' | north == '') {
                        //TODO::show error
                        valid = false;
                    }
                } else if(type == 'Address') {
                    var addr = $('.location-addr-js').val();

                    if(addr == '') {
                        //TODO::show error
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
                //$('#file_error'+lastClickedFlid).text(''); //TODO:: MAKE THESE ERRORS USEFUL
                inputName = 'file'+lastClickedFlid;
                fileDiv = ".filenames-"+lastClickedFlid+"-js";

                $.each(data.result[inputName], function (index, file) {
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
                });

                //Reset progress bar
                var progressBar = '.progress-bar-'+lastClickedFlid+'-js';
                $(progressBar).css(
                    {"width": 0, "height": 0, "margin-top": 0}
                );
            },
            fail: function (e,data){
                var error = data.jqXHR['responseText'];
                console.log(error);

                //TODO:: MAKE THESE ERRORS USEFUL
                if(error=='InvalidType'){
                    //$('#file_error{{$field->flid}}').text('');
                } else if(error=='TooManyFiles'){
                    //$('#file_error{{$field->flid}}').text('');
                } else if(error=='MaxSizeReached'){
                    //$('#file_error{{$field->flid}}').text('');
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
            var flids = ary['flids'];
            var data = ary['data'];
            var presetID = $('#presetselect').val();

            var i;
            for (i = 0; i < flids.length; i++) {
                var flid = flids[i];
                var field = data[flid];

                if(field != null) {
                    switch (field['type']) {
                        //TODO:: modular?
                        case 'Text':
                            $('[name=' + flid + ']').val(field['text']);
                            break;
                        case 'Rich Text':
                            CKEDITOR.instances[flid].setData(field['rawtext']);
                            break;
                        case 'Number':
                            $('[name=' + flid + ']').val(field['number']);
                            break;
                        case 'List':
                            $('[name=' + flid + ']').val(field['option']).trigger("chosen:updated");
                            break;
                        case 'Multi-Select List':
                            $('#list' + flid).val(field['options']).trigger("chosen:updated");
                            break;
                        case 'Generated List':
                            var options = field['options'];
                            var valArray = [];
                            var h = 0;
                            var selector = $("#list" + flid);
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
                            var date = field['data'];

                            if (date['circa'])
                                $('[name=circa_' + flid + ']').prop('checked', true);
                            $('[name=month_' + flid + ']').val(date['month']).trigger("chosen:updated");
                            $('[name=day_' + flid + ']').val(date['day']).trigger("chosen:updated");
                            $('[name=year_' + flid + ']').val(date['year']).trigger("chosen:updated");
                            $('[name=era_' + flid + ']').val(date['era']).trigger("chosen:updated");
                            break;
                        case 'Schedule':
                            var j, events = field['events'];
                            var selector = $('.' + flid + '-event-js');
                            $('.' + flid + '-event-js option[value!="0"]').remove();

                            for (j = 0; j < events.length; j++) {
                                selector.append($('<option/>', {
                                    value: events[j],
                                    text: events[j],
                                    selected: 'selected'
                                })).trigger("chosen:updated");
                            }
                            break;
                        case 'Geolocator':
                            var l, locations = field['locations'];
                            var selector = $('.' + flid + '-location-js');
                            $('.' + flid + '-location-js option[value!="0"]').remove();

                            for (l = 0; l < locations.length; l++) {
                                selector.append($('<option/>', {
                                    value: locations[l],
                                    text: locations[l],
                                    selected: 'selected'
                                })).trigger("chosen:updated");
                            }
                            break;
                        case 'Combo List':
                            var p, combos = field['combolists'];
                            var selector = $('.combo-value-div-js-' + flid);

                            // Empty defaults, we need to do this as the preset may have done so.
                            // However if it hasn't, the defaults will be in the preset so this is safe.
                            selector.find('.combo-value-item-js').each(function () {
                                $(this).remove();
                            });
                            selector.find('.combo-list-empty').each(function () {
                                $(this).remove();
                            });

                            for (p = 0; p < combos.length; p++) {
                                var rawData = combos[p];

                                var field1RawData = rawData.split('[!f1!]')[1];
                                var field2RawData = rawData.split('[!f2!]')[1];

                                var field1ToPrint = field1RawData.split('[!]');
                                var field2ToPrint = field2RawData.split('[!]');

                                var html = '<div class="combo-value-item-js">';

                                if (field1ToPrint.length == 1) {
                                    html += '<input type="hidden" name="' + flid + '_combo_one[]" value="' + field1ToPrint + '">';
                                    html += '<span class="combo-column">' + field1ToPrint + '</span>';
                                } else {
                                    html += '<input type="hidden" name="' + flid + '_combo_one[]" value="' + field1ToPrint.join('[!]') + '">';
                                    html += '<span class="combo-column">' + field1ToPrint.join(' | ') + '</span>';
                                }

                                if (field2ToPrint.length == 1) {
                                    html += '<input type="hidden" name="' + flid + '_combo_two[]" value="' + field2ToPrint + '">';
                                    html += '<span class="combo-column">' + field2ToPrint + '</span>';
                                } else {
                                    html += '<input type="hidden" name="' + flid + '_combo_two[]" value="' + field2ToPrint.join('[!]') + '">';
                                    html += '<span class="combo-column">' + field2ToPrint.join(' | ') + '</span>';
                                }

                                html += '<span class="combo-delete delete-combo-value-js"><a class="underline-middle-hover">[X]</a></span>';

                                html += '</div>';

                                selector.children('.combo-list-display').first().append(html);
                            }
                            break;
                        case 'Documents':
                            applyFilePreset(field['documents'], presetID, flid);
                            break;
                        case 'Gallery':
                            applyFilePreset(field['images'], presetID, flid);
                            break;
                        case 'Playlist':
                            applyFilePreset(field['audio'], presetID, flid);
                            break;
                        case 'Video':
                            applyFilePreset(field['video'], presetID, flid);
                            break;
                        case '3D-Model':
                            applyFilePreset(field['model'], presetID, flid);
                            break;
                        case 'Associator':
                            var r, records = field['records'];
                            console.log(field['records']);
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
                    }
                }
            }
        }

        /**
         * Applies the preset for a file type field
         */
        function applyFilePreset(typeIndex, presetID, flid) {
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
         * Generates the HTML for an uploaded file's div.
         */
        function fileDivHTML(filename, flid, userID) {
            // Build the delete file url.
            var deleteUrl = baseFileUrl;
            deleteUrl += 'f' + flid + 'u' + userID + '/' + myUrlEncode(filename);

            var HTML = '<div class="form-group mt-xxs uploaded-file">';
            HTML += '<input type="hidden" name="file'+flid+'[]" value ="'+filename+'">';
            HTML += '<a href="#" class="upload-fileup-js">';
            HTML += '<i class="icon icon-arrow-up"></i></a>';
            HTML += '<a href="#" class="upload-filedown-js">';
            HTML += '<i class="icon icon-arrow-down"></i></a>';
            HTML += '<span class="ml-sm">' + filename + '</span>';
            HTML += '<a href="#" class="upload-filedelete-js ml-sm" data-url="' + deleteUrl + '">';
            HTML += '<i class="icon icon-trash danger"></i></a></div>';

            return HTML;
        }

        /**
         * Encodes a string for a url.
         *
         * Javascript's encode function wasn't playing nice with our system so I wrote this based off of
         * a post on the PHP.net user contributions on the urlencode() page davis dot pexioto at gmail dot com
         */
        function myUrlEncode(to_encode) {
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
        function moveFiles(presetID, flid, userID) {
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

    initializeSelectAddition();
    initializeSpecialInputs();
    intializeAssociatorOptions();
    initializeComboListOptions();
    initializeScheduleOptions();
    intializeGeolocatorOptions();
    intializeFileUploaderOptions();
    initializePageNavigation();
    initializeRecordPresets();
    initializeDuplicateRecord();
    initializeNewRecordPreset();
    Kora.Records.Modal();
}
