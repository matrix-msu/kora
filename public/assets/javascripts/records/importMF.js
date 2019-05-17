var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.ImportMF = function () {
    var failedRecords = [];
    var assocTagConvert = {};
    var crossFormAssoc = {};
    var comboCrossAssoc = {};
    var droppedRecord;
    var droppedFile;

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
            $(".kora-file-upload-js").each(function() {
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
                    console.log(data)
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
                                    console.log(data)
                                    succ++;
                                    progressText.text(succ + ' of ' + total + ' Records Submitted');

                                    done++;
                                    //update progress bar
                                    percent = (done / total) * 100;
                                    if(percent < 7)
                                        percent = 7;
                                    progressFill.attr('style', 'width:' + percent + '%');
                                    progressText.text(succ + ' of ' + total + ' Records Submitted');
                                    if(data['assocTag']!=null)
                                        assocTagConvert[data['assocTag']] = data['kid'];
                                    crossFormAssoc[data['kid']] = data['assocArray'];
                                    comboCrossAssoc[data['kid']] = data['comboAssocArray'];

                                    if(done == total)
                                        finishImport(succ, total);
                                },
                                error: function (data) {
                                    console.log(data)
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
                }, error: function (error) {
                    console.log(error);
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
                    "crossFormAssoc": JSON.stringify(crossFormAssoc),
                    "comboCrossAssoc": JSON.stringify(comboCrossAssoc)
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

    // For field functionatily
    var recordInput = $(".record-input");
    var fileInput = $(".file-input");

    var recordButton = $(".record-label");
    var fileButton = $(".file-label");

    var recordFilename = $(".record-filename");
    var fileFilename = $(".file-filename");

    var recordInstruction = $(".record-instruction");
    var fileInstruction = $(".file-instruction");

    var recordDroppedFile = false;
    var fileDroppedFile = false;

    //Resets file input
    function resetFileInput(type) {
        switch (type) {
            case "record":
                recordInput.replaceWith(recordInput.val('').clone(true));
                recordFilename.html("Drag & Drop the XML / JSON / CSV Files Here");
                recordInstruction.removeClass("photo-selected");
                recordDroppedFile = false;
                break;
            case "file":
                fileInput.replaceWith(fileInput.val('').clone(true));
                fileFilename.html("Drag & Drop the XML / JSON / CSV Files Here");
                fileInstruction.removeClass("photo-selected");
                fileDroppedFile = false;
                break;
            default:
                break;
        }
    }

    //Simulating just for fun
    function newProfilePic(type, pic, name) {
        switch (type) {
            case "record":
                recordFilename.html(name + "<span class='remove-record remove ml-xs'><i class='icon icon-cancel'></i></span>");
                recordInstruction.addClass("photo-selected");
                recordDroppedFile = pic;
                $(".remove-record").click(function (event) {
                    event.preventDefault();
                    resetFileInput(type);
                });
                break;
            case "file":
                fileFilename.html(name + "<span class='remove-file remove ml-xs'><i class='icon icon-cancel'></i></span>");
                fileInstruction.addClass("photo-selected");
                fileDroppedFile = pic;
                $(".remove-file").click(function (event) {
                    event.preventDefault();
                    resetFileInput(type);
                });
                break;
            default:
                break;
        }
    }

    // Check for Drag and Drop Support on the browser
    var isAdvancedUpload = function () {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();

    function initializeFileUpload() {
        console.log('hmm')
        // When hovering over input, hitting enter or space opens the menu
        recordButton.keydown(function (event) {
            console.log('woasdjkfajsdkf');
            if (event.keyCode == 13 || event.keyCode == 32)
                recordInput.focus();
        });
        fileButton.keydown(function (event) {
            console.log('woasdjkfajsdkf');
            if (event.keyCode == 13 || event.keyCode == 32)
                fileInput.focus();
        });

        // Clicking input opens menu
        recordButton.click(function (event) {
            recordInput.focus();
        });
        fileButton.click(function (event) {
            fileInput.focus();
        });

        // For clicking on input to select an image
        recordInput.change(function (event) {
            event.preventDefault();

            if (this.files && this.files[0]) {
                var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
                var reader = new FileReader();
                reader.onload = function (e) {
                    newProfilePic("record", e.target.result, name);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
        fileInput.change(function (event) {
            event.preventDefault();

            if (this.files && this.files[0]) {
                var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
                var reader = new FileReader();
                reader.onload = function (e) {
                    newProfilePic("file", e.target.result, name);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Drag and Drop
        // detect and disable if we are on Safari
        if (isAdvancedUpload && window.safari == undefined && navigator.vendor != 'Apple Computer, Inc.') {
            recordButton.addClass('has-advanced-upload');
            fileButton.addClass('has-advanced-upload');

            recordButton.on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
            })
                .on('dragover dragenter', function () {
                    recordButton.addClass('is-dragover');
                })
                .on('dragleave dragend drop', function () {
                    recordButton.removeClass('is-dragover');
                })
                .on('drop', function (e) {
                    e.stopPropagation();
                    e.preventDefault();

                    recordDroppedFile = e.originalEvent.dataTransfer.files[0];
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        newProfilePic('record', e.target.result, recordDroppedFile.name);
                        recordDroppedFile = e.target.result;
                    };
                    reader.readAsDataURL(recordDroppedFile);
                    droppedRecord = recordDroppedFile;

                    $('.record-input-js').trigger('change');
                });
            fileButton.on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
            })
                .on('dragover dragenter', function () {
                    fileButton.addClass('is-dragover');
                })
                .on('dragleave dragend drop', function () {
                    fileButton.removeClass('is-dragover');
                })
                .on('drop', function (e) {
                    e.stopPropagation();
                    e.preventDefault();

                    fileDroppedFile = e.originalEvent.dataTransfer.files[0];
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        newProfilePic('file', e.target.result, fileDroppedFile.name);
                        fileDroppedFile = e.target.result;
                    };
                    reader.readAsDataURL(fileDroppedFile);
                    droppedFile = fileDroppedFile;

                    $('.record-input-js').trigger('change');
                });
        }

    }

    initializeSelects();
    initializeImportRecords();
    initializeFileUpload();

}
