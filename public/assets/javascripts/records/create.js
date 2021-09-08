var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Create = function() {

    $('.single-select').chosen({
        allow_single_deselect: true,
        disable_search_threshold: 4,
        width: '100%',
    });

    $('.multi-select').chosen({
        width: '100%',
    });

    function initializeSelectAddition() {
        $('.chosen-search-input').on('keyup', function(e) {
            var container = $(this).parents('.chosen-container').first();

            if (e.which === 13 && (container.find('li.no-results').length > 0 || container.find('li.active-result').length == 0)) {
                var option = $("<option>").val(this.value.trim()).text(this.value.trim());

                var select = container.siblings('.modify-select').first();

                select.append(option);
                select.find(option).prop('selected', true);
                select.trigger("chosen:updated");
            }
        });
    }

    // Arrows to move cards up and down
    function initializeMoveAction($cards) {
        $cards.each(function() {
            var $card = $(this);
            var $moveActions = $card.find('.move-action-js');

            $moveActions.unbind();
            $moveActions.click(function(e) {
                e.preventDefault();

                var $moveAction = $(this);
                if ($moveAction.hasClass('up-js')) {
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
        });
    }

    function initializeSpecialInputs() {
        $('.ckeditor-js').each(function() {
            textid = $(this).attr('id');

            CKEDITOR.replace(textid);
        });
    }

    function intializeAssociatorOptions() {
        $('.assoc-search-records-js').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if(keyCode === 13) {
                e.preventDefault();

                var keyword = $(this).val();
                var combo = $(this).attr('combo');
                var assocSearchURI = $(this).attr('search-url');
                var resultsBox = $(this).parent().next().children('.assoc-select-records-js').first();
                //Clear old values
                resultsBox.html('');
                resultsBox.trigger("chosen:updated");

                var data = {
                    "_token": csrfToken,
                    "keyword": keyword
                };

                if (combo) {
                    data['combo'] = combo;
                }

                $.ajax({
                    url: assocSearchURI,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        var opts = '';
                        for(var kid in result) {
                            var preview = result[kid];
                            opts += "<option value='"+kid+"'>"+kid+": "+preview+"</option>";
                        }

                        // Wait until all options are added to html string until we update chosen
                        resultsBox.append(opts);
                        resultsBox.trigger("chosen:updated");

                        resultInput = resultsBox.next().find('.chosen-search-input').first();
                        resultInput.val('');
                        resultInput.click();
                    }
                });
            }
        });

        $('.assoc-select-records-js').change(function() {
            defaultBox = $(this).parent().next().children('.assoc-default-records-js');

            $(this).children('option').each(function() {
                if($(this).is(':selected')) {
                    option = $("<option/>", { value: $(this).attr("value"), text: $(this).text() });

                    defaultBox.append(option);
                    defaultBox.find(option).prop('selected', true);
                    defaultBox.trigger("chosen:updated");

                    $(this).prop("selected", false);
                }
            });

            $(this).trigger("chosen:updated");
        });
    }

    function initializeComboListOptions() {
        var flid, type1, type2, $comboValueDiv, $modal, $currentEditValue;

        var $comboCardContainers = $('.combo-value-item-container-js');
        var $comboCards = $comboCardContainers.find('.combo-value-item-js');
        initializeMoveAction($comboCards);

        $('.combo-list-display-js').on('click', '.delete-combo-value-js', function() {
            parentDiv = $(this).parent();
            parentDiv.remove();
        });

        $('.open-combo-value-modal-js').click(function(e) {
          flid = $(this).attr('flid');
          type1 = $(this).attr('typeOne');
          type2 = $(this).attr('typeTwo');

          $comboValueDiv = $('.combo-value-div-js-'+flid);
          var $modal = $comboValueDiv.find('.combo-list-modal-js');

          Kora.Modal.close();
          Kora.Modal.open($modal);
        });

        $('.add-combo-value-js').click(function() {
            //Grab the default values entered
            switch(type1) {
                case 'Rich Text':
                    val1 = dis1 = CKEDITOR.instances['default_one_'+flid].getData();
                    break;
                case 'Boolean':
                    val1 = 0; dis1 = false;
                    if($('#default_one_'+flid).prop('checked') == true) {
                        val1 = 1; dis1 = true;
                    }
                    break;
                case 'Associator':
                case 'Multi-Select List':
                    val1 = JSON.stringify($('#default_one_'+flid).val());
                    dis1 = $('#default_one_'+flid).val().join(', ');
                    break;
                case 'Generated List':
                    val1 = JSON.stringify($('[name="default_one_'+flid+'[]"]').map((x, elm) => elm.value).get());
                    dis1 = $('[name="default_one_'+flid+'[]"]').map((x, elm) => elm.value).get().join(', ');
                    break;
                case 'Date':
                    monthOne = $('#month_default_one_'+flid).val(); dayOne = $('#day_default_one_'+flid).val(); yearOne = $('#year_default_one_'+flid).val();
                    val1 = dis1 = pad(yearOne,4) + '-' + pad(monthOne,2) + '-' + pad(dayOne,2);
                    break;
                case 'DateTime':
                    monthOne = $('#month_default_one_'+flid).val(); dayOne = $('#day_default_one_'+flid).val(); yearOne = $('#year_default_one_'+flid).val();
                    hourOne = $('#hour_default_one_'+flid).val(); minuteOne = $('#minute_default_one_'+flid).val(); secondOne = $('#second_default_one_'+flid).val();
                    val1 = dis1 = pad(yearOne,4) + '-' + pad(monthOne,2) + '-' + pad(dayOne,2) + ' '
                        + pad(hourOne,2) + ':' + pad(minuteOne,2) + ':' + pad(secondOne,2);
                    break;
                case 'Historical Date':
                    monthOne = $('#month_default_one_'+flid).val(); dayOne = $('#day_default_one_'+flid).val(); yearOne = $('#year_default_one_'+flid).val();
                    dateArray = [pad(yearOne,4)];
                    if(monthOne != '' && !$('#month_default_one_'+flid).is(":disabled")) {
                        dateArray.push(pad(monthOne,2));
                        if(dayOne != '' && !$('#day_default_one_'+flid).is(":disabled"))
                            dateArray.push(pad(dayOne,2));
                    }
                    dis1 = dateArray.join('-');
                    val1 = {'month': monthOne, 'day': dayOne, 'year': yearOne, 'era': '', 'prefix': ''};

                    eraDisplayOne = ''
                    $('.era_default_one_'+flid).each(function () {
                        if($(this).is(':checked')) {
                            eraDisplayOne = ' ' + $(this).val();
                            val1['era'] = $(this).val();
                        }
                    });
                    prefixDisplayOne = '';
                    $('.prefix_default_one_'+flid).each(function () {
                        if($(this).is(':checked')) {
                            prefixDisplayOne = $(this).val() + ' ';
                            val1['prefix'] = $(this).val();
                        }
                    });
                    dis1 = prefixDisplayOne + dis1 + eraDisplayOne;
                    val1 = JSON.stringify(val1);
                    break;
                default:
                    val1 = dis1 = $('#default_one_'+flid).val();
                    break;
            }

            switch(type2) {
                case 'Rich Text':
                    val2 = dis2 = CKEDITOR.instances['default_two_'+flid].getData();
                    break;
                case 'Boolean':
                    val2 = 0; dis2 = false;
                    if($('#default_two_'+flid).prop('checked') == true) {
                        val2 = 1; dis2 = true;
                    }
                    break;
                case 'Associator':
                case 'Multi-Select List':
                    val2 = JSON.stringify($('#default_two_'+flid).val());
                    dis2 = $('#default_two_'+flid).val().join(', ');
                    break;
                case 'Generated List':
                    val2 = JSON.stringify($('[name="default_two_'+flid+'[]"]').map((x, elm) => elm.value).get());
                    dis2 = $('[name="default_two_'+flid+'[]"]').map((x, elm) => elm.value).get().join(', ');
                    break;
                case 'Date':
                    monthTwo = $('#month_default_two_'+flid).val(); dayTwo = $('#day_default_two_'+flid).val(); yearTwo = $('#year_default_two_'+flid).val();
                    val2 = dis2 = pad(yearTwo,4) + '-' + pad(monthTwo,2) + '-' + pad(dayTwo,2);
                    break;
                case 'DateTime':
                    monthTwo = $('#month_default_two_'+flid).val(); dayTwo = $('#day_default_two_'+flid).val(); yearTwo = $('#year_default_two_'+flid).val();
                    hourTwo = $('#hour_default_two_'+flid).val(); minuteTwo = $('#minute_default_two_'+flid).val(); secondTwo = $('#second_default_two_'+flid).val();
                    val2 = dis2 = pad(yearTwo,4) + '-' + pad(monthTwo,2) + '-' + pad(dayTwo,2) + ' '
                        + pad(hourTwo,2) + ':' + pad(minuteTwo,2) + ':' + pad(secondTwo,2);
                    break;
                case 'Historical Date':
                    monthTwo = $('#month_default_two_'+flid).val(); dayTwo = $('#day_default_two_'+flid).val(); yearTwo = $('#year_default_two_'+flid).val();
                    dateArray = [pad(yearTwo,4)];
                    if(monthTwo != '' && !$('#month_default_two_'+flid).is(":disabled")) {
                        dateArray.push(pad(monthTwo,2));
                        if(dayTwo != '' && !$('#day_default_two_'+flid).is(":disabled"))
                            dateArray.push(pad(dayTwo,2));
                    }
                    dis2 = dateArray.join('-');
                    val2 = {'month': monthTwo, 'day': dayTwo, 'year': yearTwo, 'era': '', 'prefix': ''};

                    eraDisplayTwo = ''
                    $('.era_default_two_'+flid).each(function () {
                        if($(this).is(':checked')) {
                            eraDisplayTwo = ' ' + $(this).val();
                            val2['era'] = $(this).val();
                        }
                    });
                    prefixDisplayTwo = '';
                    $('.prefix_default_two_'+flid).each(function () {
                        if($(this).is(':checked')) {
                            prefixDisplayTwo = $(this).val() + ' ';
                            val2['prefix'] = $(this).val();
                        }
                    });
                    dis2 = prefixDisplayTwo + dis2 + eraDisplayTwo;
                    val2 = JSON.stringify(val2);
                    break;
                default:
                    val2 = dis2 = $('#default_two_'+flid).val();
                    break;
            }

            if(val1==null | val2==null) {
                $('.combo-error-'+flid+'-js').text('Both fields must be filled out');
            } else {
                $('.combo-error-'+flid+'-js').text('');

                if($comboValueDiv.find('.combo-list-empty').length)
                    $comboValueDiv.find('.combo-list-empty').first().remove();

                div = '<div class="combo-value-item combo-value-item-js">';
                div += '<span class="move-actions"><a class="action move-action-js up-js" href=""><i class="icon icon-arrow-up"></i></a><a class="action move-action-js down-js" href=""><i class="icon icon-arrow-down"></i></a></span>';
                div += '<input type="hidden" name="'+flid+'_combo_one[]" value="">';
                div += '<span class="combo-column">'+dis1+'</span>';
                div += '<input type="hidden" name="'+flid+'_combo_two[]" value="">';
                div += '<span class="combo-column">'+dis2+'</span>';
                div += '<span class="combo-delete delete-combo-value-js tooltip" tooltip="Delete Combo Value"><i class="icon icon-trash"></i></span>';
                div += '<span class="combo-edit edit-combo-value-js tooltip" tooltip="Edit Combo Value"><i class="icon icon-edit-little"></i></span>';
                div += '</div>';

                $comboCardContainer = $comboValueDiv.find('.combo-value-item-container-js');

                $comboCardContainer.append(div);
                $comboValueDiv.find('[name="'+flid+'_combo_one[]"]').last().val(val1);
                $comboValueDiv.find('[name="'+flid+'_combo_two[]"]').last().val(val2);
                console.log("stuff");

                initializeMoveAction($comboCardContainer.find('.combo-value-item-js'));
                Kora.Fields.TypedFieldInputs.Initialize();

                //Clear out entered default values
                switch(type1) {
                    case 'Rich Text':
                        CKEDITOR.instances['default_one_'+flid].setData('');
                        break;
                    case 'Boolean':
                        $('#default_one_'+flid).prop('checked', false);
                        break;
                    case 'Generated List':
                        $('.list-option-card-container-one-js').html('');
                        break;
                    case 'Date':
                    case 'DateTime':
                    case 'Historical Date':
                        $('#month_default_one_'+flid).val('');
                        $('#day_default_one_'+flid).val('');
                        $('#year_default_one_'+flid).val('');
                        $('#month_default_one_'+flid).trigger("chosen:updated");
                        $('#day_default_one_'+flid).trigger("chosen:updated");
                        $('#year_default_one_'+flid).trigger("chosen:updated");
                        break;
                    case 'List':
                    case 'Multi-Select List':
                    case 'Associator':
                        $('#default_one_'+flid).val('');
                        $('#default_one_'+flid).trigger("chosen:updated");
                        break;
                    default:
                        $('#'+flid+'default_one').val('');
                        $('#default_one_'+flid).val('');
                        break;
                }

                switch(type2) {
                    case 'Rich Text':
                        CKEDITOR.instances['default_two_'+flid].setData('');
                        break;
                    case 'Boolean':
                        $('#default_two_'+flid).prop('checked', false);
                        break;
                    case 'Generated List':
                        $('.list-option-card-container-two-js').html('');
                        break;
                    case 'Date':
                    case 'DateTime':
                    case 'Historical Date':
                        $('#month_default_two_'+flid).val('');
                        $('#day_default_two_'+flid).val('');
                        $('#year_default_two_'+flid).val('');
                        $('#month_default_two_'+flid).trigger("chosen:updated");
                        $('#day_default_two_'+flid).trigger("chosen:updated");
                        $('#year_default_two_'+flid).trigger("chosen:updated");
                        break;
                    case 'List':
                    case 'Multi-Select List':
                    case 'Associator':
                        $('#default_two_'+flid).val('');
                        $('#default_two_'+flid).trigger("chosen:updated");
                        break;
                    default:
                        $('#'+flid+'default_two').val('');
                        $('#default_two_'+flid).val('');
                        break;
                }

                Kora.Modal.close();
            }
        });

        ////THIS CODE IS FOR EDITING COMBOLIST VALUES///////////////////////////////////////////////////////////////////
        $('.combo-list-display-js').on('click', '.edit-combo-value-js', function() {
            $currentEditValue = $(this).closest('.combo-value-item-js').first();

            $comboContainer = $currentEditValue.closest('.combo-value-item-container-js').first();
            flid = $comboContainer.attr('flid');
            type1 = $comboContainer.attr('typeOne');
            type2 = $comboContainer.attr('typeTwo');

            $comboValueDiv = $('.combo-value-div-js-'+flid);
            var $modal = $comboValueDiv.find('.combo-list-edit-modal-js');

            var editVal1 = $currentEditValue.find('[name="'+flid+'_combo_one[]"]').val();
            var editVal2 = $currentEditValue.find('[name="'+flid+'_combo_two[]"]').val();

            switch(type1) {
                case 'Rich Text':
                    CKEDITOR.instances['default_one_edit_'+flid].setData(editVal1);
                    break;
                case 'Boolean':
                    if(editVal1)
                        $('#default_one_edit_'+flid).prop('checked', true);
                    else
                        $('#default_one_edit_'+flid).prop('checked', false);
                    break;
                case 'Associator':
                case 'Multi-Select List':
                    $('#default_one_edit_'+flid).val(JSON.parse(editVal1));
                    $('#default_one_edit_'+flid).trigger("chosen:updated");
                    break;
                case 'Generated List':
                    $('.list-option-card-container-one-js').html('');
                    $('[data-flid="default_one_edit_'+flid+'[]"]').val(JSON.parse(editVal1).join(','));
                    $('[data-flid="default_one_edit_'+flid+'[]"]').closest('.new-list-option-card-js').first().find('.list-option-add-js').click();
                    break;
                case 'Date':
                    dateParts = editVal1.split('-');
                    $('#month_default_one_edit_'+flid).val(dateParts[1]); $('#day_default_one_edit_'+flid).val(parseInt(dateParts[2])); $('#year_default_one_edit_'+flid).val(dateParts[0]);
                    $('#month_default_one_edit_'+flid).trigger("chosen:updated"); $('#day_default_one_edit_'+flid).trigger("chosen:updated"); $('#year_default_one_edit_'+flid).trigger("chosen:updated");
                    break;
                case 'DateTime':
                    dateParts = editVal1.split(' ')[0].split('-');
                    $('#month_default_one_edit_'+flid).val(dateParts[1]); $('#day_default_one_edit_'+flid).val(parseInt(dateParts[2])); $('#year_default_one_edit_'+flid).val(dateParts[0]);
                    $('#month_default_one_edit_'+flid).trigger("chosen:updated"); $('#day_default_one_edit_'+flid).trigger("chosen:updated"); $('#year_default_one_edit_'+flid).trigger("chosen:updated");
                    timeParts = editVal1.split(' ')[1].split(':');
                    $('#hour_default_one_edit_'+flid).val(parseInt(timeParts[0])); $('#minute_default_one_edit_'+flid).val(parseInt(timeParts[1])); $('#second_default_one_edit_'+flid).val(parseInt(timeParts[2]));
                    $('#hour_default_one_edit_'+flid).trigger("chosen:updated"); $('#minute_default_one_edit_'+flid).trigger("chosen:updated"); $('#second_default_one_edit_'+flid).trigger("chosen:updated");
                    break;
                case 'Historical Date':
                    dateParts = JSON.parse(editVal1);
                    $('#month_default_one_edit_'+flid).val(dateParts['month']); $('#day_default_one_edit_'+flid).val(dateParts['day']); $('#year_default_one_edit_'+flid).val(dateParts['year']);
                    $('#month_default_one_edit_'+flid).trigger("chosen:updated"); $('#day_default_one_edit_'+flid).trigger("chosen:updated"); $('#year_default_one_edit_'+flid).trigger("chosen:updated");

                    $('.prefix_default_one_edit_'+flid).prop('checked', false);
                    $('.prefix_default_one_edit_'+flid).each(function() {
                        if($(this).val()==dateParts['prefix'])
                            $(this).prop('checked', true);
                    });
                    $('.era_default_one_edit_'+flid).prop('checked', false);
                    $('.era_default_one_edit_'+flid).each(function() {
                        if($(this).val()==dateParts['era'])
                            $(this).prop('checked', true);
                    });
                    break;
                default:
                    $('#default_one_edit_'+flid).val(editVal1);
                    break;
            }

            switch(type2) {
                case 'Rich Text':
                    CKEDITOR.instances['default_two_edit_'+flid].setData(editVal2);
                    break;
                case 'Boolean':
                    if(editVal2)
                        $('#default_two_edit_'+flid).prop('checked', true);
                    else
                        $('#default_two_edit_'+flid).prop('checked', false);
                    break;
                case 'Associator':
                case 'Multi-Select List':
                    $('#default_two_edit_'+flid).val(JSON.parse(editVal2));
                    $('#default_two_edit_'+flid).trigger("chosen:updated");
                    break;
                case 'Generated List':
                    $('.list-option-card-container-two-js').html('');
                    $('[data-flid="default_two_edit_'+flid+'[]"]').val(JSON.parse(editVal2).join(','));
                    $('[data-flid="default_two_edit_'+flid+'[]"]').closest('.new-list-option-card-js').first().find('.list-option-add-js').click();
                    break;
                case 'Date':
                    dateParts = editVal2.split('-');
                    $('#month_default_two_edit_'+flid).val(dateParts[1]); $('#day_default_two_edit_'+flid).val(parseInt(dateParts[2])); $('#year_default_two_edit_'+flid).val(dateParts[0]);
                    $('#month_default_two_edit_'+flid).trigger("chosen:updated"); $('#day_default_two_edit_'+flid).trigger("chosen:updated"); $('#year_default_two_edit_'+flid).trigger("chosen:updated");
                    break;
                case 'DateTime':
                    dateParts = editVal2.split(' ')[0].split('-');
                    $('#month_default_two_edit_'+flid).val(dateParts[1]); $('#day_default_two_edit_'+flid).val(parseInt(dateParts[2])); $('#year_default_two_edit_'+flid).val(dateParts[0]);
                    $('#month_default_two_edit_'+flid).trigger("chosen:updated"); $('#day_default_two_edit_'+flid).trigger("chosen:updated"); $('#year_default_two_edit_'+flid).trigger("chosen:updated");
                    timeParts = editVal2.split(' ')[1].split(':');
                    $('#hour_default_two_edit_'+flid).val(parseInt(timeParts[0])); $('#minute_default_two_edit_'+flid).val(parseInt(timeParts[1])); $('#second_default_two_edit_'+flid).val(parseInt(timeParts[2]));
                    $('#hour_default_two_edit_'+flid).trigger("chosen:updated"); $('#minute_default_two_edit_'+flid).trigger("chosen:updated"); $('#second_default_two_edit_'+flid).trigger("chosen:updated");
                    break;
                case 'Historical Date':
                    dateParts = JSON.parse(editVal2);
                    $('#month_default_two_edit_'+flid).val(dateParts['month']); $('#day_default_two_edit_'+flid).val(dateParts['day']); $('#year_default_two_edit_'+flid).val(dateParts['year']);
                    $('#month_default_two_edit_'+flid).trigger("chosen:updated"); $('#day_default_two_edit_'+flid).trigger("chosen:updated"); $('#year_default_two_edit_'+flid).trigger("chosen:updated");

                    $('.prefix_default_two_edit_'+flid).prop('checked', false);
                    $('.prefix_default_two_edit_'+flid).each(function() {
                        if($(this).val()==dateParts['prefix'])
                            $(this).prop('checked', true);
                    });
                    $('.era_default_two_edit_'+flid).prop('checked', false);
                    $('.era_default_two_edit_'+flid).each(function() {
                        if($(this).val()==dateParts['era'])
                            $(this).prop('checked', true);
                    });
                    break;
                default:
                    $('#default_two_edit_'+flid).val(editVal2);
                    break;
            }

            Kora.Modal.close();
            Kora.Modal.open($modal);
        });

        $('.submit-edit-combo-js').click(function() {
            //Grab the default values entered
            switch(type1) {
                case 'Rich Text':
                    val1 = dis1 = CKEDITOR.instances['default_one_edit_'+flid].getData();
                    break;
                case 'Boolean':
                    val1 = 0; dis1 = false;
                    if($('#default_one_edit_'+flid).prop('checked') == true) {
                        val1 = 1; dis1 = true;
                    }
                    break;
                case 'Associator':
                case 'Multi-Select List':
                    val1 = JSON.stringify($('#default_one_edit_'+flid).val());
                    dis1 = $('#default_one_edit_'+flid).val().join(', ');
                    break;
                case 'Generated List':
                    val1 = JSON.stringify($('[name="default_one_edit_'+flid+'[]"]').map((x, elm) => elm.value).get());
                    dis1 = $('[name="default_one_edit_'+flid+'[]"]').map((x, elm) => elm.value).get().join(', ');
                    break;
                case 'Date':
                    monthOne = $('#month_default_one_edit_'+flid).val(); dayOne = $('#day_default_one_edit_'+flid).val(); yearOne = $('#year_default_one_edit_'+flid).val();
                    val1 = dis1 = pad(yearOne,4) + '-' + pad(monthOne,2) + '-' + pad(dayOne,2);
                    break;
                case 'DateTime':
                    monthOne = $('#month_default_one_edit_'+flid).val(); dayOne = $('#day_default_one_edit_'+flid).val(); yearOne = $('#year_default_one_edit_'+flid).val();
                    hourOne = $('#hour_default_one_edit_'+flid).val(); minuteOne = $('#minute_default_one_edit_'+flid).val(); secondOne = $('#second_default_one_edit_'+flid).val();
                    val1 = dis1 = pad(yearOne,4) + '-' + pad(monthOne,2) + '-' + pad(dayOne,2) + ' '
                        + pad(hourOne,2) + ':' + pad(minuteOne,2) + ':' + pad(secondOne,2);
                    break;
                case 'Historical Date':
                    monthOne = $('#month_default_one_edit_'+flid).val(); dayOne = $('#day_default_one_edit_'+flid).val(); yearOne = $('#year_default_one_edit_'+flid).val();
                    dateArray = [pad(yearOne,4)];
                    if(monthOne != '' && !$('#month_default_one_edit_'+flid).is(":disabled")) {
                        dateArray.push(pad(monthOne,2));
                        if(dayOne != '' && !$('#day_default_one_edit_'+flid).is(":disabled"))
                            dateArray.push(pad(dayOne,2));
                    }
                    dis1 = dateArray.join('-');
                    val1 = {'month': monthOne, 'day': dayOne, 'year': yearOne, 'era': '', 'prefix': ''};

                    eraDisplayOne = ''
                    $('.era_default_one_edit_'+flid).each(function () {
                        if($(this).is(':checked')) {
                            eraDisplayOne = ' ' + $(this).val();
                            val1['era'] = $(this).val();
                        }
                    });
                    prefixDisplayOne = '';
                    $('.prefix_default_one_edit_'+flid).each(function () {
                        if($(this).is(':checked')) {
                            prefixDisplayOne = $(this).val() + ' ';
                            val1['prefix'] = $(this).val();
                        }
                    });
                    dis1 = prefixDisplayOne + dis1 + eraDisplayOne;
                    val1 = JSON.stringify(val1);
                    break;
                default:
                    val1 = dis1 = $('#default_one_edit_'+flid).val();
                    break;
            }

            switch(type2) {
                case 'Rich Text':
                    val2 = dis2 = CKEDITOR.instances['default_two_edit_'+flid].getData();
                    break;
                case 'Boolean':
                    val2 = 0; dis2 = false;
                    if($('#default_two_edit_'+flid).prop('checked') == true) {
                        val2 = 1; dis2 = true;
                    }
                    break;
                case 'Associator':
                case 'Multi-Select List':
                    val2 = JSON.stringify($('#default_two_edit_'+flid).val());
                    dis2 = $('#default_two_edit_'+flid).val().join(', ');
                    break;
                case 'Generated List':
                    val2 = JSON.stringify($('[name="default_two_edit_'+flid+'[]"]').map((x, elm) => elm.value).get());
                    dis2 = $('[name="default_two_edit_'+flid+'[]"]').map((x, elm) => elm.value).get().join(', ');
                    break;
                case 'Date':
                    monthTwo = $('#month_default_two_edit_'+flid).val(); dayTwo = $('#day_default_two_edit_'+flid).val(); yearTwo = $('#year_default_two_edit_'+flid).val();
                    val2 = dis2 = pad(yearTwo,4) + '-' + pad(monthTwo,2) + '-' + pad(dayTwo,2);
                    break;
                case 'DateTime':
                    monthTwo = $('#month_default_two_edit_'+flid).val(); dayTwo = $('#day_default_two_edit_'+flid).val(); yearTwo = $('#year_default_two_edit_'+flid).val();
                    hourTwo = $('#hour_default_two_edit_'+flid).val(); minuteTwo = $('#minute_default_two_edit_'+flid).val(); secondTwo = $('#second_default_two_edit_'+flid).val();
                    val2 = dis2 = pad(yearTwo,4) + '-' + pad(monthTwo,2) + '-' + pad(dayTwo,2) + ' '
                        + pad(hourTwo,2) + ':' + pad(minuteTwo,2) + ':' + pad(secondTwo,2);
                    break;
                case 'Historical Date':
                    monthTwo = $('#month_default_two_edit_'+flid).val(); dayTwo = $('#day_default_two_edit_'+flid).val(); yearTwo = $('#year_default_two_edit_'+flid).val();
                    dateArray = [pad(yearTwo,4)];
                    if(monthTwo != '' && !$('#month_default_two_edit_'+flid).is(":disabled")) {
                        dateArray.push(pad(monthTwo,2));
                        if(dayTwo != '' && !$('#day_default_two_edit_'+flid).is(":disabled"))
                            dateArray.push(pad(dayTwo,2));
                    }
                    dis2 = dateArray.join('-');
                    val2 = {'month': monthTwo, 'day': dayTwo, 'year': yearTwo, 'era': '', 'prefix': ''};

                    eraDisplayTwo = ''
                    $('.era_default_two_edit_'+flid).each(function () {
                        if($(this).is(':checked')) {
                            eraDisplayTwo = ' ' + $(this).val();
                            val2['era'] = $(this).val();
                        }
                    });
                    prefixDisplayTwo = '';
                    $('.prefix_default_two_edit_'+flid).each(function () {
                        if($(this).is(':checked')) {
                            prefixDisplayTwo = $(this).val() + ' ';
                            val2['prefix'] = $(this).val();
                        }
                    });
                    dis2 = prefixDisplayTwo + dis2 + eraDisplayTwo;
                    val2 = JSON.stringify(val2);
                    break;
                default:
                    val2 = dis2 = $('#default_two_edit_'+flid).val();
                    break;
            }

            if(val1==null | val2==null) {
                $('.combo-error-'+flid+'-js').text('Both fields must be filled out');
            } else {
                $('.combo-error-'+flid+'-js').text('');

                $comboCardContainer = $comboValueDiv.find('.combo-value-item-container-js');

                $currentEditValue.find('[name="'+flid+'_combo_one[]"]').val(val1);
                $currentEditValue.find('[name="'+flid+'_combo_one[]"]').first().next().text(dis1);
                $currentEditValue.find('[name="'+flid+'_combo_two[]"]').val(val2);
                $currentEditValue.find('[name="'+flid+'_combo_two[]"]').first().next().text(dis2);

                initializeMoveAction($comboCardContainer.find('.combo-value-item-js'));
                Kora.Fields.TypedFieldInputs.Initialize();

                //Clear out entered default values
                switch(type1) {
                    case 'Rich Text':
                        CKEDITOR.instances['default_one_edit_'+flid].setData('');
                        break;
                    case 'Boolean':
                        $('#default_one_edit_'+flid).prop('checked', false);
                        break;
                    case 'Generated List':
                        $('.list-option-card-container-one-js').html('');
                        break;
                    case 'Date':
                    case 'DateTime':
                    case 'Historical Date':
                        $('#month_default_one_edit_'+flid).val('');
                        $('#day_default_one_edit_'+flid).val('');
                        $('#year_default_one_edit_'+flid).val('');
                        $('#month_default_one_edit_'+flid).trigger("chosen:updated");
                        $('#day_default_one_edit_'+flid).trigger("chosen:updated");
                        $('#year_default_one_edit_'+flid).trigger("chosen:updated");
                        break;
                    case 'List':
                    case 'Multi-Select List':
                    case 'Associator':
                        $('#default_one_edit_'+flid).val('');
                        $('#default_one_edit_'+flid).trigger("chosen:updated");
                        break;
                    default:
                        $('#'+flid+'default_one_edit').val('');
                        $('#default_one_edit_'+flid).val('');
                        break;
                }

                switch(type2) {
                    case 'Rich Text':
                        CKEDITOR.instances['default_two_edit_'+flid].setData('');
                        break;
                    case 'Boolean':
                        $('#default_two_edit_'+flid).prop('checked', false);
                        break;
                    case 'Generated List':
                        $('.list-option-card-container-two-js').html('');
                        break;
                    case 'Date':
                    case 'DateTime':
                    case 'Historical Date':
                        $('#month_default_two_edit_'+flid).val('');
                        $('#day_default_two_edit_'+flid).val('');
                        $('#year_default_two_edit_'+flid).val('');
                        $('#month_default_two_edit_'+flid).trigger("chosen:updated");
                        $('#day_default_two_edit_'+flid).trigger("chosen:updated");
                        $('#year_default_two_edit_'+flid).trigger("chosen:updated");
                        break;
                    case 'List':
                    case 'Multi-Select List':
                    case 'Associator':
                        $('#default_two_edit_'+flid).val('');
                        $('#default_two_edit_'+flid).trigger("chosen:updated");
                        break;
                    default:
                        $('#'+flid+'default_two_edit').val('');
                        $('#default_two_edit_'+flid).val('');
                        break;
                }

                Kora.Modal.close();
            }
        });

        function pad(num, size) {
            var s = num+"";
            while (s.length < size) s = "0" + s;
            return s;
        }
    }

    function initializeDateOptions() {
        var $dateFormGroups = $('.date-input-form-group-js');
        var $dateListInputs = $dateFormGroups.find('.chosen-container');
        var scrollBarWidth = 17;

        $prefixCheckboxes = $('.prefix-check-js');
        $eraCheckboxes = $('.era-check-js');

        $prefixCheckboxes.click(function() {
            var $selected = $(this);
            flid = $selected.attr('flid');
            $isChecked = $selected.prop('checked');

            $('.prefix-check-'+flid+'-js').prop('checked', false);
            if($isChecked)
                $selected.prop('checked', true);
        });
        $eraCheckboxes.click(function() {
            var $selected = $(this);
            flid = $selected.attr('flid');

            $('.era-check-'+flid+'-js').prop('checked', false);
            $selected.prop('checked', true);

            currEra = $selected.val();
            $month = $('#month_'+flid);
            $day = $('#day_'+flid);

            // Combolist historic specific attribute
            var fnum = $(this).attr('fnum');
            if (typeof fnum !== typeof undefined && fnum !== false) {
                $month = $(`#default_month_${fnum}_${flid}`);
                $day = $(`#default_day_${fnum}_${flid}`);
            }

            if(currEra=='BP' | currEra=='KYA BP') {
                $month.attr('disabled','disabled');
                $day.attr('disabled','disabled');
                $month.trigger("chosen:updated");
                $day.trigger("chosen:updated");
            } else {
                $month.removeAttr('disabled');
                $day.removeAttr('disabled');
                $month.trigger("chosen:updated");
                $day.trigger("chosen:updated");
            }
        });

        //setTextInputWidth();

        //$(window).resize(setTextInputWidth);

        function setTextInputWidth() {
            if ($(window).outerWidth() < 1175 - scrollBarWidth) {
                // Window is small, full width Inputs
                $dateListInputs.css('width', '100%');
                $dateListInputs.css('margin-bottom', '10px');
            } else {
                // Window is large, 1/3 width Inputs
                $dateListInputs.css('width', '33%');
                $dateListInputs.css('margin-bottom', '');
            }
        }
    }

    function intializeGeolocatorOptions() {
        Kora.Modal.initialize();
        var flid = '';
        var geoListDisplay = '';

        var $geoCardContainers = $('.geolocator-card-container-js');
        var $geoCards = $geoCardContainers.find('.geolocator-card-js');
        var $newLocationButtons = $('.add-new-default-location-js');

        // Action arrows on the cards
        initializeMoveAction($geoCards);

        // Drag cards to sort
        $geoCardContainers.sortable();

        // Delete card
        initializeDelete();

        $newLocationButtons.click(function(e) {
            e.preventDefault();

            flid = $(this).attr('flid');
            geoListDisplay = $(this).attr('display-type');

            Kora.Modal.open($('.geolocator-add-location-modal-js'));
        });

        $('.location-type-js').on('change', function(e) {
            newType = $(this).val();
            if(newType=='LatLon') {
                $('.lat-lon-switch-js').removeClass('hidden');
                $('.utm-switch-js').addClass('hidden');
                $('.address-switch-js').addClass('hidden');
            } else if(newType=='Address') {
                $('.lat-lon-switch-js').addClass('hidden');
                $('.utm-switch-js').addClass('hidden');
                $('.address-switch-js').removeClass('hidden');
            }
        });

        $('.add-new-location-js').click(function(e) {
            e.preventDefault();

            $('.error-message').text('');
            $('.text-input, .text-area, .cke, .chosen-container').removeClass('error');

            var type = $('.location-type-js').val();

            //determine if info is good for that type
            var valid = true;
            if(type == 'LatLon') {
                var lat = $('.location-lat-js').val();
                var lon = $('.location-lon-js').val();

                if(lat == '') {
                    $geoError = $('.location-lat-js');
                    $geoError.addClass('error');
                    $geoError.siblings('.error-message').text('Latitude value required');
                    valid = false;
                }

                if(lon == '') {
                    $geoError = $('.location-lon-js');
                    $geoError.addClass('error');
                    $geoError.siblings('.error-message').text('Longitude value required');
                    valid = false;
                }
            } else if(type == 'Address') {
                var addr = $('.location-addr-js').val();

                if(addr == '') {
                    $geoError = $('.location-addr-js');
                    $geoError.addClass('error');
                    $geoError.siblings('.error-message').text('Location address required');
                    valid = false;
                }
            }

            //if still valid
            if(valid) {
                //find info for other loc types
                if(type == 'LatLon')
                    coordinateConvert({"_token": csrfToken,type:'latlon',lat:lat,lon:lon});
                else if(type == 'Address')
                    coordinateConvert({"_token": csrfToken,type:'geo',addr:addr});

                $('.location-lat-js').val(''); $('.location-lon-js').val('');
                $('.location-addr-js').val('');
            }
        });

        function coordinateConvert(data) {
            $.ajax({
                url: geoConvertUrl,
                type: 'POST',
                data: data,
                success:function(result) {
                    // Get Values
                    var desc = $('.location-desc-js').val();
                    result['description'] = desc;
                    var latlon = result['geometry']['location']['lat']+', '+result['geometry']['location']['lng'];
                    var addr = result['formatted_address'];

                    finalResult = JSON.stringify(result).replace(/"/g, '&quot;');

                    // Create and display new geolocation card
                    var newCardHtml = '<div class="card geolocator-card geolocator-card-js">' +
                        '<input type="hidden" class="list-option-js" name="'+flid+'[]" value="' + finalResult + '">' +
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
                        '<span class="title">' + desc + '</span>' +
                        '</div>' +
                        '<div class="card-toggle-wrap">' +
                        '<a class="geolocator-delete geolocator-delete-js tooltip" tooltip="Delete Location" href=""><i class="icon icon-trash"></i></a>' +
                        '</div></div>' +
                        '<div class="content">';

                    if(geoListDisplay=='LatLon')
                        newCardHtml += '<p class="location"><span class="bold">Lat Long:</span> '+ latlon +'</p>' + '</div></div>';
                    else if(geoListDisplay=='Address')
                        newCardHtml += '<p class="location"><span class="bold">Address:</span> '+ addr +'</p>' + '</div></div>';

                    var $geoCardContainer = $('.geolocator-'+flid+'-js').find('.geolocator-card-container-js');
                    $geoCardContainer.append(newCardHtml);

                    initializeMoveAction($geoCardContainer.find('.geolocator-card-js'));
                    initializeDelete();
                    Kora.Fields.TypedFieldInputs.Initialize();

                    $('.location-desc-js').val('');
                    Kora.Modal.close($('.geolocator-add-location-modal-js'));
                }
            });
        }

        function initializeDelete() {
            $geoCardContainers.find('.geolocator-card-js').each(function() {
                var $card = $(this);
                var $deleteButton = $card.find('.geolocator-delete-js');

                $deleteButton.unbind();
                $deleteButton.click(function(e) {
                    e.preventDefault();

                    $card.remove();
                })
            });
        }
    }

    function intializeFileUploaderOptions() {
        var $fileUploads = $('.kora-file-upload-js');
        var $fileCardsContainer = $fileUploads.parent().find('.file-cards-container-js');
        //We will capture the current field when we start to upload. That way when we do upload, it's guarenteed to be that Field ID
        var lastClickedFlid = 0;

        // Prevents upload to whole web page
        $(document).bind('drop dragover', function (e) {
            e.preventDefault();
        });

        $fileUploads.each(function() {
            var $fileUpload = $(this);
            console.log("file upload: "+$fileUpload);
            $('#'+$fileUpload.attr('id')).fileupload({
                dataType: 'json',
                dropZone: $('#'+$fileUpload.attr('id')).parent(),
                singleFileUploads: false,
                done: function (e, data) {
                    var $uploadInput = $(this);
                    lastClickedFlid = $uploadInput.attr('flid');
                    console.log(lastClickedFlid);
                    inputName = 'file'+lastClickedFlid;
                    capName = 'file_captions'+lastClickedFlid;
                    fileDiv = ".filenames-"+lastClickedFlid+"-js";

                    var $formGroup = $uploadInput.parent('.form-group');

                    // Tooltip text
                    var tooltip = "Remove Document";
                    if ($formGroup.hasClass('gallery-input-form-group')) {
                        tooltip = "Remove Image";
                    } else if ($formGroup.hasClass('video-input-form-group')) {
                        tooltip = "Remove Video";
                    } else if ($formGroup.hasClass('audio-input-form-group')) {
                        tooltip = "Remove Audio";
                    } else if ($formGroup.hasClass('3d-model-input-form-group')) {
                        tooltip = "Remove 3D Model";
                    }

                    $uploadInput.removeClass('error');
                    $uploadInput.siblings('.error-message').text('');
                    $.each(data.result[inputName], function (index, file) {
                        if(file.error == "" || !file.hasOwnProperty('error')) {
                            // File card html
                            var fileCardHtml = '<div class="card file-card file-card-js">' +
                                '<input type="hidden" name="' + lastClickedFlid + '[]" value ="' + file.name + '">' +
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
                                '<span class="title">' + file.name + '</span>' +
                                '</div>' +
                                '<div class="card-toggle-wrap">' +
                                '<a href="#" class="file-delete upload-filedelete-js ml-sm tooltip" tooltip="'+tooltip+'" data-url="' + file.deleteUrl + '">' +
                                '<i class="icon icon-trash danger"></i>' +
                                '</a>' +
                                '</div>' +
                                '<textarea type="text" name="' + capName + '[]" class="caption autosize-js" placeholder="Enter caption here"></textarea>' +
                                '</div>' +
                                '</div>';
                            console.log(fileCardHtml);

                            // Add file card to list of cards
                            $formGroup.find(fileDiv).append(fileCardHtml);

                            // Change directions text
                            $formGroup.find('.directions-empty-js').removeClass('active');
                            $formGroup.find('.directions-not-empty-js').addClass('active');

                            // Reinitialize inputs
                            Kora.Fields.TypedFieldInputs.Initialize();
                            Kora.Inputs.Textarea();
                        } else {
                            $field.addClass('error');
                            $field.siblings('.error-message').text(file.error);
                            return false;
                        }
                    });

                    //Reset progress bar
                    var progressBar = '.progress-bar-'+lastClickedFlid+'-js';
                    $formGroup.find(progressBar).css(
                        {"width": 0, "height": 0, "margin-top": 0}
                    );
                },
                fail: function (e,data){
                    var $uploadInput = $(this);
                    var $errorMessage = $uploadInput.siblings('.error-message');

                    var error = data.jqXHR['responseText'];
                    lastClickedFlid = $uploadInput.attr('flid');

                    var $field = $uploadInput.siblings('#'+lastClickedFlid);

                    $field.removeClass('error');
                    $field.siblings('.error-message').text('');
                    if(error=='InvalidFileNames'){
                        $field.addClass('error');
                        $errorMessage.text('Filename not supported (filenames limited to a-z, A-Z, 0-9, ., -, and _ )');
                    } else if(error=='InvalidType'){
                        $field.addClass('error');
                        $errorMessage.text('Invalid file type provided');
                    } else if(error=='TooManyFiles'){
                        $field.addClass('error');
                        $errorMessage.text('Max file limit was reached');
                    } else if(error=='MaxSizeReached'){
                        $field.addClass('error');
                        $errorMessage.text('One or more uploaded files is bigger than limit');
                    } else {
                        $field.addClass('error');
                        $errorMessage.text('Error uploading file');
                    }
                },
                progressall: function (e, data) {
                    var $uploadInput = $(this);
                    var $formGroup = $uploadInput.parent();
                    var progressBar = '.progress-bar-'+lastClickedFlid+'-js';
                    var progress = parseInt(data.loaded / data.total * 100, 10);

                    $formGroup.find(progressBar).css(
                        {"width": progress + '%', "height": '18px', "margin-top": '10px'}
                    );
                }
            });
        });

        $fileCardsContainer.on('click', '.upload-filedelete-js', function(e) {
            e.preventDefault();

            var $fileCard = $(this).parent().parent().parent('.file-card-js');
            var $container = $fileCard.parent();

            $.ajax({
                url: $(this).attr('data-url'),
                type: 'POST',
                dataType: 'json',
                data: {
                    "_token": csrfToken,
                    "_method": 'delete'
                },
                success: function (data) {
                    $fileCard.remove();

                    // Change directions text
                    if ($fileCardsContainer.children().length > 0) {
                        $container.siblings('.directions-empty-js').removeClass('active');
                        $container.siblings('.directions-not-empty-js').addClass('active');
                    } else {
                        $container.siblings('.directions-empty-js').addClass('active');
                        $container.siblings('.directions-not-empty-js').removeClass('active');
                    }
                }
            });
        });

        // Move file card up
        $fileCardsContainer.on('click', '.up-js', function(e) {
            e.preventDefault();

            var $fileCard = $(this).parent().parent().parent().parent('.file-card-js');

            if ($fileCard.prev('.file-card-js').length == 1) {
                var $prevCard = $fileCard.prev('.file-card-js');

                $fileCard.insertBefore($prevCard);
            }
        });

        // Move file card down
        $fileCardsContainer.on('click', '.down-js', function(e) {
            e.preventDefault();

            var $fileCard = $(this).parent().parent().parent().parent('.file-card-js');

            if ($fileCard.next('.file-card-js').length == 1) {
                var $nextCard = $fileCard.next('.file-card-js');

                $fileCard.insertAfter($nextCard);
            }
        });

        // Drag file cards to reorder
        $fileCardsContainer.sortable();

        Kora.Fields.TypedFieldInputs.Initialize();
        Kora.Inputs.Textarea();
    }

    function initializePageNavigation() {
        $('.page-section-js').first().removeClass('hidden');
        $('.toggle-by-name').first().addClass('active');

        $('.toggle-by-name').click(function(e) {
            e.preventDefault();

            $this = $(this);
            $this.addClass('active');
            $this.siblings().removeClass('active');

            var pageNumber = $this.parent().children().index(this);
            var $pageLinks = $('.pagination .pages .page-link');

            $pageLinks.removeClass('active');
            $($pageLinks.get(pageNumber)).addClass('active');

            if (pageNumber === 0) {
                $('.previous.page').addClass('disabled');
            } else {
                $('.previous.page').removeClass('disabled');
            }

            if (pageNumber === $pageLinks.length - 1) {
                $('.next.page').addClass('disabled');
            } else {
                $('.next.page').removeClass('disabled');
            }

            $active = $this.attr("href");
            $('.page-section-js').each(function() {
                if($(this).attr('id') == $active)
                    $(this).removeClass('hidden');
                else
                    $(this).addClass('hidden');
            });
        });

        $('.page-link').click(function(e) {
            e.preventDefault();

            var pageNumber = $(this).text() - 1;
            $('.toggle-by-name').get(pageNumber).click();
        });

        $('.pagination .page').click(function(e) {
            e.preventDefault();

            var pageNumber = $('.pagination .pages .page-link.active').index();

            if ($(this).hasClass('next')) {
                $('.toggle-by-name').get(pageNumber + 1).click();
            } else if ($(this).hasClass('previous')) {
                $('.toggle-by-name').get(pageNumber - 1).click();
            }
        })
    }

    function initializeRecordPresets() {
        $('.preset-check-js').click(function() {
            var presetDiv = $('.preset-record-div-js');
            if(this.checked) {
                presetDiv.fadeIn();
            } else {
                presetDiv.hide();
                //CLEAR FIELDS
                $('.preset-clear-text-js').each(function(){ $(this).val(''); });
                $('.preset-clear-chosen-js').each(function(){ $(this).val(''); $(this).trigger("chosen:updated"); });
                $('.preset-clear-file-js').html('');
                $('.preset-clear-combo-js').each(function(){ $('.combo-value-item-js',this).remove(); });
            }
        });

        $('.preset-record-js').change(function() {
            $.ajax({
                url: getPresetDataUrl,
                type: 'POST',
                data: {
                    '_token': csrfToken,
                        'id': $(this).val()
                }, success: function(response) {
                    putArray(response);
                }
            });
        });

        function putArray(ary) {
            var data = ary['data'];
            var fields = ary['fields'];
            var presetID = $('.preset-record-js').val();

            moveFiles(presetID);

            for(var flid in data) {
                value = data[flid];
                type = fields[flid]['type'];

                if(value != null) {
                    switch (type) {
                        case 'Text':
                            $('[name=' + flid + ']').val(value);
                            break;
                        case 'Rich Text':
                            CKEDITOR.instances[flid].setData(value);
                            break;
                        case 'Integer':
                            $('[name=' + flid + ']').val(value);
                            break;
                        case 'Float':
                            $('[name=' + flid + ']').val(value);
                            break;
                        case 'List':
                            $('[name=' + flid + ']').val(value).trigger("chosen:updated");
                            break;
                        case 'Multi-Select List':
                            $('#list' + flid).val(JSON.parse(value)).trigger("chosen:updated");
                            break;
                        case 'Generated List':
                            var options = JSON.parse(value);
                            var valArray = [];
                            var h = 0;
                            var selector = $("#list" + flid);

                            $('#' + flid + ' option[value!="0"]').remove();
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
                            selector.val(valArray).trigger("chosen:updated");
                            break;
                        case 'Date':
                            var date = moment(value);
                            var month = ("0" + (date.month()+1) ).slice(-2);

                            $('[name=month_' + flid + ']').val(month).trigger("chosen:updated");
                            $('[name=day_' + flid + ']').val(date.date()).trigger("chosen:updated");
                            $('[name=year_' + flid + ']').val(date.year()).trigger("chosen:updated");
                            break;
                        case 'DateTime':
                            var date = moment(value);
                            var month = ("0" + (date.month()+1) ).slice(-2);

                            $('[name=month_' + flid + ']').val(month).trigger("chosen:updated");
                            $('[name=day_' + flid + ']').val(date.date()).trigger("chosen:updated");
                            $('[name=year_' + flid + ']').val(date.year()).trigger("chosen:updated");
                            $('[name=hour_' + flid + ']').val(date.hour()).trigger("chosen:updated");
                            $('[name=minute_' + flid + ']').val(date.minute()).trigger("chosen:updated");
                            $('[name=second_' + flid + ']').val(date.second()).trigger("chosen:updated");
                            break;
                        case 'Historical Date':
                            var date = JSON.parse(value);

                            $('[name=prefix_' + flid + ']').val(date['prefix']).trigger("chosen:updated");
                            $('[name=month_' + flid + ']').val(date['month']).trigger("chosen:updated");
                            $('[name=day_' + flid + ']').val(date['day']).trigger("chosen:updated");
                            $('[name=year_' + flid + ']').val(date['year']).trigger("chosen:updated");
                            $('[name=era_' + flid + ']').val(date['era']).trigger("chosen:updated");
                            break;
                        case 'Boolean':
                            if(value)
                                $('[name=' + flid + ']').prop('checked', true);
                            break;
                        case 'Geolocator':
                            var locations = JSON.parse(value);
                            var geoDiv = $('.geolocator-' + flid + '-js').find('.geolocator-card-container-js');
                            var viewType = fields[flid]['options']['DataView'];

                            locations.forEach(function (location, index) {
                                geoDiv.append(geoDivHTML(location,flid,viewType));
                            });

                            break;
                        case 'Associator':
                            var r, records = JSON.parse(value);

                            var selector = $('#' + flid);
                            $('#' + flid + ' option[value!="0"]').remove();

                            for (r = 0; r < records.length; r++) {
                                selector.append($('<option/>', {
                                    value: records[r],
                                    text: records[r],
                                    selected: 'selected'
                                })).trigger("chosen:updated");
                            }
                            break;
                        case 'Documents':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Document'));
                            });

                            break;
                        case 'Gallery':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Image'));
                            });

                            break;
                        case 'Playlist':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Audio'));
                            });

                            break;
                        case 'Video':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Video'));
                            });

                            break;
                        case '3D-Model':
                            var files = JSON.parse(value);
                            var fileDiv = $('.filenames-' + flid + '-js');

                            files.forEach(function (file, index) {
                                fileDiv.append(fileDivHTML(file, flid, 'Model File'));
                            });

                            break;
                        case 'Combo List':
                            var comboDiv = $('.combo-value-div-js-' + flid + ' .combo-value-item-container-js');
                            comboDiv.html('');

                            value.forEach(function (cVal, index) {
                                comboDiv.append(
                                    '<div class="combo-value-item combo-value-item-js">' +
                                    '<span class="combo-delete delete-combo-value-js tooltip" tooltip="Delete Combo Value"><i class="icon icon-trash"></i></span>' +
                                    '<input type="hidden" name="' + flid + '_combo_one[]" value="">' +
                                    '<span class="combo-column combo-value">' + cVal['cfDisOne'] + '</span>' +
                                    '<input type="hidden" name="' + flid + '_combo_two[]" value="">' +
                                    '<span class="combo-column combo-value">' + cVal['cfDisTwo'] + '</span>' +
                                    '</div>'
                                );

                                comboDiv.find('[name="'+flid+'_combo_one[]"]').last().val(cVal['cfOne']);
                                comboDiv.find('[name="'+flid+'_combo_two[]"]').last().val(cVal['cfTwo']);
                            });

                            break;
                    }
                }
            }
        }

        //Move files from preset to tmp directory
        function moveFiles(presetID) {
            $.ajax({
                url: moveFilesUrl,
                type: 'POST',
                data: {
                    '_token': csrfToken,
                    'presetID': presetID
                }
            });
        }

        /**
         * Generates the HTML for an geolocator's div.
         */
        function geoDivHTML(location, flid, viewType) {
            var desc = location['description'];
            var latlon = location['geometry']['location']['lat']+', '+location['geometry']['location']['lng'];
            var address = location['formatted_address'];
            var finalResult = JSON.stringify(location);

            var HTML = '<div class="card geolocator-card geolocator-card-js">';
            HTML += '<input type="hidden" class="list-option-js" name="'+flid+'[]" value="'+finalResult+'">';
            HTML += '<div class="header">';
            HTML += '<div class="left">';
            HTML += '<div class="move-actions">';
            HTML += '<a class="action move-action-js up-js" href=""><i class="icon icon-arrow-up"></i></a>';
            HTML += '<a class="action move-action-js down-js" href=""><i class="icon icon-arrow-down"></i></a>';
            HTML += '</div>';
            HTML += '<span class="title">'+desc+'</span>';
            HTML += '</div>';
            HTML += '<div class="card-toggle-wrap">';
            HTML += '<a class="geolocator-delete geolocator-delete-js tooltip" tooltip="Delete Location" href=""><i class="icon icon-trash"></i></a>';
            HTML += '</div>';
            HTML += '</div>';
            if(viewType == 'LatLon')
                HTML += '<div class="content"><p class="location"><span class="bold">LatLon:</span> '+latlon+'</p></div>';
            else if(viewType == 'Address')
                HTML += '<div class="content"><p class="location"><span class="bold">Address:</span> '+address+'</p></div>';
            HTML += '</div>';

            return HTML;
        }

        /**
         * Generates the HTML for an uploaded file's div.
         */
        function fileDivHTML(file, flid, btnName) {
            var name = file['name'];
            var caption = file['caption'];
            deleteUrl = deleteFileUrl+flid+"/"+tmpFileDir+"/"+name;

            var HTML = '<div class="card file-card file-card-js">';
            HTML += '<input type="hidden" name="'+flid+'[]" value="'+name+'">';
            HTML += '<div class="header">';
            HTML += '<div class="left">';
            HTML += '<div class="move-actions">';
            HTML += '<a class="action move-action-js up-js" href=""><i class="icon icon-arrow-up"></i></a>';
            HTML += '<a class="action move-action-js down-js" href=""><i class="icon icon-arrow-down"></i></a>';
            HTML += '</div>';
            HTML += '<span class="title">'+name+'</span>';
            HTML += '</div>';
            HTML += '<div class="card-toggle-wrap">';
            HTML += '<a href="#" class="file-delete upload-filedelete-js ml-sm tooltip" tooltip="Remove '+btnName+'" data-url="'+deleteUrl+'">';
            HTML += '<i class="icon icon-trash danger"></i>';
            HTML += '</a>';
            HTML += '</div>';
            HTML += '<textarea type="text" name="file_captions'+flid+'[]" class="caption autosize-js" placeholder="Enter caption here">'+caption+'</textarea>';
            HTML += '</div>';
            HTML += '</div>';

            return HTML;
        }
    }

    function initializeDuplicateRecord() {
        //The one that matters during execution
        $('.duplicate-check-js').click(function() {
            var duplicateDiv = $('.duplicate-record-js');
            var input = duplicateDiv.find('input').first();
            if(this.checked) {
                duplicateDiv.fadeIn();
                input.removeAttr('disabled');
            } else {
                duplicateDiv.hide();
                input.attr('disabled','disabled');
            }
        });
    }

    function initializeNewRecordPreset() {
        //The one that matters during execution
        $('.newRecPre-check-js').click(function() {
            var newRecPreDiv = $('.newRecPre-record-js');
            var input = newRecPreDiv.children('input').first();
            if(this.checked) {
                newRecPreDiv.fadeIn();
                input.removeAttr('disabled');
            } else {
                newRecPreDiv.hide();
                input.attr('disabled','disabled');
            }
        });
    }

    function multiSelectPlaceholders () {
      var inputDef = $('.chosen-container-multi').children('.chosen-choices');

      inputDef.on('click', function() {
        var childCheck = $(this).siblings('.chosen-drop').children('.chosen-results');
        if (childCheck.children().length === 0) {
          childCheck.append('<li class="no-results">No options to select!</li>');
        } else if (childCheck.children('.active-result').length === 0 && childCheck.children('.no-results').length === 0) {
          childCheck.append('<li class="no-results">No more options to select!</li>');
        }
      });

      inputDef.children('.search-field').children('input').blur(function() {
        var childCheck = inputDef.siblings('.chosen-drop').children('.chosen-results');
        if (childCheck.children('.no-results').length > 0) {
          childCheck.children('.no-results').remove();
        }
      });
    }

    initializeSelectAddition();
    initializeSpecialInputs();
    intializeAssociatorOptions();
    initializeComboListOptions();
    initializeDateOptions();
    intializeGeolocatorOptions();
    intializeFileUploaderOptions();
    initializePageNavigation();
    initializeRecordPresets();
    initializeDuplicateRecord();
    initializeNewRecordPreset();
    Kora.Records.Modal();
    multiSelectPlaceholders();
    Kora.Inputs.Number();
}
