var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.ImportMF = function () {

    var failedRecords = [];
    var assocTagConvert = {};
    var crossFormAssoc = {};

    function initializeSelects() {
        $('.multi-select').chosen({
            width: '100%',
        });
    }

    function initializeImportRecords() {
        $('.upload-record-btn-js').click(function (e) {
            e.preventDefault();

            $(this).addClass('disabled');

            var zipInput = $('.file-input-js');
            var msInput = $('.import-form-js');

            fd = new FormData();
            fd.append('_token', CSRFToken);
            if(zipInput.val() != '')
                fd.append("files", zipInput[0].files[0]);

            fd.append('importForms', JSON.stringify(msInput.val()));
            formOrder = [];
            $(".search-choice-close").each(function() {
                val = $(this).attr('data-option-array-index');
                formOrder.push(val);
            });
            fd.append('formOrder', JSON.stringify(formOrder));

            recordsArray = [];
            typesArray = [];
            $(".record-input-js").each(function() {
                val = $(this).val();
                type = val.replace(/^.*\./, '');
                recordsArray.push(val);
                typesArray.push(type);
            });
            fd.append('records', JSON.stringify(recordsArray));
            fd.append('types', JSON.stringify(typesArray));

            $.ajax({
                url: mfrInputURL,
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('.recordfile-section').addClass('hidden');
                    $('.recordresults-section').removeClass('hidden');

                    //initialize counter
                    done = 0;
                    succ = 0;
                    failed = [];
                    total = 0;
                    for(var fid in data) {
                        total += data[fid]['records'].length;
                    }
                    var progressText = $('.progress-text-js');
                    var progressFill = $('.progress-fill-js');
                    progressText.text(succ + ' of ' + total + ' Records Submitted');

                    $('.header-text-js').text('Importing Records');
                    $('.desc-text-js').text(
                        'The import has started, depending on the number of records, it may take several ' +
                        'minutes to complete. Do not leave this page or close your browser until completion. ' +
                        'When the import is complete, you can see a summary of all the data that was saved. '
                    );

                    for(var fid in data) {
                        // skip loop if the property is from prototype
                        if (!data.hasOwnProperty(fid)) continue;

                        var importRecs = data[fid]['records'];
                        var importType = data[fid]['type'];

                        //foreach record in the dataset
                        for (var kid in importRecs) {
                            // skip loop if the property is from prototype
                            if (!importRecs.hasOwnProperty(kid)) continue;

                            //ajax to store record
                            $.ajax({
                                url: importRecordUrl,
                                type: 'POST',
                                data: {
                                    "_token": CSRFToken,
                                    "fid": fid,
                                    "record": importRecs[kid],
                                    "kid": kid,
                                    "type": importType
                                },
                                local_kid: kid,
                                success: function (data) {
                                    succ++;
                                    progressText.text(succ + ' of ' + total + ' Records Submitted');

                                    done++;
                                    //update progress bar
                                    percent = (done / total) * 100;
                                    if(percent < 7)
                                        percent = 7;
                                    progressFill.attr('style', 'width:' + percent + '%');
                                    progressText.text(succ + ' of ' + total + ' Records Submitted');

                                    assocTagConvert[data['assocTag']] = data['kid'];
                                    crossFormAssoc[data['kid']] = data['assocArray'];

                                    if(done == total)
                                        finishImport(succ, total);
                                },
                                error: function (data) {
                                    failedRecords.push([this.local_kid, importRecs[this.local_kid], data]);

                                    done++;
                                    //update progress bar
                                    percent = (done / total) * 100;
                                    if (percent < 7)
                                        percent = 7;
                                    progressFill.attr('style', 'width:' + percent + '%');
                                    progressText.text(succ + ' of ' + total + ' Records Submitted');

                                    if(done == total)
                                        finishImport(succ, total);
                                }
                            });
                        }
                    }
                }
            });
        });

        function finishImport(succ, total) {
            $('.progress-text-js').html('Connecting cross-Form associations. One moment...');

            //cross form associations
            $.ajax({
                url: crossAssocURL,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "assocTagConvert": JSON.stringify(assocTagConvert),
                    "crossFormAssoc": JSON.stringify(crossFormAssoc)
                },
                success: function (data) {
                    $('.progress-text-js').html(succ + ' of ' + total + ' records successfully imported!');
                        //We might add this stuff back in later
                        // + ' Click <a class="success-link" href="' + showRecordUrl + '">here to visit the records page</a>.'
                        // + ' Or click <a class="success-link failed-records-js" href="#">here to download any records</a>'
                        // + '<form action="' + downloadFailedUrl + '" method="post" class="records-form-js" style="display:none;">'
                        // + '<input type="hidden" name="type" value="' + importType + '"/>'
                        // + '<input type="hidden" name="_token" value="' + CSRFToken + '"/>'
                        // + '</form>'
                        // + ' that failed to upload, and click <a class="success-link failed-reasons-js" href="#">here to download a report</a>'
                        // + '<form action="' + downloadReasonsUrl + '" method="post" class="reasons-form-js" style="display:none;">'
                        // + '<input type="hidden" name="_token" value="' + CSRFToken + '"/>'
                        // + '</form>'
                        // + ' of why they failed.');
                }
            });
        }
    }

    function intializeFileUploaderOptions() {

        $('.kora-file-button-js').click(function(e){
            e.preventDefault();

            fileUploader = $(this).next().trigger('click');
        });

        $('.kora-file-upload-js').fileupload({
            dataType: 'json',
            singleFileUploads: false,
            done: function (e, data) {
                inputName = 'file0';
                fileDiv = ".filenames-js";

                var $errorDiv = $('.error-message');
                $errorDiv.text('');
                $.each(data.result[inputName], function (index, file) {
                    if(file.error == "" || !file.hasOwnProperty('error')) {
                        var del = '<div class="form-group mt-xxs uploaded-file">';
                        del += '<input type="hidden" class="record-input-js" name="' + inputName + '[]" value ="' + file.name + '">';
                        del += '<a href="#" class="upload-fileup-js">';
                        del += '<i class="icon icon-arrow-up"></i></a>';
                        del += '<a href="#" class="upload-filedown-js">';
                        del += '<i class="icon icon-arrow-down"></i></a>';
                        del += '<span class="ml-sm">' + file.name + '</span>';
                        del += '<a href="#" class="upload-filedelete-js ml-sm" data-url="' + deleteFileUrl + encodeURI(file.name) + '">';
                        del += '<i class="icon icon-trash danger"></i></a></div>';
                        $(fileDiv).append(del);
                    } else {
                        $errorDiv.text(file.error);
                        return false;
                    }
                });

                //Reset progress bar
                var progressBar = '.progress-bar-js';
                $(progressBar).css(
                    {"width": 0, "height": 0, "margin-top": 0}
                );
            },
            progressall: function (e, data) {
                var progressBar = '.progress-bar-js';
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
                    "_token": CSRFToken,
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

    initializeSelects();
    initializeImportRecords();
    intializeFileUploaderOptions();

}