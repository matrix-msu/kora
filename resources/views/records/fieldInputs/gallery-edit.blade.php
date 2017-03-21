<?php
$value = array();

//clear the tmp files
$folder = 'f'.$field->flid.'u'.\Auth::user()->id;
$dirTmp = env('BASE_PATH').'storage/app/tmpFiles/'.$folder;
if(file_exists($dirTmp)) {
    foreach (new \DirectoryIterator($dirTmp) as $file) {
        if ($file->isFile()) {
            unlink($dirTmp.'/'.$file->getFilename());
        }
    }
}else{
    mkdir($dirTmp,0775,true);
    mkdir($dirTmp.'/thumbnail/',0775,true);
    mkdir($dirTmp.'/medium/',0775,true);
}
if(!is_null($gallery)){
    $names = explode('[!]',$gallery->images);
    foreach($names as $key => $file){
        $names[$key] = explode('[Name]',$file)[1];
    }
    //move things over from storage to tmp
    $dir = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid;
    if(file_exists($dir)) {
        foreach (new \DirectoryIterator($dir) as $file) {
            if ($file->isFile() && in_array($file->getFilename(),$names)) {
                copy($dir.'/'.$file->getFilename(),$dirTmp.'/'.$file->getFilename());
                copy($dir.'/thumbnail/'.$file->getFilename(),$dirTmp.'/thumbnail/'.$file->getFilename());
                copy($dir.'/medium/'.$file->getFilename(),$dirTmp.'/medium/'.$file->getFilename());
                array_push($value,$file->getFilename());
            }
        }
    }
}
?>
<div class="form-group" id="fileDiv{{$field->flid}}">
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    <span class="btn btn-success fileinput-button">
        <span>{{trans('records_fieldInput.addimg')}}...</span>
        <input id="file{{$field->flid}}" type="file" name="file{{$field->flid}}[]"
               data-url="{{ env('BASE_URL') }}public/saveTmpFile/{{$field->flid}}" multiple>
        {!! Form::hidden($field->flid,'f'.$field->flid.'u'.\Auth::user()->id) !!}
    </span>
    <br/><br/>
    <div id="progress">
        <div class="bar{{$field->flid}} progress-bar" style="width: 0%; height:18px; background:green;"></div>
    </div>
    <br/>
    <div id="file_error{{$field->flid}}" style="color: red"></div>
    <div id="filenames{{$field->flid}}">
        @foreach($value as $file)
            <div id="uploaded_file_div">
                {{$file}}
                <input type="hidden" name="file{{$field->flid}}[]" value ="{{$file}}">
                <button id="up" class="btn btn-default" type="button">{{trans('records_fieldInput.up')}}</button>
                <button id="down"class="btn btn-default" type="button">{{trans('records_fieldInput.down')}}</button>
                <button class="btn btn-danger delete" type="button" data-type="DELETE"
                        data-url="{{env('BASE_URL')}}public/deleteTmpFile/{{$folder}}/{{urlencode($file)}}">
                    <i class="glyphicon glyphicon-trash"></i>
                    {{trans('records_fieldInput.delete')}}
                </button>
            </div>
        @endforeach
    </div>
</div>

<script>
    $('#file{{$field->flid}}').fileupload({
        dataType: 'json',
        singleFileUploads: false,
        done: function (e, data) {
            $.each(data.result['file{{$field->flid}}'], function (index, file) {
                var del = '<div id="uploaded_file_div">' + file.name + ' ';
                del += '<input type="hidden" name="file{{$field->flid}}[]" value ="'+file.name+'">';
                del += '<button id="up" class="btn btn-default" type="button">{{trans('records_fieldInput.up')}}</button>';
                del += '<button id="down"class="btn btn-default" type="button">{{trans('records_fieldInput.down')}}</button>';
                del += '<button class="btn btn-danger delete" type="button" data-type="'+file.deleteType+'" data-url="'+file.deleteUrl+'" >';
                del += '<i class="glyphicon glyphicon-trash" /> {{trans('records_fieldInput.delete')}}</button>';
                del += '</div>';

                $('#filenames{{$field->flid}}').append(del);
            });
        },
        fail: function (e,data){
            var error = data.jqXHR['responseText'];

            if(error=='InvalidType'){
                $('#file_error{{$field->flid}}').text('{{trans('records_fieldInput.invalid')}}.');
            } else if(error=='TooManyFiles'){
                $('#file_error{{$field->flid}}').text('{{trans('records_fieldInput.max')}} {{\App\Http\Controllers\FieldController::getFieldOption($field,'MaxFiles')}} {{trans('records_fieldInput.submit')}}.');
            } else if(error=='MaxSizeReached'){
                $('#file_error{{$field->flid}}').text('{{trans('records_fieldInput.exceed')}} {{\App\Http\Controllers\FieldController::getFieldOption($field,'FieldSize')}} kb');
            }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar{{$field->flid}}').css(
                    'width',
                    progress + '%'
            );
        }
    });
    $('#filenames{{$field->flid}}').on('click','.delete',function(){
        var div = $(this).parent();
        $.ajax({
            url: $(this).attr('data-url'),
            type: 'DELETE',
            dataType: 'json',
            data: {
                "_token": '{{csrf_token()}}'
            },
            success: function (data) {
                div.remove();
            }
        });
    });

    $('#filenames{{$field->flid}}').on('click','#up',function(){
        fileDiv = $(this).parent('#uploaded_file_div');

        if(fileDiv.prev('#uploaded_file_div').length==1){
            prevDiv = fileDiv.prev('#uploaded_file_div');

            fileDiv.insertBefore(prevDiv);
        }
    });

    $('#filenames{{$field->flid}}').on('click','#down',function(){
        fileDiv = $(this).parent('#uploaded_file_div');

        if(fileDiv.next('#uploaded_file_div').length==1){
            nextDiv = fileDiv.next('#uploaded_file_div');

            fileDiv.insertAfter(nextDiv);
        }
    });

    function numFiles(flid){
        var cnt = 0;
        $("#filenames"+flid).find(".button").each(function () {
            cnt++;
        });

        return cnt;
    }
</script>