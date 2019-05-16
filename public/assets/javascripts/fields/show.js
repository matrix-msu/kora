var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};

Kora.Fields.Show = function() {

    function initializeCleanUpModals() {
        Kora.Modal.initialize();

        $('.field-trash-js').click(function(e) {
            e.preventDefault();

            var cleanupModal = $('.field-cleanup-modal-js');

            cleanupModal.find('.title-js').html(
                $(this).data('title')
            );

            cleanupModal.find('.delete-content-js').show();
            Kora.Modal.open(cleanupModal);
        });
    }

    function initializeRegexPresetModals() {
        Kora.Modal.initialize();

        var regexModal = $('.add-regex-preset-modal-js');
        var createRegexModal = $('.create-regex-preset-modal-js');
        var newRegex = '';

        $('.open-regex-modal-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open(regexModal);
        });

        $('.add-regex-preset-js').click(function(e) {
            e.preventDefault();

            var regexVal = $('[name="regex_preset"]').val();
            $('[name="regex"]').val(regexVal);

            Kora.Modal.close(regexModal);
        });

        $('.open-create-regex-modal-js').click(function(e) {
            e.preventDefault();

            newRegex = $('[name="regex"]').val();

            if(newRegex!='')
                Kora.Modal.open(createRegexModal);
        });

        $('.create-regex-preset-js').click(function(e) {
            e.preventDefault();

            var name = $('[name="preset_title_regex"]').val();
            var type = 'Regex';
            var preset = newRegex;
            var shared = $('[name="preset_shared_regex"]').is(':checked') ? 1 : 0;

            $.ajax({
                url: createFieldValuePresetURL,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "name": name,
                    "type": type,
                    "preset": preset,
                    "shared": shared
                },
                success: function(result) {
                    Kora.Modal.close(createRegexModal);
                }
            });
        });
    }

    function initializeListPresetModals() {
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
                            updateListDefaultOptions();
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
                            updateListDefaultOptions();
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

                    updateListDefaultOptions();
                });
            });
        }

        function updateListDefaultOptions() {
            var $cards = $('.list-option-card-js');
            var $listDef = $('.list-default-js');

            var optionsHtml = "";
            if ($cards.length > 0) {
                optionsHtml += '<option></option>';
                for (var i = 0; i < $cards.length; i++) {
                    var $card = $($cards[i]);
                    var option = $card.find('.list-option-js').val();
                    optionsHtml += '<option value="'+option+'">'+option+'</option>';
                }
            } else {
                optionsHtml += '<option value="" disabled>No options to select!</option>';
            }

            $listDef.html(optionsHtml);
            $listDef.trigger("chosen:updated");
        }

        var listModal = $('.add-list-preset-modal-js');
        var createListModal = $('.create-list-preset-modal-js');
        var newList = [];

        $('.open-list-modal-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open(listModal);
        });

        $('.add-list-preset-js').click(function(e) {
            e.preventDefault();

            var listVal = $('[name="list_preset"]').val();
            listValArray = listVal.split('[!]');
            var $cardContainer = $('.list-option-card-container-js');

            //clear old values
            $cardContainer.html('');

            //Loop through results to
            for(var i = 0; i < listValArray.length; i++) {
                var newListOption = listValArray[i];

                //add to cards
                // Create and display new card
                var newCardHtml = '<div class="card list-option-card list-option-card-js" data-list-value="' + newListOption + '">' +
                    '<input type="hidden" class="list-option-js" name="options[]" value="' + newListOption + '">' +
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
                    '<a class="list-option-delete list-option-delete-js" href=""><i class="icon icon-trash"></i></a>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                $cardContainer.append(newCardHtml);

                // Initialize functionality for all the cards
            }

            setCardTitleWidth();
            initializeListSort();
            initializeListOptionDelete();
            updateListDefaultOptions();
            Kora.Fields.TypedFieldInputs.Initialize();

            Kora.Modal.close(listModal);
        });

        $('.open-create-list-modal-js').click(function(e) {
            e.preventDefault();

            newList = $('[name="options[]"]').map(function(){return $(this).val();}).get();

            if(newList!=[])
                Kora.Modal.open(createListModal);
        });

        $('.create-list-preset-js').click(function(e) {
            e.preventDefault();

            var name = $('[name="preset_title_list"]').val();
            var type = 'List';
            var preset = newList;
            var shared = $('[name="preset_shared_list"]').is(':checked') ? 1 : 0;

            $.ajax({
                url: createFieldValuePresetURL,
                type: 'POST',
                data: {
                    "_token": CSRFToken,
                    "name": name,
                    "type": type,
                    "preset": preset,
                    "shared": shared
                },
                success: function(result) {
                    Kora.Modal.close(createListModal);
                }
            });
        });
    }

    function initializeComboPresetModals() { //TODO::CASTLE
        let option = '';

        $('.open-regex-modal-js').click(function (e) {
            e.preventDefault();

            option = $(this).parent().parent().find('label').attr('for');
            if (option.includes('one')) {
                $('.add-regex-one').next().show();
                $('.add-regex-two').next().hide();
            } else {
                $('.add-regex-one').next().hide();
                $('.add-regex-two').next().show();
            }
        });

        $('.add-combo-preset-js').click(function (e) {
            e.preventDefault();

            let select = $('.add-regex-preset-modal-js select');
            let value = '';
            if (option.includes('one')) {
                value = select[0].value;
            } else {
                value = select[1].value;
            }

            if (option.includes('regex')) {
                $('[name="'+option+'"]').val(value);
            } else {
                let listVal = value;
                listValArray = listVal.split('[!]');

                //clear old values
                let optDiv = $('[name="'+option+'\[\]"]');
                optDiv.html('');

                //Loop through results to
                for(let i = 0; i < listValArray.length; i++) {
                    let option = $("<option>").val(listValArray[i]).text(listValArray[i]);

                    optDiv.append(option.clone());
                }

                //refresh chosen
                optDiv.find($('option')).prop('selected', true);
                optDiv.trigger("chosen:updated");
            }

            Kora.Modal.close();
        });
    }

    function scrollTop (allScrolls) {
      var scrollTo = Math.min(...allScrolls);
      var scrollTo = scrollTo - 100;
      setTimeout( function () {
        $('html, body').animate({
          scrollTop: scrollTo
        }, 500);
      });
    }

    function initializeValidation() {
        $('.validate-field-js').on('click', function(e) {
            var $this = $(this);

            e.preventDefault();

            values = {};
            previews = [];
            let str = '';
            $.each($('.edit-form').serializeArray(), function(i, field) {
                if (field.name.includes('preview_')) {
                    previews.push(field.value);
                } else {
                    values[field.name] = field.value;
                }
            });
            str = previews.toString();
            str = str.replace(/,/g, '-');
            $('input[name="flids"]').val(str);

            values[$('.assoc-preview-js').attr('id')] = str;
            values['_method'] = 'PATCH';

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(data) {
                    advValid = validateAdvancedOptions(currFieldType);
                    if(advValid)
                        $('.edit-form').submit();
                },
                error: function(err) {
                    $('.error-message').text('');
                    $('.text-input, .text-area').removeClass('error');
                    var allScrolls = [];

                    $.each(err.responseJSON.errors, function(fieldName, errors) {
                        var $field = $('#'+fieldName);
                        $field.addClass('error');
                        $field.siblings('.error-message').text(errors[0]);
                        allScrolls.push($field.offset().top);
                    });

                    scrollTop(allScrolls);
                }
            });
        });

        $('.text-input, .text-area').on('blur', function(e) {
            var field = this.id;
            var values = {};
            values[field] = this.value;
            values['_token'] = CSRFToken;
            values['_method'] = 'PATCH';

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                error: function(err) {
                    if (err.responseJSON.errors[field] !== undefined) {
                        $('#'+field).addClass('error');
                        $('#'+field).siblings('.error-message').text(err.responseJSON.errors[field][0]);
                    } else {
                        $('#'+field).removeClass('error');
                        $('#'+field).siblings('.error-message').text('');
                    }
                }
            });
        });
    }

    function validateAdvancedOptions(currType) {
        var valid = true;

        switch(currType) {
            case 'Text':
                var regexDiv = $('.text-regex-js');
                var defDiv = $('.text-default-js');

                var regex = regexDiv.val();
                var def = defDiv.val();

                if(regex!='' && def!='') {
                    regex = new RegExp(regex);
                    var match = regex.test(def);

                    if(!match) {
                        defDiv.addClass('error');
                        defDiv.siblings('.error-message').text("Default value must match the regular expression pattern.");
                        valid = false;
                    } else {
                        defDiv.removeClass('error');
                        defDiv.siblings('.error-message').text('');
                    }
                } else {
                    defDiv.removeClass('error');
                    defDiv.siblings('.error-message').text('');
                }
                break;
            case 'Integer':
            case 'Float':
                var defDiv = $('.number-default-js');
                var minDiv = $('.number-min-js');
                var maxDiv = $('.number-max-js');
                var stepDiv = $('.number-step-js');

                var def = defDiv.val();
                var min = minDiv.val();
                var max = maxDiv.val();
                var step = stepDiv.val();

                if(min!='' && max!='') {
                    if(min >= max) {
                        minDiv.addClass('error');
                        minDiv.siblings('.error-message').text('The minimum must be less than the max.');
                        valid = false;
                    } else {
                        minDiv.removeClass('error');
                        minDiv.siblings('.error-message').text('');
                    }

                    if(step > (max-min)) {
                        stepDiv.addClass('error');
                        stepDiv.siblings('.error-message').text('The increment cannot be bigger than the gap between min and max.');
                        valid = false;
                    } else {
                        stepDiv.removeClass('error');
                        stepDiv.siblings('.error-message').text('');
                    }
                } else {
                    minDiv.removeClass('error');
                    minDiv.siblings('.error-message').text('');
                    stepDiv.removeClass('error');
                    stepDiv.siblings('.error-message').text('');
                }

                if(def!='') {
                    if(min!='' && def<min) {
                        defDiv.addClass('error');
                        defDiv.siblings('.error-message').text('Default value must be greater than the minimum.');
                        valid = false;
                    } else if(max!='' && def>max) {
                        defDiv.addClass('error');
                        defDiv.siblings('.error-message').text('Default value must be smaller than the maximum.');
                        valid = false;
                    } else {
                        defDiv.removeClass('error');
                        defDiv.siblings('.error-message').text('');
                    }
                } else {
                    defDiv.removeClass('error');
                    defDiv.siblings('.error-message').text('');
                }
                break;
            case 'Date':
            case 'Schedule':
                var startDiv = $('.start-year-js');
                var endDiv = $('.end-year-js');

                var start = startDiv.val();
                var end = endDiv.val();

                if(start=='') {
                    startDiv.addClass('error');
                    startDiv.siblings('.error-message').text('A start year is required.');
                    valid = false;
                } else if(start<0 || start>9999) {
                    startDiv.addClass('error');
                    startDiv.siblings('.error-message').text('A year must be between 0 and 9999.');
                    valid = false;
                } else {
                    startDiv.removeClass('error');
                    startDiv.siblings('.error-message').text('');
                }

                if(end=='') {
                    endDiv.addClass('error');
                    endDiv.siblings('.error-message').text('An end year is required.');
                    valid = false;
                } else if(end<0 || end>9999) {
                    endDiv.addClass('error');
                    endDiv.siblings('.error-message').text('A year must be between 0 and 9999.');
                    valid = false;
                } else {
                    endDiv.removeClass('error');
                    endDiv.siblings('.error-message').text('');
                }

                if(valid) {
                    if(start > end) {
                        startDiv.addClass('error');
                        startDiv.siblings('.error-message').text('The start year must be less than the end year.');
                        valid = false;
                    } else {
                        startDiv.removeClass('error');
                        startDiv.siblings('.error-message').text('');
                    }
                }
                break;
            default:
                break;
        }

        return valid;
    }

    initializeCleanUpModals();
    initializeRegexPresetModals();
    initializeListPresetModals();
    initializeComboPresetModals();
    initializeValidation();
}
