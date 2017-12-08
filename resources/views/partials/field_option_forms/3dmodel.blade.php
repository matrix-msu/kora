<hr>
{!! Form::hidden('advance','true') !!}
<div class="form-group">
    {!! Form::label('filesize',trans('fields_options_3dmodel.maxsize').' (kb): ') !!}
    <input type="number" name="filesize" class="form-control" step="1"
           value="0" min="0">
</div>

<div class="form-group">
    {!! Form::label('filetype',trans('fields_options_3dmodel.types').' (MIME): ') !!}
    {!! Form::select('filetype'.'[]',['obj' => 'OBJ','stl' => 'STL'],
        '',['class' => 'form-control filetypes', 'Multiple', 'id' => 'list']) !!}
</div>

<div class="form-group">
    {!! Form::label('color','Model Color: ') !!}
    <input type="color" name="color" class="form-control"
           value="#CAA618">
</div>

<div class="form-group">
    {!! Form::label('backone','Background Color One: ') !!}
    <input type="color" name="backone" class="form-control"
           value="#ffffff">
</div>

<div class="form-group">
    {!! Form::label('backtwo','Background Color Two: ') !!}
    <input type="color" name="backtwo" class="form-control"
           value="#383840">
</div>

<script>
    $('.filetypes').select2();
</script>