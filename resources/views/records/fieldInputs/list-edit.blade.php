<div class="form-group">
    <?php
    if($list==null){
        $value = '';
    }else{
        $value = $list->option;
    }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    {!! Form::select($field->flid,\App\ListField::getList($field,true), $value,['class' => 'form-control', 'id' => 'list'.$field->flid]) !!}
</div>

<script>
    $('#list{{ $field->flid }}').select2();
</script>