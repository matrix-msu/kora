var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};

Kora.Fields.Options = function(fieldType) {

    function initializeSelects() {
        //Most field option pages need these
        $('.single-select').chosen({
            width: '100%',
        });

        $('.multi-select').chosen({
            width: '100%',
        });
    }

    function initializeSelectAddition() {
        $('.chosen-search-input').on('keyup', function(e) {
            var container = $(this).parents('.chosen-container').first();

            if (e.which === 13 && container.find('li.no-results').length > 0) {
                var option = $("<option>").val(this.value).text(this.value);

                var select = container.siblings('.modify-select').first();

                select.append(option);
                select.find(option).prop('selected', true);
                select.trigger("chosen:updated");
            }
        });
    }

    //Fields that have specific functionality will have their own initialization process

    function initializeDateOptions() {
        $('.start-year-js').change(function() { printYears(); });

        $('.end-year-js').change(function() { printYears(); });

        function printYears(){
            start = $('.start-year-js').val(); end = $('.end-year-js').val();

            if(start=='')  { start = 0; }
            if(end =='') { end = 9999; }

            val = '<option></option>';
            for(var i=start;i<+end+1;i++) {
                val += "<option value=" + i + ">" + i + "</option>";
            }

            $('.default-year-js').html(val); $('.default-year-js').trigger("chosen:updated");
        }
    }

    function initializeGeneratedListOptions() {
        var listOpt = $('.genlist-options-js');
        var listDef = $('.genlist-default-js');

        listOpt.find('option').prop('selected', true);
        listOpt.trigger("chosen:updated");

        listOpt.chosen().change(function() {
            //TODO::figure out this
        });

        listOpt.bind("DOMSubtreeModified",function(){
            var options = listOpt.html();
            listDef.html(options);
            listDef.trigger("chosen:updated");
        });
    }

    function initializeListOptions() {
        var listOpt = $('.list-options-js');
        var listDef = $('.list-default-js');

        listOpt.find('option').prop('selected', true);
        listOpt.trigger("chosen:updated");

        listOpt.chosen().change(function() {
            //TODO::figure out this
        });

        listOpt.bind("DOMSubtreeModified",function(){
            var options = listOpt.html();
            listDef.html(options);
            listDef.trigger("chosen:updated");
        });
    }

    function initializeMultiSelectListOptions() {
        var listOpt = $('.mslist-options-js');
        var listDef = $('.mslist-default-js');

        listOpt.find('option').prop('selected', true);
        listOpt.trigger("chosen:updated");

        listOpt.chosen().change(function() {
            //figure out this
        });

        listOpt.bind("DOMSubtreeModified",function(){
            var options = listOpt.html();
            listDef.html(options);
            listDef.trigger("chosen:updated");
        });
    }

    function initializeScheduleOptions() {
        Kora.Modal.initialize();

        $('.add-new-default-event-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open($('.schedule-add-event-modal-js'));
        });

        $('.add-new-event-js').on('click', function(e) {
            e.preventDefault();

            var nameInput = $('.event-name-js');
            var sTimeInput = $('.event-start-time-js');
            var eTimeInput = $('.event-end-time-js');

            var name = nameInput.val().trim();
            var sTime = sTimeInput.val().trim();
            var eTime = eTimeInput.val().trim();

            if(name==''|sTime==''|eTime=='') {
                //TODO::show error
            } else {
                if($('.event-allday-js').is(":checked")) {
                    sTime = sTime.split(" ")[0];
                    eTime = eTime.split(" ")[0];
                }

                if(sTime>eTime) {
                    //TODO::show error
                }else {
                    val = name + ': ' + sTime + ' - ' + eTime;

                    if(val != '') {
                        //Value is good so let's add it
                        var option = $("<option>").val(val).text(val);
                        var select = $('.default-event-js');

                        select.append(option);
                        select.find(option).prop('selected', true);
                        select.trigger("chosen:updated");

                        nameInput.val('');
                        Kora.Modal.close($('.schedule-add-event-modal-js'));
                    }
                }
            }
        });
    }

    function intializeGeolocatorOptions() {
        Kora.Modal.initialize();

        $('.add-new-default-location-js').click(function(e) {
            e.preventDefault();

            Kora.Modal.open($('.geolocator-add-location-modal-js'));
        });

        $('.location-type-js').on('change', function(e) {
            newType = $(this).val();
            if(newType=='LatLon') {
                $('.lat-lon-switch-js').removeClass('hidden');
                $('.utm-switch-js').addClass('hidden');
                $('.address-switch-js').addClass('hidden');
            } else if(newType=='UTM') {
                $('.lat-lon-switch-js').addClass('hidden');
                $('.utm-switch-js').removeClass('hidden');
                $('.address-switch-js').addClass('hidden');
            } else if(newType=='Address') {
                $('.lat-lon-switch-js').addClass('hidden');
                $('.utm-switch-js').addClass('hidden');
                $('.address-switch-js').removeClass('hidden');
            }
        });

        $('.add-new-location-js').click(function(e) {
            e.preventDefault();

            //check to see if description provided
            var desc = $('.location-desc-js').val();
            if(desc=='') {
                //TODO::show error
            } else {
                var type = $('.location-type-js').val();

                //determine if info is good for that type
                var valid = true;
                if(type == 'LatLon') {
                    var lat = $('.location-lat-js').val();
                    var lon = $('.location-lon-js').val();

                    if(lat == '' | lon == '') {
                        //TODO::show error
                        valid = false;
                    }
                } else if(type == 'UTM') {
                    var zone = $('.location-zone-js').val();
                    var east = $('.location-east-js').val();
                    var north = $('.location-north-js').val();

                    if(zone == '' | east == '' | north == '') {
                        //TODO::show error
                        valid = false;
                    }
                } else if(type == 'Address') {
                    var addr = $('.location-addr-js').val();

                    if(addr == '') {
                        //TODO::show error
                        valid = false;
                    }
                }

                //if still valid
                if(valid) {
                    //find info for other loc types
                    if(type == 'LatLon')
                        coordinateConvert({"_token": csrfToken,type:'latlon',lat:lat,lon:lon});
                    else if(type == 'UTM')
                        coordinateConvert({"_token": csrfToken,type:'utm',zone:zone,east:east,north:north});
                    else if(type == 'Address')
                        coordinateConvert({"_token": csrfToken,type:'geo',addr:addr});

                    $('.location-lat-js').val(''); $('.location-lon-js').val('');
                    $('.location-zone-js').val(''); $('.location-east-js').val(''); $('.location-north-js').val('');
                    $('.location-addr-js').val('');
                }
            }
        });

        function coordinateConvert(data) {
            $.ajax({
                url: geoConvertUrl,
                type: 'POST',
                data: data,
                success:function(result) {
                    var desc = $('.location-desc-js').val();
                    var fullresult = '[Desc]'+desc+'[Desc]'+result;
                    var latlon = result.split('[LatLon]');
                    var utm = result.split('[UTM]');
                    var addr = result.split('[Address]');
                    var fulltext = 'Description: '+desc+' | LatLon: '+latlon[1]+' | UTM: '+utm[1]+' | Address: '+addr[1];
                    var option = $("<option/>", { value: fullresult, text: fulltext });

                    var select = $('.default-location-js');
                    select.append(option);
                    select.find(option).prop('selected', true);
                    select.trigger("chosen:updated");

                    $('.location-desc-js').val('');
                    Kora.Modal.close($('.geolocator-add-location-modal-js'));
                }
            });
        }
    }

    function intializeAssociatorOptions() {
        //Sets up association configurations
        $('.association-check-js').click(function() {
            var assocDiv = $(this).closest('.form-group').next();
            var input = assocDiv.children('select').first();
            if(this.checked) {
                assocDiv.fadeIn();
                input.prop('disabled', false).trigger("chosen:updated");
            } else {
                assocDiv.hide();
                input.prop('disabled', true).trigger("chosen:updated");
            }
        });

        $('.assoc-search-records-js').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if(keyCode === 13) {
                e.preventDefault();

                var keyword = $(this).val();
                var resultsBox = $('.assoc-select-records-js');
                //Clear old values
                resultsBox.html('');
                resultsBox.trigger("chosen:updated");

                $.ajax({
                    url: assocSearchURI,
                    type: 'POST',
                    data: {
                        "_token": csfrToken,
                        "keyword": keyword
                    },
                    success: function (result) {
                        for(var kid in result) {
                            var preview = result[kid];
                            var opt = "<option value='"+kid+"'>"+kid+": "+preview+"</option>";

                            resultsBox.append(opt);
                            resultsBox.trigger("chosen:updated");
                        }

                        resultInput = resultsBox.next().find('.chosen-search-input').first();
                        resultInput.val('');
                        resultInput.click();
                    }
                });
            }
        });

        $('.assoc-select-records-js').change(function() {
            defaultBox = $('.assoc-default-records-js');

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

    function initializeComboListOptions(){
        $('.combo-value-div-js').on('click', '.delete-combo-value-js', function() {
            parentDiv = $(this).parent();
            parentDiv.remove();
        });

        $('.add-combo-value-js').click(function() {
            inputOne = $('#default_one');
            inputTwo = $('#default_two');

            val1 = inputOne.val();
            val2 = inputTwo.val();

            defaultDiv = $('.combo-value-div-js');

            if(val1=='' | val2=='' | val1==null | val2==null){
                //TODO::Error out
                console.log(val1);
                console.log(val2);
                console.log('Both fields must be filled out');
            } else {
                //Remove empty div if applicable
                if(defaultDiv.children('.combo-list-empty').first())
                    defaultDiv.children('.combo-list-empty').first().remove();

                div = '<div class="combo-value-item-js">';

                if(type1=='Text' | type1=='List' | type1=='Number') {
                    div += '<input type="hidden" name="default_combo_one[]" value="'+val1+'">';
                    div += '<span class="combo-column">'+val1+'</span>';
                } else if(type1=='Multi-Select List' | type1=='Generated List' | type1=='Associator') {
                    div += '<input type="hidden" name="default_combo_one[]" value="'+val1.join('[!]')+'">';
                    div += '<span class="combo-column">'+val1.join(' | ')+'</span>';
                }

                if(type2=='Text' | type2=='List' | type2=='Number') {
                    div += '<input type="hidden" name="default_combo_two[]" value="'+val2+'">';
                    div += '<span class="combo-column">'+val2+'</span>';
                } else if(type2=='Multi-Select List' | type2=='Generated List' | type2=='Associator') {
                    div += '<input type="hidden" name="default_combo_two[]" value="'+val2.join('[!]')+'">';
                    div += '<span class="combo-column">'+val2.join(' | ')+'</span>';
                }

                div += '<span class="combo-delete delete-combo-value-js"><a class="underline-middle-hover">[X]</a></span>';

                div += '</div>';

                defaultDiv.html(defaultDiv.html()+div);

                inputOne.val('');
                if(type1=='Multi-Select List' | type1=='Generated List' | type1=='List' | type1=='Associator')
                    inputOne.trigger("chosen:updated");

                inputTwo.val('');
                if(type2=='Multi-Select List' | type2=='Generated List' | type2=='List' | type2=='Associator')
                    inputTwo.trigger("chosen:updated");
            }
        });

        //ASSOCIATOR OPTIONS
        //Sets up association configurations
        $('.association-check-js').click(function() {
            var assocDiv = $(this).closest('.form-group').next();
            var input = assocDiv.children('select').first();
            if(this.checked) {
                assocDiv.fadeIn();
                input.prop('disabled', false).trigger("chosen:updated");
            } else {
                assocDiv.hide();
                input.prop('disabled', true).trigger("chosen:updated");
            }
        });

        $('.assoc-search-records-js').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if(keyCode === 13) {
                e.preventDefault();

                var keyword = $(this).val();
                var resultsBox = $(this).parent().next().children('.assoc-select-records-js').first();
                //Clear old values
                resultsBox.html('');
                resultsBox.trigger("chosen:updated");

                $.ajax({
                    url: assocSearchURI,
                    type: 'POST',
                    data: {
                        "_token": csrfToken,
                        "keyword": keyword
                    },
                    success: function (result) {
                        for(var kid in result) {
                            var preview = result[kid];
                            var opt = "<option value='"+kid+"'>"+kid+": "+preview+"</option>";

                            resultsBox.append(opt);
                            resultsBox.trigger("chosen:updated");
                        }
                    }
                });
            }
        });

        $('.assoc-select-records-js').change(function() {
            defaultBox = $(this).parent().siblings().first().children('.assoc-default-records-js');

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

        //LIST OPTIONS
        var listOpt = $('.list-options-js');
        listOpt.find('option').prop('selected', true);
        listOpt.trigger("chosen:updated");

        listOpt = $('.mslist-options-js');
        listOpt.find('option').prop('selected', true);
        listOpt.trigger("chosen:updated");

        listOpt = $('.genlist-options-js');
        listOpt.find('option').prop('selected', true);
        listOpt.trigger("chosen:updated");
    }

    initializeSelects();

    switch(fieldType) {
        case 'Date':
            initializeDateOptions();
            break;
        case 'Generated List':
            initializeSelectAddition();
            initializeGeneratedListOptions();
            break;
        case 'List':
            initializeSelectAddition();
            initializeListOptions();
            break;
        case 'Geolocator':
            intializeGeolocatorOptions();
            break;
        case 'Multi-Select List':
            initializeSelectAddition();
            initializeMultiSelectListOptions();
            break;
        case 'Schedule':
            initializeScheduleOptions();
            break;
        case 'Associator':
            intializeAssociatorOptions();
            break;
        case 'Combo List':
            initializeSelectAddition();
            initializeComboListOptions();
            break;
        default:
            break;
    }
}