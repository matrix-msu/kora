<div class="form-group">
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    {!! Form::select($field->flid.'[]',\App\GeneratedListField::getList($field,false), explode('[!]',$field->default),['class' => 'form-control', 'Multiple', 'id' => 'list'.$field->flid]) !!}
</div>

<script>
    $('#list{{ $field->flid }}').select2({
        tags: true
    });
</script>