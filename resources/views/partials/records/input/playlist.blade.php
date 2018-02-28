<?php
    $value = array();

    //We need to clean up any lingering files in tmp for this form
    $folder = 'f'.$field->flid.'u'.\Auth::user()->id;
    $dirTmp = config('app.base_path').'storage/app/tmpFiles/'.$folder;
    if(file_exists($dirTmp)) {
        foreach (new \DirectoryIterator($dirTmp) as $file) {
            if ($file->isFile()) {
                unlink($dirTmp.'/'.$file->getFilename());
            }
        }
    } else {
        mkdir($dirTmp,0775,true); //Make it!
    }

    //If this is an edit, we need to move things around
    if($editRecord && $hasData){
        $names = explode('[!]',$typedField->audio);
        foreach($names as $key => $file){
            $names[$key] = explode('[Name]',$file)[1];
        }
        //move things over from storage to tmp
        $dir = config('app.base_path').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid;
        if(file_exists($dir)) {
            foreach (new \DirectoryIterator($dir) as $file) {
                if ($file->isFile() && in_array($file->getFilename(),$names)) {
                    copy($dir.'/'.$file->getFilename(),$dirTmp.'/'.$file->getFilename());
                    array_push($value,$file->getFilename());
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
    @foreach($value as $file)
        <div class="form-group mt-xxs uploaded-file">
            <input type="hidden" name="file{{$field->flid}}[]" value ="{{$file}}">
            <a href="#" class="upload-fileup-js">
                <i class="icon icon-arrow-up"></i>
            </a>
            <a href="#" class="upload-filedown-js">
                <i class="icon icon-arrow-down"></i>
            </a>
            <span class="ml-sm">{{$file}}</span>
            <a href="#" class="upload-filedelete-js ml-sm" data-url="{{config('app.url')}}deleteTmpFile/{{$folder}}/{{urlencode($file)}}">
                <i class="icon icon-trash danger"></i>
            </a>
        </div>
    @endforeach
</section>

<div class="form-group progress-bar-div">
    <div class="file-upload-progress progress-bar-{{$field->flid}}-js"></div>
</div>

<div class="form-group new-object-button low-margin">
    <input type="button" class="kora-file-button-js" value="Add New File" flid="{{$field->flid}}" >
    <input type="file" name="file{{$field->flid}}[]" class="kora-file-upload-js hidden"
           data-url="{{ config('app.url') }}saveTmpFile/{{$field->flid}}" multiple>
</div>