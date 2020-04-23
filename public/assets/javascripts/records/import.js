var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Import = function () {
    var droppedRecord
    var droppedFile

    var failedConnections = [];

    function initializeFormProgression() {
        $('.record-input-js').change(function () {
            $('.spacer-fade-js').fadeIn(1000);
            $('.record-import-section-2').removeClass('hidden');
        });
    }

    function initializeImportRecords() {
        $('.upload-record-btn-js').click(function (e) {
            e.preventDefault();

            $(this).addClass('disabled');

            var recordInput = $('.record-input-js');
            var zipInput = $('.file-input-js');

            var recordFileLink = $('.recordfile-link');
            var recordFileSection = $('.recordfile-section');
            var recordMatchLink = $('.recordmatch-link');
            var recordMatchSection = $('.recordmatch-section');
            var recordResultsSection = $('.recordresults-section');

            if (recordInput.val() != '' || droppedRecord || droppedFile) {

                fd = new FormData(); // Data from file select elements is not serialized https://api.jquery.com/serializeArray/ // so we need to use FormData

                if (droppedRecord) {
                    fd.append("records", droppedRecord);
                    name = droppedRecord.name;
                } else {
                    fd.append("records", recordInput[0].files[0]);
                    var name = recordInput.val();
                }

                fd.append('type', name.replace(/^.*\./, ''));

                if (droppedFile) { // zip file upload
                    fd.append("files", droppedFile);
                } else if (zipInput.val() != '') {
                    fd.append("files", zipInput[0].files[0]);
                }

                fd.append("fid", fidForFormData);
                fd.append('_token', CSRFToken);

                $.ajax({
                    url: matchUpFieldsUrl,
                    type: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        recordFileLink.removeClass('active');
                        recordMatchLink.addClass('active');
                        recordMatchLink.addClass('underline-middle');

                        recordFileSection.addClass('hidden');
                        recordMatchSection.removeClass('hidden');

                        recordMatchSection.html(data['matchup']);

                        $('.single-select').chosen({
                            allow_single_deselect: true,
                            width: '100%',
                        });

                        //Get the records
                        var importRecs = data['records'];
                        var importType = data['type'];

                        //initialize counter
                        done = 0;
                        succ = 0;
                        total = Object.keys(importRecs).length;
                        var progressText = $('.progress-text-js');
                        var progressFill = $('.progress-fill-js');
                        progressText.text(succ + ' of ' + total + ' Records Submitted');

                        //Click to start actually importing records
                        recordMatchSection.on('click', '.final-import-btn-js', function () {
                            //Remove the links and change header info
                            $('.sections-remove-js').remove();
                            $('.header-text-js').text('Importing Records');
                            $('.desc-text-js').text(
                                'The import has started, depending on the number of records, it may take several ' +
                                'minutes to complete. Do not leave this page or close your browser until completion. ' +
                                'When the import is complete, you can see a summary of all the data that was saved. '
                            );

                            recordMatchSection.addClass('hidden');
                            recordResultsSection.removeClass('hidden');

                            //initialize matchup
                            table = {};
                            tags = [];
                            slugs = [];
                            $('.get-tag-js').each(function () {
                                tags.push($(this).val());
                            });
                            $('.get-slug-js').each(function () {
                                slugs.push($(this).attr('slug'));
                            });
                            for (j = 0; j < slugs.length; j++) {
                                table[tags[j]] = slugs[j];
                            }

                            //build for potential connections
                            var kids = [];
                            var connections = {};

                            //Initialize throttler to prevent
                            var throttle = throttledQueue(150, 5000);

                            //foreach record in the dataset
                            for (var import_id in importRecs) {
                                throttle({ "import_id": import_id, "type": importType, "record": importRecs[import_id], "table": table }, function(importData) {
                                    //ajax to store record
                                    $.ajax({
                                        url: importRecordUrl,
                                        type: 'POST',
                                        data: {
                                            "_token": CSRFToken,
                                            "record": JSON.stringify(importData["record"]),
                                            "import_id": importData["import_id"],
                                            "table": JSON.stringify(importData["table"]),
                                            "type": importData["type"]
                                        },
                                        importData: importData,
                                        success: function (data) {
                                            //building connections
                                            kids.push(data['kid']);
                                            if (data['kidConnection'].length != 0) connections[data['kidConnection']] = data['kid'];

                                            succ++;
                                            progressText.text(succ + ' of ' + total + ' Records Submitted');

                                            done++;
                                            //update progress bar
                                            percent = (done / total) * 100;
                                            if (percent < 7)
                                                percent = 7;
                                            progressFill.attr('style', 'width:' + percent + '%');
                                            progressText.text(succ + ' of ' + total + ' Records Submitted');

                                            if (done == total) {
                                                $('.progress-text-js').html('Connecting cross-Form associations. One moment...');
                                                if (connections && kids) {
                                                    $.ajax({
                                                        url: connectRecordsUrl,
                                                        type: 'POST',
                                                        data: {
                                                            "_token": CSRFToken,
                                                            "connections": JSON.stringify(connections),
                                                            "kids": JSON.stringify(kids)
                                                        }, success: function (data) {
                                                            failedConnections = JSON.parse(data);
                                                            completeImport(succ, total, importData["type"]);
                                                        }
                                                    });
                                                } else
                                                    completeImport(succ, total, importData["type"]);
                                            }
                                        },
                                        error: function (data) {
                                            $.ajax({
                                                url: saveFailedUrl,
                                                type: 'POST',
                                                data: {
                                                    "_token": CSRFToken,
                                                    "failure": JSON.stringify([importData["import_id"], importData["record"], data]),
                                                    "type": importData["type"]
                                                }, success: function (data) {
                                                    //
                                                }
                                            });

                                            done++;
                                            //update progress bar
                                            percent = (done / total) * 100;
                                            if (percent < 7)
                                                percent = 7;
                                            progressFill.attr('style', 'width:' + percent + '%');
                                            progressText.text(succ + ' of ' + total + ' Records Submitted');

                                            if (done == total) {
                                                $('.progress-text-js').html('Connecting cross-Form associations. One moment...');
                                                if (connections && kids) {
                                                    $.ajax({
                                                        url: connectRecordsUrl,
                                                        type: 'POST',
                                                        data: {
                                                            "_token": CSRFToken,
                                                            "connections": JSON.stringify(connections),
                                                            "kids": JSON.stringify(kids)
                                                        }, success: function (data) {
                                                            failedConnections = JSON.parse(data);
                                                            completeImport(succ, total, importData["type"]);
                                                        }
                                                    });
                                                } else
                                                    completeImport(succ, total, importData["type"]);
                                            }
                                        }
                                    });
                                });
                            }
                        });
                    }
                });
            }
        });

        function completeImport(succ, total, impType) {
            var recImpLabel = $('.records-imported-label-js');
            var recImpText = $('.records-imported-text-js');
            var recImpText2 = $('.records-imported-text2-js');
            var recImpText3 = $('.records-imported-text3-js');
            var btnContainer = $('.button-container-js');
            var btnContainer2 = $('.button-container2-js');
            var btnContainer3 = $('.button-container3-js');

            $('.recordresults-section').addClass('hidden');
            $('.allrecords-section').removeClass('hidden');

            $('.header-text-js').text('Import Records Complete!');
            $('.desc-text-js').text('Below is a summary of the imported records.');

            if(succ==total) {
                recImpLabel.text(succ + ' of ' + total + ' Records Successfully Imported!');
                recImpText.text('Way to have your data organized! We found zero errors with this import. Woohoo!');

                btnContainer.html('<a href="' + viewRecordsUrl + '" class="btn half-btn import-thin-btn-text">View Imported Records</a>');
            } else {
                recImpLabel.text(succ + ' of ' + total + ' Records Successfully Imported');
                recImpText.html('Looks like not all of the records made it. You can download the failed records and ' +
                    'their report below to identify the problem with their import.');

                btnContainer.html('<a href="#" class="btn half-sub-btn import-thick-btn-text failed-records-js">Download Failed Records (' + impType + ')</a>'
                    + '<form action="' + downloadFailedUrl + '" method="post" class="records-form-js" style="display:none;">'
                    + '<input type="hidden" name="type" value="' + impType + '"/>'
                    + '<input type="hidden" name="_token" value="' + CSRFToken + '"/>'
                    + '</form>'
                    + '<a class="btn half-sub-btn import-thick-btn-text failed-reasons-js" href="#">Download Failed Records Report</a>'
                    + '<form action="' + downloadReasonsUrl + '" method="post" class="reasons-form-js" style="display:none;">'
                    + '<input type="hidden" name="_token" value="' + CSRFToken + '"/>'
                    + '</form>');


                recImpText2.text('You may also try importing again at anytime, or view the records that successfully imported.');

                btnContainer2.html('<a class="btn half-sub-btn import-thick-btn-text refresh-records-js" href="#">Try Importing Again</a>' +
                    '<a href="' + viewRecordsUrl + '" class="btn half-btn import-thin-btn-text">View Imported Records</a>');
            }

            if(failedConnections.length > 0) {
                recImpText3.text('Looks like some records failed to find their associations. Download the report below.');

                btnContainer3.html('<a class="btn half-sub-btn import-thick-btn-text failed-connection-js" href="#">Download Failed Connections Report</a>'
                    + '<form action="' + downloadConnectionUrl + '" method="post" class="connection-form-js" style="display:none;">'
                    + '<input type="hidden" name="_token" value="' + CSRFToken + '"/>'
                    + '</form>');
            }
        }

        $('.button-container-js').on('click', '.failed-records-js', function (e) {
            e.preventDefault();

            var $recForm = $('.records-form-js');
            $recForm.submit();
        });

        $('.button-container-js').on('click', '.failed-reasons-js', function (e) {
            e.preventDefault();

            var $recForm = $('.reasons-form-js');
            $recForm.submit();
        });

        $('.button-container2-js').on('click', '.refresh-records-js', function (e) {
            e.preventDefault();
            location.reload();
        });

        $('.button-container3-js').on('click', '.failed-connection-js', function (e) {
            e.preventDefault();
            var $recForm = $('.connection-form-js');

            var input = $("<input>")
                .attr("type", "hidden")
                .attr("name", "failures").val(JSON.stringify(failedConnections));
            $recForm.append($(input));

            $recForm.submit();
        });
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
                recordFilename.html("Drag & Drop or Select the XML / JSON / CSV File Below");
                recordInstruction.removeClass("photo-selected");
                recordDroppedFile = false;
                break;
            case "file":
                fileInput.replaceWith(fileInput.val('').clone(true));
                fileFilename.html("Drag & Drop or Select the Zipped File Below ");
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

    //We're basically replicating what profile pic does, just for 3 file inputs on a single page
    function initializeFileUpload() {
        // When hovering over input, hitting enter or space opens the menu
        recordButton.keydown(function (event) {
            if (event.keyCode == 13 || event.keyCode == 32)
                recordInput.focus();
        });
        fileButton.keydown(function (event) {
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

    initializeFormProgression();
    initializeFileUpload();
    initializeImportRecords();
}
