{!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateComboOptions', $field->pid, $field->fid, $field->flid]]) !!}
@include('fields.options.hiddens')
{!! Form::hidden('option','Regex') !!}
{!! Form::hidden('fieldnum',$fnum) !!}
<div class="form-group">
    {!! Form::label('value',trans('partials_combofields_genlist.regex').': ') !!}
    {!! Form::text('value', \App\Http\Controllers\FieldController::getComboFieldOption($field,'Regex',$fnum), ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::submit(trans('partials_combofields_genlist.updateregex'),['class' => 'btn btn-primary form-control']) !!}
</div>
{!! Form::close() !!}

<div id="list_option_form{{$fnum}}">
    <div>
        {!! Form::label('options',trans('partials_combofields_genlist.options').': ') !!}
        <select multiple class="form-control list_options{{$fnum}}">
            @foreach(\App\Http\Controllers\FieldController::getComboList($field,false,$fnum) as $opt)
                <option value="{{$opt}}">{{$opt}}</option>
            @endforeach
        </select>
        <button class="btn btn-primary remove_option{{$fnum}}">{{trans('partials_combofields_genlist.delete')}}</button>
        <button class="btn btn-primary move_option_up{{$fnum}}">{{trans('partials_combofields_genlist.up')}}</button>
        <button class="btn btn-primary move_option_down{{$fnum}}">{{trans('partials_combofields_genlist.down')}}</button>
    </div>
    <div>
        <span><input type="text" class="new_list_option{{$fnum}}"></span>
        <span><button class="btn btn-primary add_option{{$fnum}}">{{trans('partials_combofields_genlist.add')}}</button></span>
    </div>
</div>

<script>
    $('#list_option_form{{$fnum}}').on('click', '.remove_option{{$fnum}}', function(){
        val = $('option:selected', '.list_options{{$fnum}}').val();

        $('option:selected', '.list_options{{$fnum}}').remove();
        $("#default_{{$fnum}} option[value='"+val+"']").remove();
        SaveList{{$fnum}}();
    });
    $('#list_option_form{{$fnum}}').on('click', '.move_option_up{{$fnum}}', function(){
        val = $('option:selected', '.list_options{{$fnum}}').val();
        defOpt = $("#default_{{$fnum}} option[value='"+val+"']");

        $('.list_options{{$fnum}}').find('option:selected').each(function() {
            $(this).insertBefore($(this).prev());
        });
        defOpt.insertBefore(defOpt.prev());
        SaveList{{$fnum}}();
    });
    $('#list_option_form{{$fnum}}').on('click', '.move_option_down{{$fnum}}', function(){
        val = $('option:selected', '.list_options{{$fnum}}').val();
        defOpt = $("#default_{{$fnum}} option[value='"+val+"']");

        $('.list_options{{$fnum}}').find('option:selected').each(function() {
            $(this).insertAfter($(this).next());
        });
        defOpt.insertAfter(defOpt.next());
        SaveList{{$fnum}}();
    });
    $('#list_option_form{{$fnum}}').on('click', '.add_option{{$fnum}}', function() {
        val = $('.new_list_option{{$fnum}}').val();
        val = val.trim();

        if(val != ''){
            $('.list_options{{$fnum}}').append($("<option/>", {
                value: val,
                text: val
            }));
            $('.new_list_option{{$fnum}}').val('');
            $('#default_{{$fnum}}').append($("<option/>", {
                value: val,
                text: val
            }));
            SaveList{{$fnum}}();
        }
    });

    function SaveList{{$fnum}}() {
        options = new Array();
        $(".list_options{{$fnum}} option").each(function(){
            options.push($(this).val());
        });

        $.ajax({
            url: '{{ action('FieldController@saveComboList',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                action: 'SaveList',
                options: options,
                fnum: '{{$fnum}}'
            },
            success: function (result) {
                //location.reload();
            }
        });
    }
</script>