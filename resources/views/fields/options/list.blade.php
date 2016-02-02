@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_list.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_list.updatereq'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateDefault', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('default',trans('fields_options_list.def').': ') !!}
        {!! Form::select('default',\App\Http\Controllers\FieldController::getList($field,true), $field->default,['class' => 'form-control', 'id' => 'default']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_list.updatedef'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    <div class="list_option_form">
        <div>
            {!! Form::label('options',trans('fields_options_list.options').': ') !!}
            <select multiple class="form-control list_options">
                @foreach(\App\Http\Controllers\FieldController::getList($field,false) as $opt)
                    <option value="{{$opt}}">{{$opt}}</option>
                @endforeach
            </select>
            <button class="btn btn-primary remove_option">{{trans('fields_options_list.delete')}}</button>
            <button class="btn btn-primary move_option_up">{{trans('fields_options_list.up')}}</button>
            <button class="btn btn-primary move_option_down">{{trans('fields_options_list.down')}}</button>
        </div>
        <div>
            <span><input type="text" class="new_list_option"></span>
            <span><button class="btn btn-primary add_option">{{trans('fields_options_list.add')}}</button></span>
        </div>
    </div>

    @include('partials.option_preset')

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('#default').select2();

        $('.list_option_form').on('click', '.remove_option', function(){
            val = $('option:selected', '.list_options').val();

            $('option:selected', '.list_options').remove();
            $("#default option[value='"+val+"']").remove();
            SaveList();
        });
        $('.list_option_form').on('click', '.move_option_up', function(){
            val = $('option:selected', '.list_options').val();
            defOpt = $("#default option[value='"+val+"']");

            $('.list_options').find('option:selected').each(function() {
                $(this).insertBefore($(this).prev());
            });
            defOpt.insertBefore(defOpt.prev());
            SaveList();
        });
        $('.list_option_form').on('click', '.move_option_down', function(){
            val = $('option:selected', '.list_options').val();
            defOpt = $("#default option[value='"+val+"']");

            $('.list_options').find('option:selected').each(function() {
                $(this).insertAfter($(this).next());
            });
            defOpt.insertAfter(defOpt.next());
            SaveList();
        });
        $('.list_option_form').on('click', '.add_option', function() {
            val = $('.new_list_option').val();
            val = val.trim();

            if(val != ''){
                $('.list_options').append($("<option/>", {
                    value: val,
                    text: val
                }));
                $('#default').append($("<option/>", {
                    value: val,
                    text: val
                }));
                $('.new_list_option').val('');
                SaveList();
            }
        });

        function SaveList() {
            options = new Array();
            $(".list_options option").each(function(){
                options.push($(this).val());
            });

            $.ajax({
                url: '{{ action('FieldController@saveList',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    action: 'SaveList',
                    options: options
                },
                success: function (result) {
                    //location.reload();
                }
            });
        }
    </script>
@stop