<?php
    //We need to clean up any lingering files in tmp for this form
    $folder = 'f'.$field->flid.'u'.\Auth::user()->id;
    $dir = config('app.base_path').'storage/app/tmpFiles/'.$folder;
    if(file_exists($dir)) {
        //clear tmp and thumb folders
        foreach (new \DirectoryIterator($dir) as $file) {
            if ($file->isFile()) {
                unlink($dir.'/'.$file->getFilename());
            }
        }
        if(file_exists($dir.'/thumbnail')) {
            foreach (new \DirectoryIterator($dir.'/thumbnail') as $file) {
                if ($file->isFile()) {
                    unlink($dir.'/thumbnail/'.$file->getFilename());
                }
            }
        }
        if(file_exists($dir.'/medium')) {
            foreach (new \DirectoryIterator($dir.'/medium') as $file) {
                if ($file->isFile()) {
                    unlink($dir.'/medium/'.$file->getFilename());
                }
            }
        }
    }
?>

<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::hidden($field->flid,'f'.$field->flid.'u'.\Auth::user()->id) !!}
</div>

<section class="filenames filenames-{{$field->flid}}-js">
</section>

<div class="form-group progress-bar-div">
    <div class="file-upload-progress progress-bar-{{$field->flid}}-js"></div>
</div>

<div class="form-group new-object-button low-margin">
    <input type="button" class="kora-file-button-js" value="Add New File" flid="{{$field->flid}}" >
    <input type="file" name="file{{$field->flid}}[]" class="kora-file-upload-js hidden"
           data-url="{{ config('app.url') }}saveTmpFile/{{$field->flid}}" multiple>
</div>