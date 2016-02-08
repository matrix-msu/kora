<div class="form-group">
    <?php
    if($genlist==null){
        $value = '';
        $value2 = \App\GeneratedListField::getList($field,false);
    }else{
        $value = explode('[!]',$genlist->options);
        $value2 = array();
        foreach($value as $val){
            $value2[$val] = $val;
        }
    }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    {!! Form::select($field->flid.'[]',$value2, $value,['class' => 'form-control', 'Multiple', 'id' => 'list'.$field->flid]) !!}
</div>

<script>
    $('#list{{ $field->flid }}').select2({
        tags: true
    });
</script>