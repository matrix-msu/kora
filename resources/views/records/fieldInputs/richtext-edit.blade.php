<div class="form-group">
    <?php
        if($richtext==null){
            $value = '';
        }else{
            $value = $richtext->rawtext;
        }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif

    <textarea id="{{$field->flid}}" name="{{$field->flid}}">{{$value}}</textarea>

</div>

<script>
    CKEDITOR.replace( '{{$field->flid}}' );
</script>