@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')

    @if (isset($cloneArray))
        <h1>Clone Record {{$form->pid}}-{{$form->fid}}-{{$rid}}</h1>
    @else
        <h1>{{trans('records_create.new')}} {{ $form->name }}</h1>
    @endif

    <hr/>
    <div class="form-group">
        <span>{!! Form::label('presetlabel', trans('records_create.from').': ') !!}</span>
        <select class="form-control" id="presetselect" onchange="populate()">
            <option disabled selected>{{trans('records_create.select')}}</option>
            @for($i=0; $i < sizeof($presets); $i++)
                <option value="{{$presets[$i]['id']}}">{{$presets[$i]['name']}}</option>
            @endfor
        </select>
    </div>

    <hr/>

    <script>
        $('#presetselect').select2({
            placeholder: '{{trans('records_create.select')}}'
        });

        function populate() {
            var val = $('#presetselect').val();

            $.ajax({
                url: '{{action('RecordPresetController@getData')}}',
                type: 'POST',
                data: {
                    '_token': '{{csrf_token()}}',
                    'id': val
                }, success: function(response) {
                    putArray(response);
                }
            });
        }

        function putArray(ary) {
            var flids = ary['flids'];
            var data = ary['data'];

            var i;
            for (i = 0; i < flids.length; i++) {
                var flid = flids[i];
                var field = data[flid];

                switch (field['type']) {
                    case 'Text':
                        $('[name='+flid+']').val(field['text']);
                        break;

                    case 'Rich Text':
                        CKEDITOR.instances[flid].setData(field['rawtext']);
                        break;

                    case 'Number':
                        $('[name='+flid+']').val(field['number']);
                        break;

                    case 'List':
                        $('[name='+flid+']').select2('val', field['option']);
                        break;

                    case 'Multi-Select List':
                        $('#list'+flid).select2('val', field['options']);
                        break;

                    case 'Generated List':
                        var options = field['options'];
                        var valArray = [];
                        var h = 0;
                        var selector = $("#list" + flid);
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
                        selector.select2('val', valArray);
                        break;

                    case 'Date':
                        var date = field['data'];

                        if(date['circa'])
                            $('[name=circa_'+flid+']').prop('checked', true);
                        $('[name=month_'+flid+']').val(date['month']);
                        $('[name=day_'+flid+']').val(date['day']);
                        $('[name=year_'+flid+']').val(date['year']);
                        $('[name=era_'+flid+']').val(date['era']);
                        break;

                    case 'Schedule':
                        var j,  events = field['events'];
                        var selector = $('#list'+flid);
                        $('#list'+flid+' option[value!="0"]').remove();

                        for (j=0; j < events.length; j++) {
                            selector.append($('<option/>', {
                                value: events[j],
                                text: events[j],
                                selected: 'selected'
                            }));
                        }
                        break;

                    case 'Geolocator':
                        var l, locations = field['locations'];
                        var selector = $('#list'+flid);
                        $('#list'+flid+' option[value!="0"]').remove();

                        for (l=0; l < locations.length; l++) {
                            selector.append($('<option/>', {
                                value: locations[l],
                                text: locations[l],
                                selected: 'selected'
                            }));
                        }
                        break;

                    case 'Combo List':
                        var p, combos = field['combolists'];
                        var selector = $('#combo_list_'+flid);

                        // Empty defaults, we need to do this as the preset may have done so.
                        // However if it hasn't, the defaults will be in the preset so this is safe.
                        selector.children("#val_"+flid).each(function(){
                            $(this).remove();
                        });

                        for(p=0; p < combos.length; p++) {
                            var rawData = combos[p];

                            var field1RawData = rawData.split('[!f1!]')[1];
                            var field2RawData = rawData.split('[!f2!]')[1];

                            var field1ToPrint = field1RawData.split('[!]');
                            var field2ToPrint = field2RawData.split('[!]');

                            var html = "";
                            html += '<div id="val_'+flid+'">';

                            if (field1ToPrint.length == 1) {
                                html += '<span style="float:left;width:40%;margin-bottom:10px">'+field1ToPrint+'</span>';
                            }
                            else {
                                html += '<span style="float:left;width:40%;margin-bottom:10px">';
                                for (var q = 0; q < field1ToPrint.length; q++) {
                                    html += '<div>'+field1ToPrint[q]+'</div>';
                                }
                                html+= '</span>';
                            }
                            if (field2ToPrint.length == 1) {
                                html += '<span style="float:left;width:40%;margin-bottom:10px">'+field2ToPrint+'</span>';
                            }
                            else {
                                html += '<span style="float:left;width:40%;margin-bottom:10px">';
                                for (var r = 0; r < field2ToPrint.length; r++) {
                                    html += '<div>'+field2ToPrint[r]+'</div>';
                                }
                                html += '</span>';
                            }
                            html += '<input name="'+flid+'_val[]" type="hidden" value="'+rawData+'" id="'+flid+'_val[]">';
                            html += '<span class="delete_combo_def_11" style="float:left;width:20%;margin-bottom:10px"><a>[X]</a></span>';
                            html += '</div>';

                            selector.append(html);
                        }
                        break;
                }
            }
        }

    </script>

    @if(isset($cloneArray))
        <script>
            window.onload = function() { putArray(<?php echo json_encode($cloneArray); ?>) };
        </script>
    @endif

    {!! Form::model($record = new \App\Record, ['url' => 'projects/'.$form->pid.'/forms/'.$form->fid.'/records', 'id' => 'createform']) !!}
    {!! Form::model($record = new \App\Record, ['url' => 'projects/'.$form->pid.'/forms/'.$form->fid.'/records',
        'enctype' => 'multipart/form-data', 'id' => 'new_record_form']) !!}
        <div><b>{{trans('records_create.mass')}} ({{trans('records_create.max')}} 1000):</b> <input type="checkbox" name="mass_creation"></div>
        <input type="number" name="mass_creation_num" class="form-control" value="2" step="1" max="1000" min="2">
    <br>
        @include('records.form',['submitButtonText' => trans('records_create.create'), 'form' => $form])
    {!! Form::close() !!}

    @include('errors.list')
@stop
