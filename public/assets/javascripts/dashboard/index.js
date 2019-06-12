var Kora = Kora || {};
Kora.Dashboard = Kora.Dashboard || {};

Kora.Dashboard.Index = function() {

    function initializeSelects() {
        //Most field option pages need these
        $('.edit-block-modal-js .single-select, .create-block-modal-js .single-select').chosen({
            width: '100%',
            disable_search_threshold: 7
        });
    }

	function setQuickActionOrder () {
        let cards = $('#card-container .card');
        let options = []
		let hiddenOpts = []

        for (let i = 0; i < cards.length; i++) {
            if (i < 6)
                options.push(cards[i].getAttribute('type'))
            else
                hiddenOpts.push(cards[i].getAttribute('type'))
        }
		$('input[name="options"]').val(options);
        $('input[name="hiddenOpts"]').val(hiddenOpts);

        let line = $('#card-container .line-container').detach();
        line.insertBefore($('#card-container .card:nth-child(7)'));
	}

    function editQuickActionsSort () {
        $(".card-container").sortable({
            helper: 'clone',
            revert: true,
            containment: ".edit-quick-actions-modal-js",
			update: function () {
				setQuickActionOrder()
			}
        });

        $('.edit-quick-actions-submit-js').click(function (e) {
            e.preventDefault();

            let form = $('#edit_quickActions_form');
            let values = {};
            $.each(form.children('input').serializeArray(), function (i, field) {
                values[field.name] = field.value;
            });

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: values,
                success: function () {
                    Kora.Modal.close($('.edit-quick-actions-modal-js'));
                    window.localStorage.setItem('edit-mode', true)
                    window.location.reload()
                },
                error: function (err) {
                    console.warn(err)
                }
            });
        })
    }

	function moveUp (event) {
		let $card = event.target.parentElement.parentElement.parentElement.parentElement
		$card = $('#' + $card.parentElement.id + ' #' + $card.id + '.card');

        let $previousCard = $card.prev();
		if ($previousCard.length == 0)
            return

        if ($previousCard.hasClass('line-container'))
            $previousCard = $card.prev().prev();

		$previousCard.css('z-index', 999)
			.css('position', 'relative')
			.animate({
				top: $card.height()
			}, 300);

        $card.css('z-index', 1000)
			.css('position', 'relative')
			.animate({
				top: '-' + $card.height()
			}, 300, function() {
				$previousCard.css('z-index', '')
					.css('top', '')
					.css('position', '');
				$card.css('z-index', '')
					.css('top', '')
					.css('position', '')
					.insertBefore($previousCard);

					setQuickActionOrder()
			});
	}

	function moveDown (event) {
        let $card = event.target.parentElement.parentElement.parentElement.parentElement
		$card = $('#' + $card.parentElement.id + ' #' + $card.id + '.card');

		let $nextCard = $card.next();
		if ($nextCard.length == 0)
            return

        if ($nextCard.hasClass('line-container'))
            $nextCard = $card.next().next();

		$nextCard.css('z-index', 999)
			.css('position', 'relative')
			.animate({
				top: '-' + $card.height()
			}, 300);

        $card.css('z-index', 1000)
			.css('position', 'relative')
			.animate({
				top: $card.height()
			}, 300, function() {
				$nextCard.css('z-index', '')
					.css('top', '')
					.css('position', '');
				$card.css('z-index', '')
					.css('top', '')
					.css('position', '')
					.insertAfter($nextCard);

					setQuickActionOrder()
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
            $('.delete-block-modal-js input[name="blkID"]').val(blkID);

            Kora.Modal.open($('.delete-block-modal-js'));
        });

        $('.edit-block-js').click(function (e) {
            e.preventDefault();

            let blkID = $(this).attr('blkid');
            $('input[name="selected_id"]').val(blkID);

            let blkType = $(this).attr('blocktype');
            let secID = $(this).attr('secid');
            let selected
            let selectedSelector

            if (blkType == 'Project') {
                selectedSelector = $('.edit-block-project-js');
                selected = $(this).attr('blockproject');
            } else if (blkType == 'Form') {
                selectedSelector = $('.edit-block-form-js');
                selected = $(this).attr('blockform');
            } else if (blkType == 'Record') {
                selectedSelector = $('.edit-block-record-js');
                selected = $(this).attr('blockrecord');
            } else if (blkType == 'Note') {
				let note = $(this).parent().parent().parent().parent();
				$('.edit-note-title-js').val(note.find('.note-title-js').attr('placeholder'));
				$('.edit-note-desc-js').val(note.find('.note-desc-js').attr('placeholder'));
			}

            $('.edit-block-type-selected-js').val(blkType);
            $('.edit-block-type-selected-js').trigger('chosen:updated');
            $('.block-type-selected-js').trigger('change');

            if (selected) {
                selectedSelector.val(selected);
                selectedSelector.trigger('chosen:updated');
                $('.block-type-selected-js').trigger('change');
            }

            $('.edit-section-to-add-js').val(secID);
            $('.edit-section-to-add-js').trigger('chosen:updated');
            $('.section-to-add-js').trigger('change');

            Kora.Modal.open($('.edit-block-modal-js'));
        });

        $('.edit-quick-options-js').click(function (e) {
            e.preventDefault();

			$('input[name="selected_id"]').val($(this).siblings('a.remove-block-js').attr('blkid'));
			$('#card-container').children().remove();

            let template = document.getElementById('quick-action-template-js')

            let cardContainer = document.getElementById('card-container')

            let $linkOpts = $(this).parent().parent().parent().siblings('.element-link-container').children('.element-link:not(.right)');
            $.each($linkOpts, function (i) {
				let clone = document.importNode(template.content, true)

				clone.querySelector('.card').id = i
				clone.querySelector('.card').setAttribute('type', $linkOpts[i].getAttribute('quickaction'))
				clone.querySelector('.quick-action-title-js').textContent = $linkOpts[i].getAttribute('tooltip')
				clone.querySelector('.up-js').addEventListener('click', moveUp)
				clone.querySelector('.down-js').addEventListener('click', moveDown)

                cardContainer.appendChild(clone)
            });

            $('#card-container').append('<div class="line-container"><span class="line"></span></div>');

			//let lowerCards = document.getElementById('card-container-bottom')
			let rightOpts = $(this).parent().parent().parent().siblings('.element-link-container').find('.element-link-right-tooltips ul li').children();
			$.each(rightOpts, function (i) {
				let clone = document.importNode(template.content, true)

				clone.querySelector('.card').id = 6 + i
				clone.querySelector('.card').setAttribute('type', rightOpts[i].getAttribute('quickaction'))
				clone.querySelector('.quick-action-title-js').textContent = rightOpts[i].textContent
				clone.querySelector('.up-js').addEventListener('click', moveUp)
				clone.querySelector('.down-js').addEventListener('click', moveDown)

                //lowerCards.appendChild(clone)
                cardContainer.appendChild(clone)
			});

            setQuickActionOrder()
            Kora.Modal.open($('.edit-quick-actions-modal-js'));
        });

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
            $('.header.add-section').removeClass('hidden');
            $('.section-quick-actions').addClass('show');
            $('.grid:not(.add-section) .title').addClass('hidden');
            $('.edit-section-title-js').removeClass('hidden');

            $('.sections').sortable({
                disabled: false
            });
            $('#sections .section-js .container').sortable({
                disabled: false
            });
            window.localStorage.setItem('edit-mode', true)
        });

        $('.done-editing-dash-js').click(function (e) {
            e.preventDefault();

            $('.edit-dashboard-js').addClass('hidden');
            $('.done-editing-dash-js').addClass('hidden');
            $('.edit-blocks-js').removeClass('hidden');
            $('.container .element').removeClass('edit-mode');
            $('.floating-buttons').removeClass('hidden');
            $('.header.add-section').addClass('hidden');
            $('.section-quick-actions').removeClass('show');
            $('.title').removeClass('hidden');
            $('.edit-section-title-js').addClass('hidden');

            $('.sections').sortable({
                disabled: true
            });
            $('#sections .section-js .container').sortable({
                disabled: true
            });
            window.localStorage.clear()
        });
    }

    function initializeEditSections() {
        function reorderSections () {
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
        }

        $(".sections").sortable({
            helper: 'clone',
            revert: true,
            containment: ".dashboard",
            update: function(event, ui) {
                reorderSections()
            },
            disabled: true,
            cancel: '.add-section'
        });

        $('.move-action-js').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var $section = $this.parent().parent().parent();

            if ($this.hasClass('up-js')) {
                var $previousSection = $section.prev();
                if ($previousSection.length == 0 || $previousSection.hasClass('no-section'))
                    return

                $previousSection.css('z-index', 999)
                    .css('position', 'relative')
                    .animate({
                        top: $section.height()
                    }, 300);
                $section.css('z-index', 1000)
                    .css('position', 'relative')
                    .animate({
                        top: '-' + $previousSection.height()
                    }, 300, function() {
                        $previousSection.css('z-index', '')
                            .css('top', '')
                            .css('position', '');
                        $section.css('z-index', '')
                            .css('top', '')
                            .css('position', '')
                            .insertBefore($previousSection);

                        reorderSections()
                    });
            } else {
                var $nextSection = $section.next();
                if ($nextSection.length == 0 || $nextSection.hasClass('add-section'))
                    return

                $nextSection.css('z-index', 999)
                    .css('position', 'relative')
                    .animate({
                        top: '-' + $section.height()
                    }, 300);
                $section.css('z-index', 1000)
                    .css('position', 'relative')
                    .animate({
                        top: $nextSection.height()
                    }, 300, function() {
                        $nextSection.css('z-index', '')
                            .css('top', '')
                            .css('position', '');
                        $section.css('z-index', '')
                            .css('top', '')
                            .css('position', '')
                            .insertAfter($nextSection);

                        reorderSections()
                    });
            }
        });

        $('.add-section-input-js').on('keyup', function (e) {
            e.preventDefault();

            function createNewSection (section) {
                // have to use JQuery's .clone() method instead of javascript .importNode or similar methods
                // because JAVASCRIPT doesn't clone event listeners whereas JQ .clone() does
                $('.dashboard .section-js.hidden').clone(true).insertBefore('.sections section:last-child');
                $('.sections .section-js.hidden').find('.title').text(section.sec_title);
                $('.sections .section-js.hidden').find('.edit-section-title-js').val('' + section.sec_title);
                $('.sections .section-js.hidden').find('.edit-section-title-js').attr('placeholder', section.sec_title);
                $('.sections .section-js.hidden').find('.delete-section-js').attr('data-id', section.sec_id);
                $('.sections .section-js.hidden').attr('id', section.sec_id);
                $('.sections .section-js.hidden').addClass('no-children');

                $(".sections").sortable({
                    helper: 'clone',
                    revert: true,
                    containment: ".dashboard",
                    update: function(event, ui) {
                        reorderSections()
                    },
                    disabled: false,
                    cancel: '.add-section'
                });

                $("#sections .section-js .container").sortable({
                    helper: 'clone',
                    containment: ".dashboard",
                    connectWith: '.container',
                    items: '.element',
                    update: function(event, ui) {
                        reorderBlocks()
                    },
                    disabled: false
                });

                $('.sections .section-js.hidden').removeClass('hidden');

                $('.add-section-input-js').val('');
            }

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
                    success: function (data) {
                        createNewSection(data)
                    },
                    error: function (err) {
                        console.log(err);
                        // POSSIBLE::Notify user of failed section creation?
                    }
                });
            }
        });

        $('.delete-section-js').click(function (e) {
            e.preventDefault();

            function removeSection (dSection, nSectionID) {
                let blocks = dSection.children('.container').children();

                $.each(blocks, function () {
                    $('#' + nSectionID).children('.container').append($(this));
                });

                dSection.remove();
                reorderBlocks()
            }

            let deletedSection = $(this).parent().parent().parent();
            let secID = $(this).attr('data-id');
            let url = removeSectionUrl + '/' + secID;

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    '_token': CSRFToken,
                    '_method': 'DELETE'
                },
                success: function (response) {
                    removeSection(deletedSection, response.section)
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
            $.each($('.sections .edit-section-title-js'), function () {
                if ($(this).val() != $(this).attr('placeholder')) {
                    console.log($(this))
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
                        //window.location.reload();
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        });
    }

    function reorderBlocks() {
        $.each($('.sections .section-js:not(.add-section'), function () {
			let section = $(this).attr('id');
            let blocks = $(this).children('.container').sortable('toArray');

			if (blocks.length > 0) {
				$.ajax({
					url: editBlockOrderUrl,
					type: 'POST',
					data: {
						"_token": CSRFToken,
						"_method": 'PATCH',
						"blocks": blocks,
						"section": section
					},
					success: function(result) {},
					error: function (err) {
						console.log(err)
					}
				});
			}
        });

        $.each($('.sections .section-js:not(.add-section)'), function () {
            if ($(this).children('.container').children().length > 0)
                $(this).removeClass('no-children');
            else
                $(this).addClass('no-children');
        });
    }

    function initializeEditBlocks() {
        $("#sections .section-js .container").sortable({
            helper: 'clone',
            containment: ".dashboard",
            connectWith: '.container',
            items: '.element',
            update: function(event, ui) {
                reorderBlocks()
            },
            disabled: true
        });

        // allows blocks to be dragged by the 'edit-block' button
        // taken from https://stackoverflow.com/questions/22254701/drag-a-draggable-when-clicking-on-child?rq=1 -> http://jsfiddle.net/55bxX/1/
        $(".container .edit-block-js").on('mousedown', function (e) {
            var mdown = document.createEvent("MouseEvents");
            mdown.initMouseEvent("mousedown", true, true, window, 0, e.screenX, e.screenY, e.clientX, e.clientY, true, false, false, true, 0, null);
            $(this).closest('.edit-mode')[0].dispatchEvent(mdown);
        }).on('click', function (e) {
            var $draggable = $(this).closest('.edit-mode');
            if ($draggable.data("preventBehaviour")) {
                e.preventDefault();
                $draggable.data("preventBehaviour", false)
            }
        });

        function updateNoteBlock (noteBlock) {
            let blockId = noteBlock.attr('id');
            let noteTitle = noteBlock.find('.note-title-js');
            let noteDesc = noteBlock.find('.note-desc-js');

            if (noteTitle.attr('placeholder') != noteTitle.val())
                noteTitle = noteTitle.val();
            else
                noteTitle = noteTitle.attr('placeholder');

            if (noteDesc.attr('placeholder') != noteDesc.val())
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
            //this.setAttribute('style', 'height:auto;');
            this.style.height = (this.scrollHeight + 8) + 'px';
        }).on('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight + 8) + 'px';
            console.log(this.scrollHeight)
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
                success: function () {
                    Kora.Modal.close($('.delete-block-modal-js'));
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
            else if (form)
                $('.form-block-fields-js').removeClass('hidden');
            else if (rec)
                $('.record-block-fields-js').removeClass('hidden');
            else if (note)
                $('.note-block-fields-js').removeClass('hidden');
            else {
                $('.project-block-fields-js').addClass('hidden');
                $('.form-block-fields-js').addClass('hidden');
                $('.record-block-fields-js').addClass('hidden');
                $('.note-block-fields-js').addClass('hidden');
            }
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
            else
                setAddBlockVisibility(0,0,0,0)

            $('.section-to-add-js').prop('disabled', false).trigger("chosen:updated");
            $('.add-block-section-js .chosen-default span').text('Select a section to add to');
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

    function initEditPlaceholders () {
        $('.add-block-type-js .chosen-default span').text('Select a block type');
        $('.add-block-section-js .chosen-default span').text('Select a section to add to');
    }

    //Customized for the dashboard page because multiple form blocks can exist
    function initFormRecordExport() {
        var activeFid = 0;

        $('.export-record-js').click(function(e) {
            e.preventDefault();
            Kora.Modal.initialize();

            activeFid = $(this).attr('fid');
            var $exportRecordsModal = $('.export-records-modal-'+activeFid+'-js');

            Kora.Modal.open($exportRecordsModal);
        });

        $('.export-dashboard-begin-files-js').click(function(e) {
            e.preventDefault();
            $exportDiv = $(this);

            $exportDiv.addClass('disabled');
            $exportDiv.text("Generating zip file...");

            startURL = $exportDiv.attr('startURL');
            endURL = $exportDiv.attr('endURL');
            token = $exportDiv.attr('token');

            //Ajax call to prep zip
            $.ajax({
                url: startURL,
                type: 'POST',
                data: {
                    "_token": token
                },
                success: function (data) {
                    //Change text back
                    $exportDiv.removeClass('disabled');
                    $exportDiv.text("Export Record Files");
                    //Set page to download URL
                    document.location.href = endURL;
                },
                error: function (error) {
                    $exportDiv.removeClass('disabled');
                    $exportDiv.text("Something went wrong :(");

                    if(typeof error.responseJSON == 'undefined') {
                        $exportDiv.text("Error creating zip :(");
                        $('.export-files-desc-'+activeFid+'-js').text("Unable to create the zip. Please contact your administrator for more information. You may still export all form records in the formats of JSON or XML.");
                    } else if(error.responseJSON.message == 'no_record_files') {
                        $exportDiv.text("No record files :(");
                        $('.export-files-desc-'+activeFid+'-js').text("There are no record files in this Form. You may still export all form records in the formats of JSON or XML.");
                    } else if(error.responseJSON.message == 'zip_too_big') {
                        $exportDiv.text("Zip too big :(");
                        $('.export-files-desc-'+activeFid+'-js').text("Zipped file is too big. Please use the php artisan command for exporting record files. You may still export all form records in the formats of JSON or XML.");
                    }
                }
            });
        });
    }

    function setState () {
        let editMode = window.localStorage.getItem('edit-mode')
        window.localStorage.clear()

        if (editMode) {
            $('.edit-blocks-js').trigger('click');
        }
    }

    $(document).ready(function () {
        $.each($('.sections .section-js:not(.add-section)'), function () {
            if ($(this).children('.container').children().length > 0)
                $(this).removeClass('no-children');
            else
                $(this).addClass('no-children');
        });
    });

    initializeSelects();
	editQuickActionsSort();
    initializeDashboardModals();
    initializeEditDashboardMode();
    initializeEditBlocks();
    initializeEditSections();
    initializeAddBlockFunctions();
    initializeValidation();
    initEditPlaceholders();
    initFormRecordExport();
    setState();
}