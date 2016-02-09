<div class="form-group">
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    {!! Form::select($field->flid,\App\ListField::getList($field,true), $field->default,['class' => 'form-control', 'id' => 'list'.$field->flid]) !!}
</div>

<script>
    $('#list{{ $field->flid }}').select2();
</script>