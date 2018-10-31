{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('default','Default') !!}
    <textarea id="default" name="default" class="ckeditor-js"></textarea>
</div>

<script>
    Kora.Fields.Options('Rich Text');
</script>