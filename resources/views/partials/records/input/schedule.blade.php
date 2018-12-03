<?php
    if($editRecord && $hasData) {
        $selected = App\ScheduleField::eventsToOldFormat($typedField->events()->get());
        $listOpts = array();
        foreach($selected as $val){
            $listOpts[$val] = $val;
        }
    } else if($editRecord) {
        $selected = null;
        $listOpts = array();
    } else {
        $selected = explode('[!]',$field->default);
        $listOpts = \App\ScheduleField::getDateList($field);
    }
?>
<div class="form-group schedule-form-group schedule-form-group-js schedule-{{$field->flid}}-js mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>

    <div class="form-input-container">
        <p class="directions">Add Default Locations below, and order them via drag & drop or their arrow icons.</p>

        <div class="schedule-card-container schedule-card-container-js mb-xxl">
            @foreach($listOpts as $opt)
                <?php
                $event = explode(': ', $opt, 2);
                $name = $event[0];
                $times = $event[1];
                ?>
                <div class="card schedule-card schedule-card-js">
                    <input type="hidden" class="list-option-js" name="{{$field->flid}}[]" value="{{$opt}}'">
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
                            <a class="schedule-delete schedule-delete-js tooltip" tooltip="Delete Event" href=""><i class="icon icon-trash"></i></a>
                        </div>
                    </div>
                    <div class="content"><p class="event-time">{{$times}}</p></div>
                </div>
            @endforeach
        </div>

        <section class="new-object-button">
            <input flid="{{$field->flid}}" type="button" class="add-new-default-event-js" value="Create New Event"
                   start="{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}"
                   end="{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}">        </section>
    </div>
</div>

{{--<div class="form-group mt-xxxl">--}}
    {{--<label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>--}}
    {{--<span class="error-message"></span>--}}
    {{--{!! Form::select($field->flid.'[]', $listOpts, $selected,['class' => 'multi-select  '.$field->flid.'-event-js preset-clear-chosen-js',--}}
        {{--'Multiple', 'data-placeholder' => "Add Events Below", 'id' => 'list'.$field->flid]) !!}--}}
{{--</div>--}}

{{--<section class="new-object-button form-group mt-xl">--}}
    {{--<input flid="{{$field->flid}}" type="button" class="add-new-default-event-js" value="Create New Event"--}}
        {{--start="{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}"--}}
        {{--end="{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}">--}}
{{--</section>--}}