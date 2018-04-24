var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Import = function() {

    function initializeFormProgression() {
        $('.record-input-js').change(function () {
            $('.record-import-section-2').removeClass('hidden');
        });
    }

    function initializeImportRecords() { //TODO::Refactor
        $('.upload-record-btn-js').click(function(e) {
            e.preventDefault();

            $(this).addClass('disabled');

            var recordInput = $('.record-input-js');
            var zipInput = $('.file-input-js');

            var recordFileLink = $('.recordfile-link');
            var recordFileSection = $('.recordfile-section');
            var recordMatchLink = $('.recordmatch-link');
            var recordMatchSection = $('.recordmatch-section');
            var recordResultsSection = $('.recordresults-section');

            if(recordInput.val() != '') {
                fd = new FormData();
                fd.append("records",recordInput[0].files[0]);
                var name = recordInput.val();
                fd.append('type',name.replace(/^.*\./, ''));
                if (zipInput.val() != '') {
                    fd.append("files", zipInput[0].files[0]);
                }
                fd.append("fid",fidForFormData);
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
                            width: '100%',
                        });

                        //Get the records
                        var importRecs = data['records'];
                        var importType = data['type'];

                        //initialize counter
                        done = 0;
                        succ = 0;
                        failed = [];
                        total = Object.keys(importRecs).length;
                        var progressText = $('.progress-text-js');
                        var progressFill = $('.progress-fill-js');
                        progressText.text(succ+' of '+total+' Records Submitted');

                        //Click to start actually importing records
                        recordMatchSection.on('click', '.final-import-btn-js', function() {
                            recordMatchLink.removeClass('active');
                            recordMatchLink.removeClass('underline-middle');
                            recordMatchSection.addClass('hidden');

                            recordResultsSection.removeClass('hidden');

                            //initialize matchup
                            tags = [];
                            slugs = [];
                            table = {};
                            $('.get-tag-js').each(function(){
                                tags.push($(this).val());
                            });
                            $('.get-slug-js').each(function(){
                                slugs.push($(this).attr('slug'));
                            });
                            for(j=0; j<slugs.length; j++){
                                table[tags[j]] = slugs[j];
                            }

                            //foreach record in the dataset
                            for(var kid in importRecs) {
                                // skip loop if the property is from prototype
                                if (!importRecs.hasOwnProperty(kid)) continue;

                                //ajax to store record
                                $.ajax({
                                    url: importRecordUrl,
                                    type: 'POST',
                                    data: {
                                        "_token": CSRFToken,
                                        "record": importRecs[kid],
                                        "kid": kid,
                                        "table": table,
                                        "type": importType
                                    },
                                    success: function(data){
                                        console.log(data);
                                        //if success
                                        if(data=='') {
                                            succ++;
                                            progressText.text(succ+' of '+total+' Records Submitted');
                                        } else {
                                            //TODO::THIS WHOLE ERROR
                                            //list error message
                                            $('#error_div').html(data);
                                            //add obj to failed
                                            //failed.push(data['records'][i]);
                                        }
                                        done++;
                                        //update progress bar
                                        percent = (done/total)*100;
                                        if(percent<7)
                                            percent = 7;
                                        progressFill.attr('style','width:'+percent+'%');
                                        progressText.text(done+' of '+total+' Records Submitted');

                                        //if done = total
                                        if(done==total) {
                                            //Display links for downloading bad xml //TODO:: build link for failed records
                                            //Display link to Go to Records Page
                                            progressText.html('Records successfully imported! Click ' +
                                                '<a class="success-link" href="'+showRecordUrl+'">here to visit the ' +
                                                'records page</a>. Or click <a class="success-link" href="#">here to ' +
                                                'download any records</a> that failed to upload.');
                                            //console.log(failed);
                                        }
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    }

    // For fiel functionatily
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
        switch(type) {
            case "record":
                recordInput.replaceWith(recordInput.val('').clone(true));
                recordFilename.html("Drag & Drop or Select the Kora 2 Record XML Below");
                recordInstruction.removeClass("photo-selected");
                recordDroppedFile = false;
                break;
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

    //SImulating just for fun
    function newProfilePic(type, pic, name) {
        switch(type) {
            case "record":
                recordFilename.html(name + "<span class='remove-record remove ml-xs'><i class='icon icon-cancel'></i></span>");
                recordInstruction.addClass("photo-selected");
                recordDroppedFile = pic;
                $(".remove-record").click(function(event) {
                    event.preventDefault();
                    resetFileInput(type);
                });
                break;
            case "file":
                fileFilename.html(name + "<span class='remove-file remove ml-xs'><i class='icon icon-cancel'></i></span>");
                fileInstruction.addClass("photo-selected");
                fileDroppedFile = pic;
                $(".remove-file").click(function(event) {
                    event.preventDefault();
                    resetFileInput(type);
                });
                break;
            default:
                break;
        }
    }

    // Check for Drag and Drop Support on the browser //TODO::fix drag and drop....
    // var isAdvancedUpload = function() {
    //     var div = document.createElement('div');
    //     return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    // }();

    //We're basically replicating what profile pic does, just for 3 file inputs on a single page
    function initializeFileUpload() {
        // When hovering over input, hitting enter or space opens the menu
        recordButton.keydown(function(event) {
            if( event.keyCode == 13 || event.keyCode == 32 )
                recordInput.focus();
        });
        fileButton.keydown(function(event) {
            if( event.keyCode == 13 || event.keyCode == 32 )
                fileInput.focus();
        });

        // Clicking input opens menu
        recordButton.click(function(event) { recordInput.focus(); });
        fileButton.click(function(event) { fileInput.focus(); });

        // For clicking on input to select an image
        recordInput.change(function(event) {
            event.preventDefault();

            if (this.files && this.files[0]) {
                var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
                var reader = new FileReader();
                reader.onload = function (e) { newProfilePic("record",e.target.result, name); };
                reader.readAsDataURL(this.files[0]);
            }
        });
        fileInput.change(function(event) {
            event.preventDefault();

            if (this.files && this.files[0]) {
                var name = this.value.substring(this.value.lastIndexOf('\\') + 1);
                var reader = new FileReader();
                reader.onload = function (e) { newProfilePic("file",e.target.result, name); };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Drag and Drop
        // if (isAdvancedUpload) {
        //     recordButton.addClass('has-advanced-upload');
        //     fileButton.addClass('has-advanced-upload');
        //
        //     recordButton.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) { e.preventDefault(); e.stopPropagation(); })
        //         .on('dragover dragenter', function() { recordButton.addClass('is-dragover'); })
        //         .on('dragleave dragend drop', function() { recordButton.removeClass('is-dragover'); })
        //         .on('drop', function(e) {
        //             e.stopPropagation();
        //             e.preventDefault();
        //
        //             recordDroppedFile = e.originalEvent.dataTransfer.files[0];
        //             var reader = new FileReader();
        //             reader.onload = function (e) {
        //                 newProfilePic('record', e.target.result, recordDroppedFile.name);
        //                 recordDroppedFile = e.target.result;
        //             };
        //             reader.readAsDataURL(recordDroppedFile);
        //         });
        //     fileButton.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) { e.preventDefault(); e.stopPropagation(); })
        //         .on('dragover dragenter', function() { fileButton.addClass('is-dragover'); })
        //         .on('dragleave dragend drop', function() { fileButton.removeClass('is-dragover'); })
        //         .on('drop', function(e) {
        //             e.stopPropagation();
        //             e.preventDefault();
        //
        //             fileDroppedFile = e.originalEvent.dataTransfer.files[0];
        //             var reader = new FileReader();
        //             reader.onload = function (e) {
        //                 newProfilePic('file', e.target.result, fileDroppedFile.name);
        //                 fileDroppedFile = e.target.result;
        //             };
        //             reader.readAsDataURL(fileDroppedFile);
        //         });
        // }
    }

    initializeFormProgression();
    initializeFileUpload();
    initializeImportRecords();
}