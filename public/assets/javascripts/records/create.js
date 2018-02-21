var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Create = function() {

    $('.single-select').chosen({
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
                    // var del = '<div id="uploaded_file_div">' + file.name + ' ';
                    // del += '<input type="hidden" name="'+inputName+'[]" value ="'+file.name+'">';
                    // del += '<button id="up" class="btn" type="button">Up</button>';
                    // del += '<button id="down"class="btn" type="button">Down</button>';
                    // del += '<button class="btn btn-danger" type="button" data-type="' + file.deleteType + '" data-url="' + file.deleteUrl + '" >';
                    // del += '<i class="glyphicon glyphicon-trash" />Delete</button>';
                    // del += '</div>';
                    var del = '<div class="form-group mt-xxs uploaded-file">';
                    del += '<input type="hidden" name="'+inputName+'[]" value ="'+file.name+'">';
                    del += '<a href="#" class="upload-fileup-js">';
                    del += '<i class="icon icon-arrow-up"></i>';
                    del += '</a>';
                    del += '<a href="#" class="upload-filedown-js">';
                    del += '<i class="icon icon-arrow-down"></i>';
                    del += '</a>';
                    del += '<span class="ml-sm">' + file.name + '</span>';
                    del += '<a href="#" class="upload-filedelete-js ml-sm" data-type="' + file.deleteType + '" data-url="' + file.deleteUrl + '">';
                    del += '<i class="icon icon-trash danger"></i>';
                    del += '</a>';
                    del += '</div>';
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
                type: 'DELETE',
                dataType: 'json',
                data: {
                    "_token": csrfToken
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

            $active = $this.attr("href");
            $('.page-section-js').each(function() {
                if($(this).attr('id') == $active)
                    $(this).removeClass('hidden');
                else
                    $(this).addClass('hidden');
            });
        });
    }

    //TODO:: set up presets JS
    function initializeRecordPresets() {
        function populate() {
            var val = $('#presetselect').val();
            $.ajax({
                url: getPresetDataUrl,
                type: 'POST',
                data: {
                    '_token': csrfToken,
                        'id': val
                }, success: function(response) {
                    putArray(response);
                }
            });
        }

        function putArray(ary) {
            var flids = ary['flids'];
            var data = ary['data'];
            var presetID = $('#presetselect').val();

            var i;
            var filename;
            for (i = 0; i < flids.length; i++) {
                var flid = flids[i];
                var field = data[flid];

                switch (field['type']) {
                    case 'Text':
                        $('[name='+flid+']').val(field['text']);
                        break;

                    case 'Rich Text':
                        CKEDITOR.instances[flid].setData(field['rawtext']);
                        break;

                    case 'Number':
                        $('[name='+flid+']').val(field['number']);
                        break;

                    case 'List':
                        $('[name='+flid+']').select2('val', field['option']);
                        break;

                    case 'Multi-Select List':
                        $('#list'+flid).select2('val', field['options']);
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
                        selector.select2('val', valArray);
                        break;

                    case 'Date':
                        var date = field['data'];

                        if(date['circa'])
                            $('[name=circa_'+flid+']').prop('checked', true);
                        $('[name=month_'+flid+']').val(date['month']);
                        $('[name=day_'+flid+']').val(date['day']);
                        $('[name=year_'+flid+']').val(date['year']);
                        $('[name=era_'+flid+']').val(date['era']);
                        break;

                    case 'Schedule':
                        var j,  events = field['events'];
                        var selector = $('#list'+flid);
                        $('#list'+flid+' option[value!="0"]').remove();

                        for (j=0; j < events.length; j++) {
                            selector.append($('<option/>', {
                                value: events[j],
                                text: events[j],
                                selected: 'selected'
                            }));
                        }
                        break;

                    case 'Geolocator':
                        var l, locations = field['locations'];
                        var selector = $('#list'+flid);
                        $('#list'+flid+' option[value!="0"]').remove();

                        for (l=0; l < locations.length; l++) {
                            selector.append($('<option/>', {
                                value: locations[l],
                                text: locations[l],
                                selected: 'selected'
                            }));
                        }
                        break;

                    case 'Combo List':
                        var p, combos = field['combolists'];
                        var selector = $('#combo_list_'+flid);

                        // Empty defaults, we need to do this as the preset may have done so.
                        // However if it hasn't, the defaults will be in the preset so this is safe.
                        selector.children("#val_"+flid).each(function(){
                            $(this).remove();
                        });

                        for(p=0; p < combos.length; p++) {
                            var rawData = combos[p];

                            var field1RawData = rawData.split('[!f1!]')[1];
                            var field2RawData = rawData.split('[!f2!]')[1];

                            var field1ToPrint = field1RawData.split('[!]');
                            var field2ToPrint = field2RawData.split('[!]');

                            var html = "";
                            html += '<div id="val_'+flid+'">';

                            if (field1ToPrint.length == 1) {
                                html += '<span style="float:left;width:40%;margin-bottom:10px">'+field1ToPrint+'</span>';
                            }
                            else {
                                html += '<span style="float:left;width:40%;margin-bottom:10px">';
                                for (var q = 0; q < field1ToPrint.length; q++) {
                                    html += '<div>'+field1ToPrint[q]+'</div>';
                                }
                                html+= '</span>';
                            }
                            if (field2ToPrint.length == 1) {
                                html += '<span style="float:left;width:40%;margin-bottom:10px">'+field2ToPrint+'</span>';
                            }
                            else {
                                html += '<span style="float:left;width:40%;margin-bottom:10px">';
                                for (var r = 0; r < field2ToPrint.length; r++) {
                                    html += '<div>'+field2ToPrint[r]+'</div>';
                                }
                                html += '</span>';
                            }
                            html += '<input name="'+flid+'_val[]" type="hidden" value="'+rawData+'" id="'+flid+'_val[]">';
                            html += '<span class="delete_combo_def_11" style="float:left;width:20%;margin-bottom:10px"><a>[X]</a></span>';
                            html += '</div>';

                            selector.append(html);
                        }
                        break;

                    // The file fields will all have the same routine, basically.
                    case 'Documents':

                        var filenames = $("#filenames" + flid);
                        filenames.empty();

                        if (!field['documents']) { /* Do nothing. */ }
                        else {
                            moveFiles(presetID, flid, userID);

                            for (var z = 0; z < field['documents'].length; z++) {
                                filename = field['documents'][z].split('[Name]')[1];
                                filenames.append(fileDivHTML(filename, flid, userID, true));
                            }
                        }
                        break;

                    case 'Gallery':

                        var filenames = $("#filenames" + flid);
                        filenames.empty();

                        if (!field['images']) { /* Do nothing. */ }
                        else {
                            moveFiles(presetID, flid, userID);

                            for (var x = 0; x < field['images'].length; x++) {
                                filename = field['images'][x].split('[Name]')[1];
                                filenames.append(fileDivHTML(filename, flid, userID, true));
                            }
                        }
                        break;

                    case 'Playlist':

                        var filenames = $("#filenames" + flid);
                        filenames.empty();

                        if (!field['audio']) { /* Do nothing. */ }
                        else {
                            moveFiles(presetID, flid, userID);

                            for (var y = 0; y < field['audio'].length; y++) {
                                filename = field['audio'][y].split('[Name]')[1];
                                filenames.append(fileDivHTML(filename, flid, userID, true));
                            }
                        }
                        break;

                    case 'Video':

                        var filenames = $("#filenames" + flid);
                        filenames.empty();

                        if (!field['video']) { /* Do nothing. */ }
                        else {
                            moveFiles(presetID, flid, userID);

                            for (var tv = 0; tv < field['video'].length; tv++) {
                                filename = field['video'][tv].split('[Name]')[1];
                                filenames.append(fileDivHTML(filename, flid, userID, true));
                            }
                        }
                        break;

                    case '3D-Model':

                        var filenames = $("#filenames" + flid);
                        filenames.empty();

                        if (!field['model']) { /* Do nothing. */ }
                        else {
                            moveFiles(presetID, flid, userID);

                            var mod = field['model'].split('[Name]')[1];
                            filenames.append(fileDivHTML(mod, flid, userID, false));
                        }
                        break;

                    case 'Associator':
                        var r, records = field['records'];
                        var selector = $('#'+flid);
                        $('#'+flid+' option[value!="0"]').remove();

                        for (r=0; r < records.length; r++) {
                            selector.append($('<option/>', {
                                value: records[r],
                                text: records[r],
                                selected: 'selected'
                            }));
                        }
                        break;

                }
            }
        }

        /**
         * Generates the HTML for an uploaded file's div.
         *
         * This is the HTML that handles moving the order of file type fields that allow for
         * multiple inputs and deleting a file input. It builds the url for the delete button and
         * encodes the URL as expected.
         *
         * @param {string} filename The filename of file's div we're generating.
         * @param {int} flid The field ID we're generating for.
         * @param {int} userID The ID of the user currently creating a file from the preset.
         *                     This is needed to build the delete button's URL.
         * @param {bool} multiple True if the field can have multiple entries, false otherwise.
         * @return {string} The formatted HTML.
         */
        function fileDivHTML(filename, flid, userID, multiple) {
            var HTML = "";
            HTML += '<div id="uploaded_file_div">' + filename + ' ';
            HTML += '<input type="hidden" name="file'+ flid +'[]" value ="'+ filename +'">';

            //
            // Build the delete file url.
            //
            var baseUrl = baseFileUrl;
            baseUrl += 'f' + flid + 'u' + userID + '/' + myUrlEncode(filename);

            //
            // If it is possible for a file field to have multiple inputs, we'll
            // print out the up and down buttons for ordering
            //
            if (multiple) {
                HTML += '<button id="up" class="btn btn-default" type="button">Up</button>';
                HTML += '<button id="down"class="btn btn-default" type="button">Down</button>';
            }

            HTML += '<button class="btn btn-danger delete" type="button" data-type="DELETE" data-url="'+
                baseUrl +'" >';
            HTML += '<i class="glyphicon glyphicon-trash" /> Delete</button>';
            HTML += '</div>';

            return HTML;
        }

        /**
         * Encodes a string for a url.
         *
         * Javascript's encode function wasn't playing nice with our system so I wrote this based off of
         * a post on the PHP.net user contributions on the urlencode() page davis dot pexioto at gmail dot com
         *
         */
        function myUrlEncode(to_encode) {
            //
            // Build array of characters that need to be replaced.
            //
            var replace = ['!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?",
                "%", "#", "[", "]"];
            //
            // Build array of the replacements for the characters listed above.
            //
            var entities = ['%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B',
                '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'];

            // Replace them in the string!
            for(var i = 0; i < entities.length; i++) {
                to_encode = to_encode.replace(replace[i], entities[i]);
            }

            return to_encode;
        }

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
            if(this.checked)
                duplicateDiv.fadeIn();
            else
                duplicateDiv.hide();
        });
    }

    function initializeNewRecordPreset() {
        //The one that matters during execution
        $('.newRecPre-check-js').click(function() {
            var newRecPreDiv = $('.newRecPre-record-js');
            if(this.checked)
                newRecPreDiv.fadeIn();
            else
                newRecPreDiv.hide();
        });
    }

    initializeSelectAddition();
    initializeSpecialInputs();
    initializeScheduleOptions();
    intializeGeolocatorOptions();
    intializeFileUploaderOptions();
    initializePageNavigation();
    initializeDuplicateRecord();
    initializeNewRecordPreset();
}