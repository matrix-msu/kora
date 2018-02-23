<div class="page card all active">
    <div class="header active">
        <div class="left pl-m">
            <a class="title">
                <span class="name page-title">{{$page["title"]}}</span>
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
            @foreach($page["fields"] as $field)
                @if($field->viewable)
                    <div class="field-title mt-xl">{{$field->name}}: </div>

                    <section class="field-data">
                        {{--<?php $typedField = $field->getTypedField(); ?>--}}
                        {{--@include($typedField::FIELD_DISPLAY_VIEW, ['field' => $field])--}}
                    </section>
                @endif
            @endforeach
        </div>
    </div>
</div>