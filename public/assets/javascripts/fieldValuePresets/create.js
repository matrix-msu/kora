var Kora = Kora || {};
Kora.FieldValuePresets = Kora.FieldValuePresets || {};

Kora.FieldValuePresets.Create = function() {
    
    var currentPreset = -1;

    $('.single-select').chosen({
        width: '100%',
    });

    $('.multi-select').chosen({
        width: '100%',
    });

    function initializeFieldValuePresetSwitcher() {
        $('.preset-type-js').change(function() {
            var fieldType = $(this).val();
            var submitButton = $('.submit-button-js');

            submitButton.removeClass('disabled');

            switch(fieldType) {
                case 'Regex':
                    openOptionPreset(['show','hide','hide','hide']);
                    enableOptionInput([null,'disabled','disabled','disabled']);
                    break;
                case 'List':
                    openOptionPreset(['hide','show','hide','hide']);
                    enableOptionInput(['disabled',null,'disabled','disabled']);
                    break;
                default:
                    submitButton.addClass('disabled');
                    break;
            }
        });

        function openOptionPreset(order) {
            $('.open-regex-js').effect('slide', {
                direction: 'up',
                mode: order[0],
                duration: 240
            });
            $('.open-list-js').effect('slide', {
                direction: 'up',
                mode: order[1],
                duration: 240
            });
        }

        function enableOptionInput(order) {
            var textInput = $('.open-regex-js').find('.text-input').first();
            textInput.attr('disabled',order[0]);

            $(".list-option-js").each(function() {
                $(this).attr('disabled',order[1]);
            });
        }
    }

    function initializeDeletePresetModal() {
        Kora.Modal.initialize();

        $('.delete-preset-open-js').click(function(e) {
            e.preventDefault();

            currentPreset = $(this).attr('preset-id');
            Kora.Modal.open($('.delete-preset-modal-js'));
        });

        $('.delete-preset-js').click(function(e) {
            $.ajax({
                url: deletePresetURL,
                type: 'POST',
                data: {
                    "_token": csrfToken,
                    "_method": 'delete',
                    "presetId": currentPreset
                },
                success: function (result) {
                    location.replace(deleteRedirect);
                }
            });
        });
    }

    function initializeValidation() {
        $('.validate-preset-js').on('click', function(e) {
            var $this = $(this);

            e.preventDefault();

            values = {};
            $.each($('.preset-form').serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(data) {
                    $('.preset-form').submit();
                },
                error: function(err) {
                    $('.error-message').text('');
                    $('.text-input, .text-area').removeClass('error');

                    $.each(err.responseJSON.errors, function(fieldName, errors) {
                        var $field = $('input[name="'+fieldName+'"]');
                        $field.addClass('error');
                        $field.siblings('.error-message').text(errors[0]);
                    });
                }
            });
        });

        $('.text-input, .text-area').on('blur', function(e) {
            var field = this.id;
            var values = {};
            values[field] = this.value;
            values['_token'] = csrfToken;

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                error: function(err) {
                    if (err.responseJSON.errors[field] !== undefined) {
                        $('input[name="'+field+'"]').addClass('error');
                        $('input[name="'+field+'"]').siblings('.error-message').text(err.responseJSON.errors[field][0]);
                    } else {
                        $('input[name="'+field+'"]').removeClass('error');
                        $('input[name="'+field+'"]').siblings('.error-message').text('');
                    }
                }
            });
        });
    }

    function initializeList() {
        Kora.Modal.initialize();

        function setCardTitleWidth() {
            var $cards = $('.list-option-card-js');

            $cards.each(function() {
                var $card = $(this);
                var $value = $card.find('.title');

                var maxValueWidth = $card.outerWidth() * .75;
                $value.css('max-width', maxValueWidth);
            })
        }

        // Function to add list options and the respective cards
        function initializeListAddOption() {
            var $addButton = $('.list-option-add-js');
            var $newListOptionInput = $('.new-list-option-js');
            var $cardContainer = $('.list-option-card-container-js');

            $newListOptionInput.keypress(function(e) {
                var keycode =  (e.keyCode ? e.keyCode : e.which);
                if (keycode == '13') {
                    e.preventDefault();

                    // Enter key pressed, trigger 'add' button click
                    $addButton.click();
                }
            });

            // Add new list option card after 'add' button pressed
            $addButton.click(function(e) {
                e.preventDefault();

                if($newListOptionInput.val() == '')
                    return;

                //Splits options up by comma, but ignores commas inside of double quotes
                var newListOptions = $newListOptionInput.val().split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/);

                if(newListOptions !== undefined && newListOptions.length > 0) {
                    // Prevent duplicate entries

                    //Foreach option
                    for(newOpt in newListOptions) {
                        //Trim whitespace, and remove surrounding quotes
                        newListOption = newListOptions[newOpt].replace (/(^")|("$)/g, '').trim();

                        // Create and display new card
                        var newCardHtml = '<div class="card list-option-card list-option-card-js" data-list-value="' + newListOption + '">' +
                            '<input type="hidden" class="list-option-js" name="preset[]" value="' + newListOption + '">' +
                            '<div class="header">' +
                            '<div class="left">' +
                            '<div class="move-actions">' +
                            '<a class="action move-action-js up-js" href="">' +
                            '<i class="icon icon-arrow-up"></i>' +
                            '</a>' +
                            '<a class="action move-action-js down-js" href="">' +
                            '<i class="icon icon-arrow-down"></i>' +
                            '</a>' +
                            '</div>' +
                            '<span class="title">' + newListOption + '</span>' +
                            '</div>' +
                            '<div class="card-toggle-wrap">' +
                            '<a class="list-option-delete list-option-delete-js tooltip" tooltip="Delete List Option" href=""><i class="icon icon-trash"></i></a>' +
                            '</div>' +
                            '</div>' +
                            '</div>';

                        $cardContainer.append(newCardHtml);
                    }

                    // Initialize functionality for all the cards again
                    $('.move-action-js').unbind();
                    setCardTitleWidth();
                    initializeListSort();
                    initializeListOptionDelete();

                    // Clear input after everything is finished
                    $newListOptionInput.val("");
                }
            });
        }

        function initializeListSort() {
            $('.move-action-js').click(function(e) {
                e.preventDefault();

                var $this = $(this);
                var $headerInnerWrapper = $this.parent().parent();
                var $header = $headerInnerWrapper.parent();
                var $card = $header.parent();
                // $form.prev().before(current);
                if ($this.hasClass('up-js')) {
                    var $previousForm = $card.prev();
                    if ($previousForm.length == 0) {
                        return;
                    }

                    $previousForm.css('z-index', 999)
                        .css('position', 'relative')
                        .animate({
                            top: $card.height()
                        }, 300);
                    $card.css('z-index', 1000)
                        .css('position', 'relative')
                        .animate({
                            top: '-' + $previousForm.height()
                        }, 300, function() {
                            $previousForm.css('z-index', '')
                                .css('top', '')
                                .css('position', '');
                            $card.css('z-index', '')
                                .css('top', '')
                                .css('position', '')
                                .insertBefore($previousForm);
                        });
                } else {
                    var $nextForm = $card.next();
                    if ($nextForm.length == 0) {
                        return;
                    }

                    $nextForm.css('z-index', 999)
                        .css('position', 'relative')
                        .animate({
                            top: '-' + $card.height()
                        }, 300);
                    $card.css('z-index', 1000)
                        .css('position', 'relative')
                        .animate({
                            top: $nextForm.height()
                        }, 300, function() {
                            $nextForm.css('z-index', '')
                                .css('top', '')
                                .css('position', '');
                            $card.css('z-index', '')
                                .css('top', '')
                                .css('position', '')
                                .insertAfter($nextForm);
                        });
                }
            });
        }

        function initializeListOptionDelete() {
            var $listOptionCards = $('.list-option-card-js');

            $listOptionCards.each(function() {
                var $card = $(this);
                var $deleteButton = $card.find('.list-option-delete-js');

                $deleteButton.click(function(e) {
                    e.preventDefault();

                    $card.remove();
                });
            });
        }

        function initializeMassListOptions() {
            $('.list-option-mass-copy-js').click(function(e) {
                e.preventDefault();

                var $cards = $('.list-option-card-js');
                var returnArray = [];

                if($cards.length > 0) {
                    for (var i = 0; i < $cards.length; i++) {
                        var $card = $($cards[i]);
                        var option = $card.find('.list-option-js').val();

                        if(option.includes(','))
                            option = '"'+option+'"';

                        returnArray.push(option);
                    }
                }

                var returnString = returnArray.join();

                //Send to clipboard
                copyToClipboard(returnString);
            });

            $('.list-option-mass-delete-js').click(function(e) {
                e.preventDefault();

                $deleteMassListOptionModal = $('.delete-mass-list-option-js');
                $deleteMassOptionButton = $('.delete-mass-options-js');
                $deleteMassOptionButton.attr('card-class','.list-option-card-js');

                Kora.Modal.open($deleteMassListOptionModal);
            });

            $('.delete-mass-options-js').click(function(e) {
                $deleteMassListOptionModal = $('.delete-mass-list-option-js');
                var callback = $(this).attr('card-class');
                var $cards = $(callback);

                if($cards.length > 0) {
                    for (var i = 0; i < $cards.length; i++) {
                        var $card = $($cards[i]);

                        $card.remove();
                    }
                }

                Kora.Modal.close($deleteMassListOptionModal);
            });

            function copyToClipboard(stringToCopy) {
                // Create a dummy input to copy the string array inside it
                var dummy = document.createElement("input");
                // Add it to the document
                document.body.appendChild(dummy);
                // Set its ID
                dummy.setAttribute("id", "copy_to_clipboard");
                // Output the array into it
                document.getElementById("copy_to_clipboard").value=stringToCopy;
                // Select it
                dummy.select();
                // Copy its contents
                document.execCommand("copy");
                // Remove it as its not needed anymore
                document.body.removeChild(dummy);
            }
        }

        setCardTitleWidth();
        initializeListAddOption();
        initializeListSort();
        initializeListOptionDelete();
        initializeMassListOptions();
    }

    initializeFieldValuePresetSwitcher();
    initializeList();
    initializeDeletePresetModal();
    initializeValidation();
}