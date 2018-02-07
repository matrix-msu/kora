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
        $('#assocSearch').on('keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();

                var assocText = $('#assocSearch');
                var loadbar = $('#search_progress');

                //get value
                var keyword = assocText.val();

                //hide value and display loading
                assocText.hide();
                loadbar.show();

                //send it to ajax
                $.ajax({
                    url: assocSearchURI,
                    type: 'POST',
                    data: {
                        "_token": csfrToken,
                        "keyword": keyword
                    },
                    success: function (result) {
                        assocText.show();
                        loadbar.hide();

                        var records = result;
                        var html = '';

                        var cnt = 0;
                        var page = 1;
                        for (var index in records) {
                            //close pagination
                            if(cnt==10){
                                html += "</div>";
                                //next page
                                cnt = 0;
                                page++;
                            }

                            cnt++;

                            //setup pagination
                            if(cnt == 1 && page==1){
                                html += "<div id='pg1' class='aPage'>";
                            }else if(cnt == 1){
                                html += "<div id='pg"+page+"' class='aPage' style='display:none'>";
                            }
                            //print out results
                            if(cnt%2==1)
                                html += "<div class='result_div'>";
                            else
                                html += "<div class='result_div' style='background-color: lightgrey'>";
                            html += "<span class='result_kid' style='float: left; width: 33%;'>"+index+"</span>";
                            //html += "<span style='display: inline-block; width: 33%;'>"+records[index]+"</span>";
                            var preview = records[index];
                            html += "<span style='display: inline-block; width: 33%;'>"+preview[0];
                            for(var j=1;j<preview.length;j++) {
                                html += "<br>"+preview[j];
                            }
                            html += "</span>";
                            html += "<span class='result_add' style='float: right; width: 33%;'><a>Add</a></span></div>";
                        }
                        //case where the last page has less than 10 records
                        if(cnt != 1){html += '</div>';}

                        //case where no results
                        if(cnt==0 && page==1){
                            html += "<div id='pg1' class='aPage'>No results found...</div>";
                        }

                        $('#assocSearchResults').html(html);

                        //adding the pagination links if more than one page
                        if(page>1){
                            pageHTML = '';
                            for(var i=1;i<page+1;i++){
                                pageHTML += "<button type='button' class='page' style='margin-right:5px'>"+i+"</button>";
                            }
                            $('#assocPages').html(pageHTML);
                        }else{
                            $('#assocPages').html('');
                        }
                    }
                });

                return false;
            }
        });

        $('#assocPages').on('click', '.page', function() {
            var page = 'pg'+$(this).text();
            $('.aPage').each(function(){
                var pgid = $(this).attr('id');
                if(pgid==page){
                    $(this).attr('style','');
                }else{
                    $(this).attr('style','display:none');
                }
            });
        });

        $('#assocSearchResults').on('click', '.result_add', function() {
            var kid = $(this).siblings('.result_kid').text();

            var html = "<option value='"+kid+"' selected>"+kid+"</option>";
            var options = $('#default').html()+html;

            $('#default').html(options);
        });
        $('.default_div').on('click', '.remove_option', function(){
            val = $('option:selected', '#default').val();

            $('option:selected', '#default').remove();
        });
        $('.default_div').on('click', '.move_option_up', function(){
            val = $('option:selected', '#default').val();

            $('#default').find('option:selected').each(function() {
                $(this).insertBefore($(this).prev());
            });
        });
        $('.default_div').on('click', '.move_option_down', function(){
            val = $('option:selected', '#default').val();

            $('#default').find('option:selected').each(function() {
                $(this).insertAfter($(this).next());
            });
        });

        function saveAssocList(){
            //foreach assoc_item
            var list = '';
            $('.assoc_item').each(function( index, element ) {
                fid = $(this).attr('id');
                //if checked or if preview fields has selections
                if($('#search'+fid).prop('checked') | $('#preview'+fid).val()!=null) {
                    //gather info and add to array
                    search = $('#search'+fid).prop('checked');
                    preview = $('#preview'+fid).val();

                    pOne = '[fid]'+fid+'[fid]';

                    if(search){
                        pTwo = '[search]1[search]';
                    }else{
                        pTwo = '[search]0[search]';
                    }

                    if(preview != null){
                        pThree = '[flids]'+preview[0];
                        for(var i=1;i<preview.length;i++){
                            pThree += '-'+preview[i];
                        }
                        pThree += '[flids]';
                    }else{
                        pThree = '[flids][flids]';
                    }

                    item = pOne+pTwo+pThree;

                    if(list==''){
                        list = item;
                    }else{
                        list += '[!]'+item;
                    }
                }
            });

            $('#assocValue').val(list);

            $("#default > option").each(function() {
                $(this).attr('selected','selected');
            });
        }
    }

    function initializeComboListOptions(){
        $('#combo_defaults').on('click', '.delete_combo_def', function() {
            parentDiv = $(this).parent();
            parentDiv.remove();
        });

        $('.form-group').on('click', '.add_option', function() {
            val1 = $('#default_one').val();
            val2 = $('#default_two').val();
            type1 = '{{$oneType}}';
            type2 = '{{$twoType}}';

            if(val1=='' | val2==''){
                console.log('Both fields must be filled out');
            }else{
                div = '<div class="default">';

                if(type1=='Text' | type1=='List'){
                    div += '<span style="float:left;width:40%;margin-bottom:10px">'+val1+'</span>';
                }else if(type1=='Number'){
                    // unit = '<?php
                    // if($oneType=='Number')
                    //     echo \App\ComboListField::getComboFieldOption($field,'Unit','one');
                    //     ?>';
                    // div += '<span style="float:left;width:40%;margin-bottom:10px">'+val1+' '+unit+'</span>';
                }else if(type1=='Multi-Select List' | type1=='Generated List' | type1=='Associator'){
                    div += '<span style="float:left;width:40%;margin-bottom:10px">';
                    for(k=0;k<val1.length;k++){
                        div += '<div>'+val1[k]+'</div>';
                    }
                    div += '</span>';
                }

                if(type2=='Text' | type2=='List'){
                    div += '<span style="float:left;width:40%;margin-bottom:10px">'+val2+'</span>';
                }else if(type2=='Number'){
                    // unit = '<?php
                    // if($twoType=='Number')
                    //     echo \App\ComboListField::getComboFieldOption($field,'Unit','two');
                    //     ?>';
                    //div += '<span style="float:left;width:40%;margin-bottom:10px">'+val2+' '+unit+'</span>';
                }else if(type2=='Multi-Select List' | type2=='Generated List' | type2=='Associator'){
                    div += '<span style="float:left;width:40%;margin-bottom:10px">';
                    for(k=0;k<val2.length;k++){
                        div += '<div>'+val2[k]+'</div>';
                    }
                    div += '</span>';
                }

                div += '<span class="delete_combo_def" style="float:left;width:20%;margin-bottom:10px"><a>[X]</a></span>';

                div += '</div>';

                $('#combo_defaults').html($('#combo_defaults').html()+div);

                if(type1=='Multi-Select List' | type1=='Generated List' | type1=='List')
                    $('#default_one').select2("val", "");
                else if(type1=='Associator')
                    $('#default_one').html("");
                else
                    $('#default_one').val('');

                if(type2=='Multi-Select List' | type2=='Generated List' | type2=='List')
                    $('#default_two').select2("val", "");
                else if(type2=='Associator')
                    $('#default_two').html("");
                else
                    $('#default_two').val('');
            }
        });
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
            initializeComboListOptions();
            break;
        default:
            break;
    }
}