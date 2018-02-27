@if(\App\Http\Controllers\FieldController::getFieldOption($field,'Calendar')=='No')
    @foreach(App\ScheduleField::eventsToOldFormat($typedField->events()->get()) as $event)
        <div>{{ $event }}</div>
    @endforeach
@else
    <div id='calendar{{$field->flid}}'></div>
    <script>
        jQuery('#calendar{{$field->flid}}').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            events: [
                    @foreach(App\ScheduleField::eventsToOldFormat($typedField->events()->get()) as $event)
                {
                    <?php
                        $nameTime = explode(': ',$event);
                        $times = explode(' - ',$nameTime[1]);
                        $allDay = true;
                        if(strpos($nameTime[1],'PM') | strpos($nameTime[1],'AM')){
                            $allDay = false;
                        }
                        ?>
                    title: '{{ $nameTime[0] }}',
                    start: '{{ $times[0] }}',
                    end: '{{ $times[1] }}',
                    @if($allDay)
                    allDay: true
                    @else
                    allDay: false
                    @endif
                },
                @endforeach
            ]
        });
    </script>
@endif