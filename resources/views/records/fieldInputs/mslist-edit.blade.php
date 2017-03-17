<div class="form-group">
    <?php
    if($mslist==null){
        $value = '';
    }else{
        $value = explode('[!]',$mslist->options);
    }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    {!! Form::select($field->flid.'[]',\App\MultiSelectListField::getList($field,false), $value,['class' => 'form-control', 'Multiple', 'id' => 'list'.$field->flid]) !!}
</div>

<script>
    $('#list{{ $field->flid }}').select2();
</script>