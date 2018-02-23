<div class="record card all active" id="{{$record->id}}">
    <div class="header active">
        <div class="left pl-m">
            <a class="title underline-middle-hover" href="{{ action("RecordController@show",
                ["pid" => $record->pid, "fid" => $record->fid, "rid" => $record->rid]) }}">
                <span class="name">{{$record->kid}}</span>
                <i class="icon icon-arrow-right"></i>
            </a>
        </div>

        <div class="card-toggle-wrap">
            <a href="#" class="card-toggle record-toggle-js">
                <i class="icon icon-chevron active"></i>
            </a>
        </div>
    </div>

    <div class="content active">
        <div class="description">
            @foreach(\App\Http\Controllers\PageController::getFormLayout($record->fid) as $page)
                <section class="record-page mt-xxxl">
                    <div class="record-page-title">{{$page["title"]}}</div>
                    <div class="record-page-spacer mt-xs"></div>
                    @foreach($page["fields"] as $field)
                        @if($field->viewresults)
                            <div class="field-title mt-xl">{{$field->name}}: </div>

                            <section class="field-data">
                                {{--<?php $typedField = $field->getTypedField(); ?>--}}
                                {{--@include($typedField::FIELD_DISPLAY_VIEW, ['field' => $field])--}}
                            </section>
                        @endif
                    @endforeach
                </section>
            @endforeach
        </div>
    </div>
</div>