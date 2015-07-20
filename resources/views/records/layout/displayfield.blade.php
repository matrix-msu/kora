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
        @endif
        @elseif($field->type=='Multi-Select List')
            @foreach($record->multiselectlistfields as $mslf)
                @if($mslf->flid == $field->flid)
                    {{ $mslf->options }}
                @endif
            @endforeach
        @endif
    </span>
</div>