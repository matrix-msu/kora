<div class="record-data-card">
    <div class="gallery-field-display">
        @foreach(explode('[!]',$typedField->images) as $img)
            @if($img != '')
                <?php
                $name = explode('[Name]',$img)[1];
                $link = action('FieldAjaxController@getImgDisplay',['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name, 'type' => 'medium']);
                ?>
                <div><img class="img-responsive" src="{{$link}}" alt="{{$name}}"></div>
            @endif
        @endforeach
    </div>
</div>