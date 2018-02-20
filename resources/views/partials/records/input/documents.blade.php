<?php
    //We need to clean up any lingering files in tmp for this form
    $folder = 'f'.$field->flid.'u'.\Auth::user()->id;
    $dir = config('app.base_path').'storage/app/tmpFiles/'.$folder;
    if(file_exists($dir)) {
        foreach (new \DirectoryIterator($dir) as $file) {
            if ($file->isFile()) {
                unlink($dir.'/'.$file->getFilename());
            }
        }
    }
?>

<div class="form-group mt-xl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::hidden($field->flid,'f'.$field->flid.'u'.\Auth::user()->id) !!}
</div>

<section class="filenames filenames-{{$field->flid}}-js mt-m">
    <div class="form-group mt-sm uploaded-file">
        <a href="#" class="upload-fileup-js" flid="{{$field->flid}}">
            <i class="icon icon-arrow-up"></i>
        </a>
        <a href="#" class="upload-filedown-js" flid="{{$field->flid}}">
            <i class="icon icon-arrow-down"></i>
        </a>
        <span class="ml-sm">TestFile.exe</span>
        <a href="#" class="upload-filedelete-js ml-sm" flid="{{$field->flid}}">
            <i class="icon icon-trash danger"></i>
        </a>
    </div>
</section>

<div class="form-group mt-m new-object-button">
    <input type="button" class="kora-file-button-js" value="Add New File" flid="{{$field->flid}}" >
    <input type="file" name="file{{$field->flid}}[]" class="kora-file-upload-js hidden"
           data-url="{{ config('app.url') }}saveTmpFile/{{$field->flid}}" multiple>
</div>

<div id="form-group mt-xl">
    <div class="file-upload-progress progress-bar-{{$field->flid}}-js"></div>
</div>