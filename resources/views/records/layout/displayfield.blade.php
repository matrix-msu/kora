<div>
    <span><b>{{ $field->name }}:</b> </span>
    <span>
        @if($field->type=='Text')
            @foreach($record->textfields as $tf)
                @if($tf->flid == $field->flid)
                    {{ $tf->text }}
                @endif
            @endforeach
        @elseif($field->type=='Rich Text')
            @foreach($record->richtextfields as $rtf)
                @if($rtf->flid == $field->flid)
                    <?php echo $rtf->rawtext ?>
                @endif
            @endforeach
        @elseif($field->type=='Number')
            @foreach($record->numberfields as $nf)
                @if($nf->flid == $field->flid)
                    <?php
                        echo $nf->number;
                        if($nf->number!='')
                            echo ' '.\App\Http\Controllers\FieldController::getFieldOption($field,'Unit');
                    ?>
                @endif
            @endforeach
        @elseif($field->type=='List')
            @foreach($record->listfields as $lf)
                @if($lf->flid == $field->flid)
                    {{ $lf->option }}
                @endif
            @endforeach
        @elseif($field->type=='Multi-Select List')
            @foreach($record->multiselectlistfields as $mslf)
                @if($mslf->flid == $field->flid)
                    @foreach(explode('[!]',$mslf->options) as $opt)
                        <div>{{ $opt }}</div>
                    @endforeach
                @endif
            @endforeach
        @elseif($field->type=='Generated List')
            @foreach($record->generatedlistfields as $glf)
                @if($glf->flid == $field->flid)
                    @foreach(explode('[!]',$glf->options) as $opt)
                        <div>{{ $opt }}</div>
                    @endforeach
                @endif
            @endforeach
        @elseif($field->type=='Date')
            @foreach($record->datefields as $df)
                @if($df->flid == $field->flid)
                    @if($df->circa==1 && \App\Http\Controllers\FieldController::getFieldOption($field,'Circa')=='Yes')
                        {{'circa '}}
                    @endif
                    @if($df->month==0 && $df->day==0)
                        {{$df->year}}
                    @elseif($df->day==0)
                        {{ $df->month.' '.$df->year }}
                    @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='MMDDYYYY')
                        {{$df->month.'-'.$df->day.'-'.$df->year}}
                    @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='DDMMYYYY')
                        {{$df->day.'-'.$df->month.'-'.$df->year}}
                    @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='YYYYMMDD')
                        {{$df->year.'-'.$df->month.'-'.$df->day}}
                    @endif
                    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Era')=='Yes')
                        {{' '.$df->era}}
                    @endif
                @endif
            @endforeach
        @elseif($field->type=='Schedule')
            @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Calendar')=='No')
                @foreach($record->schedulefields as $sf)
                    @if($sf->flid == $field->flid)
                        @foreach(explode('[!]',$sf->events) as $event)
                            <div>{{ $event }}</div>
                        @endforeach
                    @endif
                @endforeach
            @endif
        @endif
    </span>
</div>