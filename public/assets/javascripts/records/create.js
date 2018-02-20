var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Create = function() {

    $('.single-select').chosen({
        width: '100%',
    });

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

    initializePageNavigation();
    initializeDuplicateRecord();
    initializeNewRecordPreset();
}