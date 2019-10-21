@php
    $value = array();

    //If this is an edit, we need to move things around
    if($editRecord && !is_null($record->{$flid}))
        $value = json_decode($record->{$flid},true);
@endphp

<div class="form-group file-input-form-group audio-input-form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>

    <label for="{{$flid}}" class="file-label file-label-js">
        <div class="file-cards-container file-cards-container-js filenames-{{$flid}}-js preset-clear-file-js">
            @foreach($value as $file)
                @php
                    $name = $file['name'];
                @endphp
                <div class="card file-card file-card-js">
                    <input type="hidden" name="{{$flid}}[]" value="{{$name}}">
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
                            <span class="title">{{$name}}</span>
                        </div>

                        <div class="card-toggle-wrap">
                            <a href="#" class="file-delete upload-filedelete-js ml-sm tooltip" tooltip="Remove Audio" data-url="{{ url("deleteTmpFile/$form->id/$flid/".urlencode($name)) }}">
                                <i class="icon icon-trash danger"></i>
                            </a>
                        </div>

                        <textarea type="text" name="file_captions{{$flid}}[]" class="caption autosize-js" placeholder="Enter caption here">{{ $file['caption'] }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="progress-bar-div">
            <div class="file-upload-progress progress-bar-{{$flid}}-js"></div>
        </div>

        <div class="directions directions-not-empty-js">
            <p class="mb-m">Drag & Drop Another File Here</p>
            <p class="text-green">Or Select Another File</p>
        </div>

        <div class="directions directions-empty-js active">
            <p class="mb-m">Drag & Drop a File Here</p>
            <p class="text-green">Or Select a File</p>
        </div>
    </label>

    <input type="file" flid="{{$flid}}" id="{{$flid}}" name="file{{$flid}}[]" class="kora-file-upload-js hidden"
           data-url="{{ url("saveTmpFile/$form->id/$flid") }}" multiple>
</div>
