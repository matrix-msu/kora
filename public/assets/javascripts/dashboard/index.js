var Kora = Kora || {};
Kora.Dashboard = Kora.Dashboard || {};

Kora.Dashboard.Index = function() {

    function initializeSelects() {
        //Most field option pages need these
        $('.single-select').chosen({
            width: '100%',
        });
    }

    function initializeDashboardModals() {
        Kora.Modal.initialize();

        $('.create-block-js').click(function (e) {
            e.preventDefault();

            Kora.Modal.open($('.create-block-modal-js'));
        });

        $('.remove-block-js').click(function (e) {
            e.preventDefault();

            let blkID = $(this).attr('blkid');
            let secID = $(this).attr('secid');
            let url = removeBlockUrl + '/' + blkID + '/' + secID;
            $('.delete-block-form-js').attr('action', url);

            Kora.Modal.open($('.delete-block-modal-js'));
        });

        $('.edit-block-js').click(function (e) {
            e.preventDefault();

            let blkID = $(this).attr('blkid');
            $('input[name="selected_id"]').val(''+blkID+'');

            Kora.Modal.open($('.edit-block-modal-js'));
        });

        $('.edit-quick-options-js').click(function (e) {
            e.preventDefault();

            Kora.Modal.open($('.edit-quick-actions-modal-js'));
        })

        if(state == 1)
            Kora.Modal.open($('.create-block-modal-js'));
    }

	function initializeEditDashboardMode() {
        $('.edit-blocks-js').click(function (e) {
            e.preventDefault();

            $('.edit-dashboard-js').removeClass('hidden');
            $('.done-editing-dash-js').removeClass('hidden');
            $('.edit-blocks-js').addClass('hidden');
            $('.container .element').addClass('edit-mode');
            $('.floating-buttons').addClass('hidden');
            $('.grid.add-section').removeClass('hidden');
            $('.section-quick-actions').addClass('show');
            $('.grid:not(.add-section) .title').addClass('hidden');
            $('.edit-section-title-js').removeClass('hidden');

            $('.sections').sortable({
                disabled: false
            });
            $('.section-js .container').sortable({
                disabled: false
            });
        });

        $('.done-editing-dash-js').click(function (e) {
            e.preventDefault();

            $('.edit-dashboard-js').addClass('hidden');
            $('.done-editing-dash-js').addClass('hidden');
            $('.edit-blocks-js').removeClass('hidden');
            $('.container .element').removeClass('edit-mode');
            $('.floating-buttons').removeClass('hidden');
            $('.grid.add-section').addClass('hidden');
            $('.section-quick-actions').removeClass('show');
            $('.title').removeClass('hidden');
            $('.edit-section-title-js').addClass('hidden');

            $('.sections').sortable({
                disabled: true
            });
            $('.section-js .container').sortable({
                disabled: true
            });
        });
    }

    function initializeEditSections() {
        $(".sections").sortable({
            helper: 'clone',
            revert: true,
            containment: ".dashboard",
            update: function(event, ui) {
                sectionIDs = $('.sections').sortable('toArray');

                $.ajax({
                    url: editSectionUrl,
                    type: 'POST',
                    data: {
                        "_token": CSRFToken,
                        "_method": 'PATCH',
                        "sections": sectionIDs
                    },
                    success: function(result) {},
                    error: function (err) {
                        console.log(err);
                    }
                });
            },
            disabled: true
        });

        $('.add-section-input-js').on('keyup', function (e) {
            e.preventDefault();

            if (e.keyCode == 13) {
                let secTitle = $('.add-section-input-js').val();
                let url = addSectionUrl + '/' + secTitle;

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        '_token': CSRFToken,
                        '_method': 'POST',
                        'sectionTitle': secTitle
                    },
                    success: function () {
                        window.location.reload();
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        });

        $('.delete-section-js').click(function (e) {
            e.preventDefault();

            let secID = $(this).attr('data-id');
            let url = removeSectionUrl + '/' + secID;

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    '_token': CSRFToken,
                    '_method': 'DELETE'
                },
                success: function (data) {
                    window.location.reload();
                },
                error: function (err) {
                    console.log(err);
                }
            });
        });

        $('.done-editing-dash-js').click(function (e) {
            e.preventDefault();

            let titles
            values = {};
            $.each($('.edit-section-title-js'), function () {
                if ($(this).val()) {
                    if (!titles)
                        titles = $(this).attr('secid') + '-' + $(this).val() + '_';
                    else
                        titles = titles + $(this).attr('secid') + '-' + $(this).val() + '_';
                }
            });

            if (titles) {
                titles = titles.slice(0, -1);
                values['modified_titles'] = titles;
                values['_token'] = CSRFToken;
                values['_method'] = 'PATCH';

                $.ajax({
                    url: editSectionUrl,
                    method: 'POST',
                    data: values,
                    success: function (data) {
                        window.location.reload();
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        });
    }

    function initializeEditBlocks() {
        $(".section-js .container").sortable({
            helper: 'clone',
            revert: true,
            containment: ".sections",
            update: function(event, ui) {
                blocks = $('.section-js .container').sortable('toArray');

                $.ajax({
                    url: editBlockOrderUrl,
                    type: 'POST',
                    data: {
                        "_token": CSRFToken,
                        "_method": 'PATCH',
                        "blocks": blocks
                    },
                    success: function(result) {},
                    error: function (err) {
                        console.log(err);
                    }
                });
            },
            disabled: true
        });

        function updateNoteBlock (noteBlock) {
            let blockId = noteBlock.attr('id');
            let noteTitle = noteBlock.find('.note-title-js');
            let noteDesc = noteBlock.find('.note-desc-js');

            if (noteTitle.val() != '' && noteTitle.attr('placeholder') != noteTitle.val())
                noteTitle = noteTitle.val();
            else
                noteTitle = noteTitle.attr('placeholder');

            if (noteDesc.val() != '' && noteDesc.attr('placeholder') != noteDesc.val())
                noteDesc = noteDesc.val();
            else
                noteDesc = noteDesc.attr('placeholder');

            $.ajax({
                url: editNoteBlockUrl,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "_method": 'PATCH',
                    "block_id": blockId,
                    "block_note_title": noteTitle,
                    "block_note_content": noteDesc
                },
                success: function (result) {},
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $('.note-title-js').on('blur', function (e) {
            e.preventDefault();

            updateNoteBlock($(this).parent().parent());
        });

        $('.note-desc-js').each(function () {
            this.setAttribute('style', 'height:auto;');
        }).on('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight + 8) + 'px';
        }).on('blur', function () {
            updateNoteBlock($(this).parent());
        });

        $('.delete-block-js').click(function (e) {
            e.preventDefault();

            let $form = $('.delete-block-form-js');
            let url = $form.attr('action');

            values = {};
            $.each($form.children('input').serializeArray(), function (i, field) {
                values[field.name] = field.value;
            });
            values['_method'] = 'DELETE';

            $.ajax({
                url: url,
                method: 'POST',
                data: values,
                success: function (data) {
                    window.location.reload();
                },
                error: function (err) {
                    console.log(err);
                }
            });
        });
    }

    function initializeAddBlockFunctions() {
        function setAddBlockVisibility(proj, form, rec, note) {
            $('.project-block-fields-js').addClass('hidden');
            $('.form-block-fields-js').addClass('hidden');
            $('.record-block-fields-js').addClass('hidden');
            $('.note-block-fields-js').addClass('hidden');

            if(proj)
                $('.project-block-fields-js').removeClass('hidden');
            if(form)
                $('.form-block-fields-js').removeClass('hidden');
            if(rec)
                $('.record-block-fields-js').removeClass('hidden');
            if(note)
                $('.note-block-fields-js').removeClass('hidden');
        }

        $('.block-type-selected-js').change(function(e) {
            var typeVal = $(this).val();

            if(typeVal == 'Project')
                setAddBlockVisibility(1,0,0,0);
            else if(typeVal == 'Form')
                setAddBlockVisibility(0,1,0,0);
            else if(typeVal == 'Record')
                setAddBlockVisibility(0,0,1,0);
            else if(typeVal == 'Note')
                setAddBlockVisibility(0,0,0,1);

            $('.section-to-add-js').prop('disabled', false).trigger("chosen:updated");
        });

        $('.section-to-add-js').change(function(e) {
            $('.add-block-submit-js, .edit-block-submit-js').removeClass('disabled');
        });
    }

    function initializeValidation() {
        function validate($form) {
            values = {};
            $.each($form.serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });
            values['_token'] = CSRFToken;

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(data) {
                    $form.submit();
                },
                error: function(err) {
					console.log(err);
                    $('.error-message').text('');
                    $('.text-input, .text-area, .chosen-container').removeClass('error');

                    $.each(err.responseJSON.errors, function(fieldName, errors) {
                        var $field = $('#'+fieldName);
                        $field.addClass('error');
                        $field.siblings('.error-message').text(errors[0]);
                    });
                }
            });
        }

        $('.edit-block-submit-js').on('click', function(e) {
            e.preventDefault();

            let $form = $('#block_edit_form');

            //validate($form);

            values = {};
            $.each($form.serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });
            values['_token'] = CSRFToken;

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: values,
                success: function(data) {
                    $form.submit();
                },
                error: function(err) {
                    console.log(err);
                    $('.error-message').text('');
                    $('.text-input, .text-area, .chosen-container').removeClass('error');

                    $.each(err.responseJSON.errors, function(fieldName, errors) {
                        var $field = $('#'+fieldName);
                        $field.addClass('error');
                        $field.siblings('.error-message').text(errors[0]);
                    });
                }
            });
        });

        $('.add-block-submit-js').on('click', function(e) {
            e.preventDefault();

            let $form = $('#block_create_form');
            validate($form);
        });
    }

    initializeSelects();
    initializeDashboardModals();
    initializeEditDashboardMode();
    initializeEditBlocks();
    initializeEditSections();
    initializeAddBlockFunctions();
    initializeValidation();
}