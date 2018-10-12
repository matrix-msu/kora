<div class="form-group mt-xxxl">
    {!! Form::label('options_'.$fnum,'List Options') !!}
    <select multiple class="multi-select modify-select genlist-options-js" name="options_{{$fnum}}[]" data-placeholder="Select or Add Some Options">
        @foreach(\App\ComboListField::getComboList($field,false,$fnum) as $opt)
            <option value="{{$opt}}">{{$opt}}</option>
        @endforeach
    </select>
</div>

<div class="form-group mt-xl">
    {!! Form::label('regex_'.$fnum,'Regex') !!}
    {!! Form::text('regex_'.$fnum, \App\ComboListField::getComboFieldOption($field,'Regex',$fnum), ['class' => 'text-input', 'placeholder' => 'Enter regular expression pattern here']) !!}
</div>
