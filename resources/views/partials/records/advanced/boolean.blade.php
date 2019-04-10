<div class="form-group mt-xl">
    {!! Form::label($flid.'_input',$field['name']) !!}
    <span class="error-message"></span>

    <div class="check-box-half">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="{{$flid.'_input'}}">
        <span class="check"></span>
        <span class="placeholder"></span>
    </div>
</div>