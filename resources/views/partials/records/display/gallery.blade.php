<div class="record-data-card">
    <div class="gallery-field-display gallery-field-display-js">
        @foreach(explode('[!]',$typedField->images) as $img)
            @if($img != '')
                <?php
                $name = explode('[Name]',$img)[1];
                $link = action('FieldAjaxController@getImgDisplay',['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name, 'type' => 'medium']);
                ?>
                <div class="slide slide-js">
                    <img class="slide-img slide-img-js" src="{{$link}}" alt="{{$name}}">
                </div>
            @endif
        @endforeach
    </div>

    <div class="gallery-controls">
        <div class="field-btn field-btn-circle prev-button prev-button-js">
            <i class="icon icon-chevron"></i>
        </div>

        <div class="dots dots-js"></div>

        <div class="field-btn field-btn-circle next-button next-button-js">
            <i class="icon icon-chevron"></i>
        </div>
    </div>

    <div class="field-sidebar gallery-sidebar gallery-sidebar-js">
        <div class="top">
            <div class="field-btn external-button-js">
                <i class="icon icon-external-link"></i>
            </div>

            <a href="{{ action('FieldAjaxController@getZipDownload', ['flid' => $field->flid, 'rid' => $record->rid, 'filename' => 'gallery']) }}" class="field-btn external-button-js">
                <i class="icon icon-download"></i>
            </a>
        </div>

        <div class="bottom">
            <div class="field-btn full-screen-button-js">
                <i class="icon icon-maximize"></i>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-js modal-mask gallery-modal gallery-modal-js full-screen-modal">
    <div class="content">
        <div class="header">
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <div class="gallery-field-display gallery-field-display-js">
                @foreach(explode('[!]',$typedField->images) as $img)
                    @if($img != '')
                        <?php
                        $name = explode('[Name]',$img)[1];
                        $link = action('FieldAjaxController@getImgDisplay',['flid' => $field->flid, 'rid' => $record->rid, 'filename' => $name, 'type' => 'medium']);
                        ?>
                        <div class="slide slide-js">
                            <img class="slide-img slide-img-js" src="{{$link}}" alt="{{$name}}">
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="gallery-controls">
                <div class="field-btn field-btn-circle prev-button prev-button-js">
                    <i class="icon icon-chevron"></i>
                </div>

                <div class="dots dots-js"></div>

                <div class="field-btn field-btn-circle next-button next-button-js">
                    <i class="icon icon-chevron"></i>
                </div>
            </div>
        </div>
    </div>
</div>