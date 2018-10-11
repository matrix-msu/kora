<div class="form-group mt-xxxl">
    {!! Form::label('options_'.$fnum,'List Options') !!}
    <select multiple class="multi-select modify-select mslist-options-js" name="options_{{$fnum}}[]" data-placeholder="Select or Add Some Options">
        @foreach(\App\ComboListField::getComboList($field,false,$fnum) as $opt)
            <option value="{{$opt}}">{{$opt}}</option>
        @endforeach
    </select>
</div>
