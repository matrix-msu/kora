@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <span><h1>{{ $form->name }}</h1></span>

    @include('partials.adminpanel')

    <hr/>

    <div><b>Internal Name:</b> {{ $form->slug }}</div>
    <div><b>Description:</b> {{ $form->description }}</div>

    @if (\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
        <form action="{{action('FormGroupController@index', ['fid'=>$form->fid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">Manage Groups</button>
        </form>
        <form action="{{action('RevisionController@index', ['pid'=>$form->pid, 'fid'=>$form->fid])}}" style="display: inline">
            <button type="submit" class="btn btn-default">Revision History</button>
        </form>
    @endif

    <div>
        @if(\Auth::user()->canIngestRecords($form))
        <a href="{{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }}">[New Record]</a>
        @endif
    </div>
    <hr/>
    <h2>Records</h2>

    @foreach($form->records as $record)
        <div class="panel panel-default">
            <div>
                <b>Record:</b> <a href="{{ action('RecordController@show',['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]) }}">{{ $record->kid }}</a>
            </div>
            @foreach($form->fields as $field)
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
                                    {{  $lf->option }}
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
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endforeach
@stop