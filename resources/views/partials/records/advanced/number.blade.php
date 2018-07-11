<div class="form-group mt-xl">
    {!! Form::label($field->flid.'_left',$field->name) !!}
    <input class="text-input" type="number" name="{{$field->flid}}_left" placeholder="Enter left bound (leave blank for -infinity)">
</div>
<div class="form-group mt-sm">
    <input class="text-input" type="number" name="{{$field->flid}}_right" placeholder="Enter right bound (leave blank for infinity)">
</div>
<div class="form-group mt-sm">
    <div class="check-box-half">
        <input type="checkbox" value="1" id="active" class="check-box-input" name="{{$field->flid}}_invert" />
        <span class="check"></span>
        <span class="placeholder">Searches outside the given range</span>
    </div>
</div>