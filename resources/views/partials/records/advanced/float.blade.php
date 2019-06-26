<div class="form-group mt-xl">
    {!! Form::label($flid.'_left',$field['alt_name']!='' ? $field['name'].' ('.$field['alt_name'].')' : $field['name']) !!}
    <input class="text-input" type="number" name="{{$flid}}_left" placeholder="Enter left bound (leave blank for -infinity)">
</div>
<div class="form-group mt-sm">
    <input class="text-input" type="number" name="{{$flid}}_right" placeholder="Enter right bound (leave blank for infinity)">
</div>
<div class="form-group mt-sm">
    <div class="check-box-half">
        <input type="checkbox" value="1" id="active" class="check-box-input" name="{{$flid}}_invert" />
        <span class="check"></span>
        <span class="placeholder">Searches outside the given range</span>
    </div>
</div>
