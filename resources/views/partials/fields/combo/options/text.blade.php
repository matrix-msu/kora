<div class="form-group mt-xxl">
    {!! Form::label('regex_'.$fnum,'Regex') !!}
    {!! Form::text('regex_'.$fnum, App\KoraFields\ComboListField::getComboFieldOption($field,'Regex',$fnum), ['class' => 'text-input', 'placeholder' => 'Enter regular expression pattern here']) !!}
	<div><a href="#" class="field-preset-link open-regex-modal-js">Use a Value Preset for this Regex</a></div>
</div>

<div class="form-group mt-xxxl">
    <label for="multi_{{$fnum}}">Multilined?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="multi_{{$fnum}}" {{App\KoraFields\ComboListField::getComboFieldOption($field,'MultiLine',$fnum) ? 'checked': ''}} />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Select to set the field as multilined</span>
        <span class="placeholder-alt">Field is set to be multilined</span>
    </div>
</div>
