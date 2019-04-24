{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl ">
    {!! Form::label('default','Default') !!}
    <div class="check-box-half">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="default">
        <span class="check"></span>
        <span class="placeholder"></span>
    </div>
</div>

<script>
    Kora.Fields.Options('Boolean');
</script>