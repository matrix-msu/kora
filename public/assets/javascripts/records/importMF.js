var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.ImportMF = function () {

    var failedRecords = [];

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

            var recordFileLink = $('.recordfile-link');
            var recordFileSection = $('.recordfile-section');
            var recordMatchLink = $('.recordmatch-link');
            var recordMatchSection = $('.recordmatch-section');
            var recordResultsSection = $('.recordresults-section');

            fd = new FormData();
            fd.append('_token', CSRFToken);
            if(zipInput.val() != '')
                fd.append("files", zipInput[0].files[0]);
            formOrder = [];
            formsThemselves = [];
            $(".search-choice-close").each(function() {
                $thisIndex = $(this).attr('data-option-array-index');
                formOrder.push($thisIndex);
                formsThemselves.push(msInput.val()[$thisIndex]);
            });
            fd.append('importForms', JSON.stringify(formsThemselves));
            fd.append('formOrder', JSON.stringify(formOrder));

            // from https://stackoverflow.com/a/3730579
            // this normalizes the order array to be readable on the backend
            function sortWithIndeces(toSort) {
              for (var i = 0; i < toSort.length; i++) {
                toSort[i] = [toSort[i], i];
              }
              toSort.sort(function(left, right) {
                return left[0] < right[0] ? -1 : 1;
              });
              toSort.sortIndices = [];
              for (var j = 0; j < toSort.length; j++) {
                toSort.sortIndices.push(toSort[j][1]);
                toSort[j] = toSort[j][0];
              }
              return toSort.sortIndices;
            }

            fd.append('formOrder', JSON.stringify(sortWithIndeces(formOrder)));

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
                    recordFileLink.removeClass('active');
                    recordMatchLink.addClass('active');
                    recordMatchLink.addClass('underline-middle');

                    recordFileSection.addClass('hidden');
                    recordMatchSection.removeClass('hidden');

                    //Build the Labels first
                    var matchup = `
                        <div class="form-group mt-xl half">
                            <label>Form Field Names</label>
                        </div>
                        <div class="form-group mt-xl half">
                            <label>Select Uploaded Field to Match</label>
                        </div>
                        <div class="form-group"></div>
                    `;

                    // Fill the body
                    for(var fid in data) {
                        matchup += data[fid]['matchup'];
                    }

                    //Finish off the table
                    matchup += `
                        <div class="form-group mt-xxxl">
                            <input type="button" class="btn final-import-btn-js" value="Upload Records">
                        </div>
                    `;

                    recordMatchSection.html(matchup);

                    $('.single-select').chosen({
                        allow_single_deselect: true,
                        width: '100%',
                    });

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

                        $('.get-fid-js').each(function () {
                            let fid = $(this).attr('fid');

                            table[fid] = {};
                            tags = [];
                            slugs = [];

                            $(this).find('.get-tag-js').each(function () {
                                tags.push($(this).val());
                            });
                            $(this).find('.get-slug-js').each(function () {
                                slugs.push($(this).attr('slug'));
                            });
                            for (j = 0; j < slugs.length; j++) {
                                table[fid][tags[j]] = slugs[j];
                            }
                        });

                        //build for potential connections
                        var assocTagConvert = {};
                        var crossFormAssoc = {};
                        var kids = {};
                        var fids = [];
                        var connections = {};

                        for(var fid in data) {
                            // skip loop if the property is from prototype
                            if (!data.hasOwnProperty(fid)) continue;

                            var importRecs = data[fid]['records'];
                            var importType = data[fid]['type'];

                            // cross assoc requires fid
                            assocTagConvert[fid] = {};
                            crossFormAssoc[fid] = {};
                            kids[fid] = [];
                            connections[fid] = {};

                            fids.push(fid);

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
                                        "table": table,
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
                                        if(data['assocTag']!=null)
                                            assocTagConvert[fid][data['assocTag']] = data['kid'];
                                        crossFormAssoc[fid][data['kid']] = data['assocArray'];
                                        kids[fid].push(data['kid']);
                                        if (data['kidConnection'].length != 0) connections[fid][data['kidConnection']] = data['kid'];

                                        if(done == total)
                                            $.ajax({
                                                url: crossAssocURL,
                                                type: 'POST',
                                                data: {
                                                    "_token": CSRFToken,
                                                    "assocTagConvert": assocTagConvert,
                                                    "crossFormAssoc": crossFormAssoc,
                                                    "connections": connections,
                                                    "kids": kids,
                                                    "fids": fids
                                                }
                                            });
                                            finishImport(succ, total, importType);
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
                                            finishImport(succ, total, importType);
                                    }
                                });
                            }
                        }
                    });
                }
            });
        });

        function finishImport(succ, total, importType) {
            $('.progress-text-js').html('Connecting cross-Form associations. One moment...');

            var recImpLabel = $('.records-imported-label-js');
            var recImpText = $('.records-imported-text-js');
            var recImpText2 = $('.records-imported-text2-js');
            var btnContainer = $('.button-container-js');
            var btnContainer2 = $('.button-container2-js');

            $('.progress-text-js').html(succ + ' of ' + total + ' records successfully imported!');
            if(succ==total) {
                recImpText.text('Way to have your data organized! We found zero errors with this import. Woohoo!');
            } else {
                recImpText.html('Looks like not all of the records made it. You can download the failed records and ' +
                    'their report below to identify the problem with their import.');
                btnContainer.html('<a href="#" class="btn half-sub-btn import-thick-btn-text failed-records-js">Download Failed Records (' + importType + ')</a>'
                    + '<form action="' + downloadFailedUrl + '" method="post" class="records-form-js" style="display:none;">'
                    + '<input type="hidden" name="type" value="' + importType + '"/>'
                    + '<input type="hidden" name="_token" value="' + CSRFToken + '"/>'
                    + '</form>'
                    + '<a class="btn half-sub-btn import-thick-btn-text failed-reasons-js" href="#">Download Failed Records Report</a>'
                    + '<form action="' + downloadReasonsUrl + '" method="post" class="reasons-form-js" style="display:none;">'
                    + '<input type="hidden" name="_token" value="' + CSRFToken + '"/>'
                    + '</form>');

                recImpText2.text('You may also try importing again at anytime, or view the records that successfully imported.');
            }
        }

        $('.button-container-js').on('click', '.failed-records-js', function (e) {
            e.preventDefault();

            var $recForm = $('.records-form-js');

            var input = $("<input>")
                .attr("type", "hidden")
                .attr("name", "failures").val(JSON.stringify(failedRecords));
            $recForm.append($(input));

            $recForm.submit();
        });

        $('.button-container-js').on('click', '.failed-reasons-js', function (e) {
            e.preventDefault();
            var $recForm = $('.reasons-form-js');

            var input = $("<input>")
                .attr("type", "hidden")
                .attr("name", "failures").val(JSON.stringify(failedRecords));
            $recForm.append($(input));

            $recForm.submit();
        });

        $('.button-container2-js').on('click', '.refresh-records-js', function (e) {
            e.preventDefault();
            location.reload();
        });
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

    // For field functionatily
    var fileInput = $(".file-input");

    var fileButton = $(".file-label");

    var fileFilename = $(".file-filename");

    var fileInstruction = $(".file-instruction");

    var fileDroppedFile = false;

    //Resets file input
    function resetFileInput(type) {
        switch (type) {
            case "file":
                fileInput.replaceWith(fileInput.val('').clone(true));
                fileFilename.html("Drag & Drop or Select the Kora 2 Record Files Zip Below");
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
        fileButton.keydown(function (event) {
            if (event.keyCode == 13 || event.keyCode == 32)
                fileInput.focus();
        });

        // Clicking input opens menu
        fileButton.click(function (event) {
            fileInput.focus();
        });

        // For clicking on input to select an image
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
            fileButton.addClass('has-advanced-upload');

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
    initializeFileUpload();
    initializeImportRecords();
    intializeFileUploaderOptions();

}
