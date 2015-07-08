<div class="form-group">
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif

    <textarea id="{{$field->flid}}" name="{{$field->flid}}"></textarea>

</div>

<script>
    CKEDITOR.replace( '{{$field->flid}}' );
</script>