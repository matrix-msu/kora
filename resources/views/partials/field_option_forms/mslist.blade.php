<hr>
{!! Form::hidden('advance','true') !!}

<div class="form-group">
    {!! Form::label('default',trans('fields_options_mslist.def').': ') !!}
    {!! Form::select('default[]',[], '',['class' => 'form-control', 'multiple', 'id' => 'default']) !!}
</div>

<div class="list_option_form">
    <div>
        {!! Form::label('options',trans('fields_options_mslist.options').': ') !!}
        <select multiple class="form-control list_options" name="options[]">
        </select>
        <button type="button" class="btn btn-primary remove_option">{{trans('fields_options_mslist.delete')}}</button>
        <button type="button" class="btn btn-primary move_option_up">{{trans('fields_options_mslist.up')}}</button>
        <button type="button" class="btn btn-primary move_option_down">{{trans('fields_options_mslist.down')}}</button>
    </div>
    <div>
        <span><input type="text" class="new_list_option"></span>
        <span><button type="button" class="btn btn-primary add_option">{{trans('fields_options_mslist.add')}}</button></span>
    </div>
</div>

<script>
    $('#default').select2();

    $('.list_option_form').on('click', '.remove_option', function(){
        val = $('option:selected', '.list_options').val();

        $('option:selected', '.list_options').remove();
        $("#default option[value='"+val+"']").remove();
    });
    $('.list_option_form').on('click', '.move_option_up', function(){
        val = $('option:selected', '.list_options').val();
        defOpt = $("#default option[value='"+val+"']");

        $('.list_options').find('option:selected').each(function() {
            $(this).insertBefore($(this).prev());
        });
        defOpt.insertBefore(defOpt.prev());
    });
    $('.list_option_form').on('click', '.move_option_down', function(){
        val = $('option:selected', '.list_options').val();
        defOpt = $("#default option[value='"+val+"']");

        $('.list_options').find('option:selected').each(function() {
            $(this).insertAfter($(this).next());
        });
        defOpt.insertAfter(defOpt.next());
    });
    $('.list_option_form').on('click', '.add_option', function(){
        val = $('.new_list_option').val();
        val = val.trim();

        if(val != '') {
            $('.list_options').append($("<option/>", {
                value: val,
                text: val
            }));
            $('#default').append($("<option/>", {
                value: val,
                text: val
            }));
            $('.new_list_option').val('');
        }
    });

    function selectAll(){
        selectBox = $('.list_options > option').each(function(){
            $(this).attr('selected', 'selected');
        });
    }
</script>