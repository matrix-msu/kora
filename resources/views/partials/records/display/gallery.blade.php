@php
    $images = $typedField->processDisplayData($field, $value);
    $single = (sizeof($images) <= 1);
@endphp

<div class="record-data-card">
    <div class="gallery-field-display gallery-field-display-js {{ ($single ? 'single' : '') }}">
        @foreach($images as $img)
            @php
                $ogName = $img['original_name'];
                $locName = $img['local_name'];
                $link = action('FieldAjaxController@publicRecordFile',['filename' => $locName]);
            @endphp
            <div class="slide slide-js">
                <img class="slide-img slide-img-js" src="{{$link}}" alt="{{$ogName}}" resLink="{{$link}}">
            </div>
        @endforeach
    </div>

    @if(!$single)
        <div class="gallery-controls">
            <div class="field-btn field-btn-circle prev-button prev-button-js">
                <i class="icon icon-chevron"></i>
            </div>

            <div class="dots dots-js"></div>

            <div class="field-btn field-btn-circle next-button next-button-js">
                <i class="icon icon-chevron"></i>
            </div>
        </div>
    @endif

    <div class="caption-container caption-container-js">
        @foreach($images as $index => $img)
            <div class="caption caption-js {{ ($index == 0 ? 'active' : '') }}">
                {{ $img['caption'] }}
                @php
                    $locName = $img['local_name'];
                    $link = action('FieldAjaxController@publicRecordFile',['filename' => $locName]);
                @endphp
                <div>Public URL: {{ $link }}</div>
            </div>
        @endforeach
    </div>
    <a class="caption-more caption-more-js underline-middle-hover" showing="less" href="#">Show Full Caption</a>

    <div class="field-sidebar gallery-sidebar gallery-sidebar-js {{ ($single ? 'single' : '') }}">
        <div class="top">
            <div class="field-btn external-button-js">
                <i class="icon icon-external-link"></i>
            </div>

            <a href="{{ ($single ? action('FieldAjaxController@getFileDownload', ['kid' => $record->kid, 'filename' => $images[0]['local_name']]) : action('FieldAjaxController@getZipDownload', ['kid' => $record->kid])) }}"
               class="field-btn">
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
        <div class="body">
            <a href="#" class="modal-toggle modal-toggle-js field-btn">
                <i class="icon icon-cancel"></i>
            </a>

            <div class="gallery-field-display gallery-field-display-js {{($single && $images[0]['caption'] == "") ? 'full-height' : ''}}">
                @foreach($images as $img)
                    @php
                        $link = action('FieldAjaxController@publicRecordFile',['filename' => $locName]);
                    @endphp
                    <div class="slide slide-js">
                        <img class="slide-img slide-img-js" src="{{$link}}" alt="{{$img['original_name']}}">
                    </div>
                @endforeach
            </div>

            @if(!$single)
                <div class="gallery-controls">
                    <div class="field-btn field-btn-circle prev-button prev-button-js">
                        <i class="icon icon-chevron"></i>
                    </div>

                    @if (!$single)
                        <div class="dots dots-js"></div>
                    @endif

                    <div class="field-btn field-btn-circle next-button next-button-js">
                        <i class="icon icon-chevron"></i>
                    </div>
                </div>
            @endif

            <div class="caption-container caption-container-js">
                @foreach($images as $index => $img)
                    <div class="caption caption-js modal-caption-js {{ ($index == 0 ? 'active' : '') }}">
                        {{ $img['caption'] }}
                        @php
                            $locName = $img['local_name'];
                            $link = action('FieldAjaxController@publicRecordFile',['filename' => $locName]);
                        @endphp
                        <div>Public URL: {{ $link }}</div>
                    </div>
                @endforeach
            </div>
            <a class="caption-more caption-more-js underline-middle-hover" showing="less" href="#">Show Full Caption</a>
        </div>
    </div>
</div>
