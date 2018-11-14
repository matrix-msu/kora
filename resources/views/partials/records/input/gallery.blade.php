<?php
    $value = array();

    //We need to clean up any lingering files in tmp for this form
    $folder = 'f'.$field->flid.'u'.\Auth::user()->id;
    $dirTmp = storage_path('app/tmpFiles/'.$folder);
    if(file_exists($dirTmp)) {
        //clear tmp folder
        foreach(new \DirectoryIterator($dirTmp) as $file) {
            if($file->isFile())
                unlink($dirTmp.'/'.$file->getFilename());
        }
    } else {
        mkdir($dirTmp,0775,true); //Make it!
    }
    if(file_exists($dirTmp.'/medium')) {
        //clear tmp folder
        foreach(new \DirectoryIterator($dirTmp) as $file) {
            if($file->isFile())
                unlink($dirTmp.'/medium/'.$file->getFilename());
        }
    } else {
        mkdir($dirTmp.'/medium',0775,true); //Make it!
    }
    if(file_exists($dirTmp.'/thumbnail')) {
        //clear tmp folder
        foreach(new \DirectoryIterator($dirTmp) as $file) {
            if($file->isFile())
                unlink($dirTmp.'/thumbnail/'.$file->getFilename());
        }
    } else {
        mkdir($dirTmp.'/thumbnail',0775,true); //Make it!
    }

    //If this is an edit, we need to move things around
    if($editRecord && $hasData) {
        $names = explode('[!]',$typedField->images);
        foreach($names as $key => $file) {
            $name = explode('[Name]',$file)[1];
            $names[$key] = $name;
            array_push($value,$name);
        }
        //move things over from storage to tmp
        $dir = storage_path('app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid);
        if(file_exists($dir)) {
            foreach(new \DirectoryIterator($dir) as $file) {
                if($file->isFile() && in_array($file->getFilename(),$names)) {
                    copy($dir.'/'.$file->getFilename(),$dirTmp.'/'.$file->getFilename());
                    if(file_exists($dir.'/medium/'.$file->getFilename()))
                        copy($dir.'/medium/'.$file->getFilename(),$dirTmp.'/medium/'.$file->getFilename());
                    if(file_exists($dir.'/thumbnail/'.$file->getFilename()))
                        copy($dir.'/thumbnail/'.$file->getFilename(),$dirTmp.'/thumbnail/'.$file->getFilename());
                }
            }
        }
    }
?>


<div class="form-group file-input-form-group gallery-input-form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>

    {!! Form::hidden($field->flid,'f'.$field->flid.'u'.\Auth::user()->id, ['id'=>$field->flid]) !!}

    <label for="file{{$field->flid}}" class="file-label file-label-js">
        <div class="file-cards-container file-cards-container-js filenames-{{$field->flid}}-js preset-clear-file-js">
            @foreach($value as $file)
                <div class="card file-card file-card-js">
                    <input type="hidden" name="file{{$field->flid}}[]" value ="{{$file}}">
                    <div class="header">
                        <div class="left">
                            <div class="move-actions">
                                <a class="action move-action-js up-js" href="">
                                    <i class="icon icon-arrow-up"></i>
                                </a>
                                <a class="action move-action-js down-js" href="">
                                    <i class="icon icon-arrow-down"></i>
                                </a>
                            </div>
                            <span class="title">{{$file}}</span>
                        </div>

                        <div class="card-toggle-wrap">
                            <a href="#" class="file-delete upload-filedelete-js ml-sm tooltip" tooltip="Remove Image" data-url="{{ url('deleteTmpFile/'.$folder.'/'.urlencode($file)) }}">
                                <i class="icon icon-trash danger"></i>
                            </a>
                        </div>

                        <textarea type="text" name="caption[]" class="caption autosize-js" placeholder="Enter caption here"></textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="progress-bar-div">
            <div class="file-upload-progress progress-bar-{{$field->flid}}-js"></div>
        </div>

        <div class="directions">
            <p class="mb-m">Drag & Drop Another File Here</p>
            <p class="text-green">Or Select Another File</p>
        </div>
        {{--<input type="button" id="file-{{$field->flid}}" class="kora-file-button-js" value="Add New File" flid="{{$field->flid}}" >--}}
    </label>
    
    <input type="file" flid="{{$field->flid}}" id="file{{$field->flid}}" name="file{{$field->flid}}[]" class="kora-file-upload-js hidden"
               data-url="{{ url('saveTmpFile/'.$field->flid) }}" multiple>
</div>

{{--<div class="form-group mt-xxxl">--}}
    {{--<label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>--}}
    {{--<span class="error-message"></span>--}}
    {{--{!! Form::hidden($field->flid,'f'.$field->flid.'u'.\Auth::user()->id, ['id'=>$field->flid]) !!}--}}
{{--</div>--}}

{{--<section class="filenames filenames-{{$field->flid}}-js preset-clear-file-js">--}}
    {{--@foreach($value as $file)--}}
        {{--<div class="form-group mt-xxs uploaded-file">--}}
            {{--<input type="hidden" name="file{{$field->flid}}[]" value ="{{$file}}">--}}
            {{--<a href="#" class="upload-fileup-js">--}}
                {{--<i class="icon icon-arrow-up"></i>--}}
            {{--</a>--}}
            {{--<a href="#" class="upload-filedown-js">--}}
                {{--<i class="icon icon-arrow-down"></i>--}}
            {{--</a>--}}
            {{--<span class="ml-sm">{{$file}}</span>--}}
            {{--<a href="#" class="upload-filedelete-js ml-sm" data-url="{{ url('deleteTmpFile/'.$folder.'/'.urlencode($file)) }}">--}}
                {{--<i class="icon icon-trash danger"></i>--}}
            {{--</a>--}}
        {{--</div>--}}
    {{--@endforeach--}}
{{--</section>--}}

{{--<div class="form-group progress-bar-div">--}}
    {{--<div class="file-upload-progress progress-bar-{{$field->flid}}-js"></div>--}}
{{--</div>--}}

{{--<div class="form-group new-object-button low-margin">--}}
    {{--<input type="button" class="kora-file-button-js" value="Add New File" flid="{{$field->flid}}" >--}}
    {{--<input type="file" name="file{{$field->flid}}[]" class="kora-file-upload-js hidden"--}}
           {{--data-url="{{ url('saveTmpFile/'.$field->flid) }}" multiple>--}}
{{--</div>--}}