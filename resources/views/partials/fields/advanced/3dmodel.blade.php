{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('filesize','Max File Size (kb)') !!}
    <input type="number" name="filesize" class="text-input" step="1" value="0" min="0" placeholder="Enter max file size (kb) here">
</div>

<div class="form-group mt-xl">
    {!! Form::label('filetype','Allowed File Types (MIME)') !!}
    {!! Form::select('filetype'.'[]',['obj' => 'OBJ','stl' => 'STL','image/jpeg' => 'JPEG Texture',
        'image/png' => 'PNG Texture','application/octet-stream' => 'Other'], getDefaultTypes('3D-Model'),
        ['class' => 'multi-select', 'Multiple']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('color','Model Color') !!}
    <input type="color" name="color" class="text-input" value="#CAA618">
</div>

<div class="form-group mt-xl">
    {!! Form::label('backone','Background Color One') !!}
    <input type="color" name="backone" class="text-input" value="#ffffff">
</div>

<div class="form-group mt-xl">
    {!! Form::label('backtwo','Background Color Two') !!}
    <input type="color" name="backtwo" class="text-input" value="#383840">
</div>

<script>
    Kora.Fields.Options('Model');
</script>