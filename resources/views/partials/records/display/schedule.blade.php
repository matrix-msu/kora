@if(\App\Http\Controllers\FieldController::getFieldOption($field,'Calendar')=='No')
    @foreach(App\ScheduleField::eventsToOldFormat($typedField->events()->get()) as $event)
        <div>{{ $event }}</div>
    @endforeach
@else
    <div class="schedule-cal-js">
        @foreach(App\ScheduleField::eventsToOldFormat($typedField->events()->get()) as $event)
            <?php
                $nameTime = explode(': ',$event);
                $times = explode(' - ',$nameTime[1]);
                $allDay = true;
                if(strpos($nameTime[1],'PM') | strpos($nameTime[1],'AM'))
                    $allDay = false;
            ?>
            <span class="schedule-event-js hidden" event-title="{{$nameTime[0]}}" event-start="{{$times[0]}}"
                  event-end="{{$times[1]}}" event-all-day="{{$allDay}}"></span>
        @endforeach
    </div>
@endif