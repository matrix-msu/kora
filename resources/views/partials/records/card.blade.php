<div class="record card all active" id="{{$record->id}}">
    <div class="header active">
        <div class="left pl-m">
            <a class="title">
                <span class="name">{{$record->kid}}</span>
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
            @foreach($form->fields as $field)
                @if($field->viewresults)
                    <div class="field-title">{{$field->name}}: </div>

                    <section class="field-data mb-xl">
                        {{--<?php $typedField = $field->getTypedField(); ?>--}}
                        {{--@include($typedField::FIELD_DISPLAY_VIEW, ['field' => $field])--}}
                    </section>
                @endif
            @endforeach
        </div>
    </div>
</div>