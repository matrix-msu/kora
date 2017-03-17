<hr>
{!! Form::hidden('advance','true') !!}

<div class="form-group">
    {!! Form::label('filesize',trans('fields_options_documents.maxsize').' (kb): ') !!}
    <input type="number" name="filesize" class="form-control" step="1"
           value="0" min="0">
</div>

<div class="form-group">
    {!! Form::label('maxfiles',trans('fields_options_documents.maxamount').': ') !!}
    <input type="number" name="maxfiles" class="form-control" step="1"
           value="0" min="0">
</div>

<div class="form-group">
    {!! Form::label('filetype',trans('fields_options_documents.types').' (MIME): ') !!}
    {!! Form::select('filetype'.'[]',[],'',
        ['class' => 'form-control filetypes', 'Multiple', 'id' => 'list']) !!}
</div>

<script>
    $('.filetypes').select2({
        tags: true
    });
</script>