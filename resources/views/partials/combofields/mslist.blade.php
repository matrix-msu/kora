<div id="list_option_form{{$fnum}}">
    <div>
        {!! Form::label('options_'.$fnum,trans('partials_combofields_mslist.options').': ') !!}
        <select multiple class="form-control list_options{{$fnum}}" name = "options_{{$fnum}}[]">
            @foreach(\App\Http\Controllers\FieldController::getComboList($field,false,$fnum) as $opt)
                <option value="{{$opt}}">{{$opt}}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-primary remove_option{{$fnum}}">{{trans('partials_combofields_mslist.delete')}}</button>
        <button type="button" class="btn btn-primary move_option_up{{$fnum}}">{{trans('partials_combofields_mslist.up')}}</button>
        <button type="button" class="btn btn-primary move_option_down{{$fnum}}">{{trans('partials_combofields_mslist.down')}}</button>
    </div>
    <div>
        <span><input type="text" class="new_list_option{{$fnum}}"></span>
        <span><button type="button" class="btn btn-primary add_option{{$fnum}}">{{trans('partials_combofields_mslist.add')}}</button></span>
    </div>
</div>

<script>
    $('#list_option_form{{$fnum}}').on('click', '.remove_option{{$fnum}}', function(){
        val = $('option:selected', '.list_options{{$fnum}}').val();

        $('option:selected', '.list_options{{$fnum}}').remove();
        $("#default_{{$fnum}} option[value='"+val+"']").remove();
    });
    $('#list_option_form{{$fnum}}').on('click', '.move_option_up{{$fnum}}', function(){
        val = $('option:selected', '.list_options{{$fnum}}').val();
        defOpt = $("#default_{{$fnum}} option[value='"+val+"']");

        $('.list_options{{$fnum}}').find('option:selected').each(function() {
            $(this).insertBefore($(this).prev());
        });
        defOpt.insertBefore(defOpt.prev());
    });
    $('#list_option_form{{$fnum}}').on('click', '.move_option_down{{$fnum}}', function(){
        val = $('option:selected', '.list_options{{$fnum}}').val();
        defOpt = $("#default_{{$fnum}} option[value='"+val+"']");

        $('.list_options{{$fnum}}').find('option:selected').each(function() {
            $(this).insertAfter($(this).next());
        });
        defOpt.insertAfter(defOpt.next());
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
        }
    });
</script>