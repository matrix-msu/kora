@foreach($revisions as $revision)
    <?php $data = json_decode($revision->data, true);
          $oldData = json_decode($revision->oldData, true);
    ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                @if($message == 'Recent')
                <a href="javascript:void(0)" onclick='showRecordRevisions(0, {{$revision->rid}})'>{{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}</a>
                @else
                    {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                @endif
                <span class="pull-right">{{ ucfirst($revision->type) }}</span>
            </div>
            <div class="collapseTest" style="display: none">
                <div>Revision Type: {{$revision->type}}</div>
                <div>Rollback: @if($revision->rollback)True @else False @endif</div>
                @if($revision->rollback)
                    <a href="javascript:void(0)" onclick="rollback({{$revision->id}})">[Rollback Record]</a>
                @endif
                @if($revision->type != 'create')
                <div><b>Before</b></div>
                @elseif($revision->type == 'create')
                <div><b>After</b></div>
                @endif
                <div class="panel panel-default">
                    <div>
                        <b>Record: </b> {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                    </div>
                    @foreach($form->fields()->get() as $field)
                        <div>
                            @if($field->type=='Text')
                                @if($data['textfields'][$field->flid]['data'] != $oldData['textfields'][$field->flid]['data'] || $revision->type == 'create' || $revision->type=='delete')
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($data['textfields']))
                                        {{$data['textfields'][$field->flid]['data']}}
                                    @endif
                                @endif
                            @elseif($field->type=='Rich Text')
                                @if($data['richtextfields'][$field->flid]['data'] != $oldData['richtextfields'][$field->flid]['data'] || $revision->type == 'create' || $revision->type=='delete')
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($data['richtextfields']))
                                        <?php echo $data['richtextfields'][$field->flid]['data']; ?>
                                    @endif
                                @endif
                            @elseif($field->type=='Number')
                                @if($data['numberfields'][$field->flid]['data'] != $oldData['numberfields'][$field->flid]['data'] || $revision->type == 'create' || $revision->type=='delete')
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($data['numberfields']))
                                        <?php
                                        echo $data['numberfields'][$field->flid]['data'];
                                        if($data['numberfields'][$field->flid]['data'] != '')
                                            echo ' '.App\Http\Controllers\FieldController::getFieldOption($field,'Unit');
                                        ?>
                                    @endif
                                @endif
                            @elseif($field->type=='List')
                                @if($data['listfields'][$field->flid]['data'] != $oldData['listfields'][$field->flid]['data'] || $revision->type == 'create' || $revision->type=='delete')
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($data['listfields']))
                                        {{$data['listfields'][$field->flid]['data']}}
                                    @endif
                                @endif
                            @elseif($field->type=='Multi-Select List')
                                @if($data['multiselectlistfields'][$field->flid]['data'] != $oldData['multiselectlistfields'][$field->flid]['data'] || $revision->type == 'create' || $revision->type=='delete')
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($data['multiselectlistfields']))
                                        @foreach(explode('[!]', $data['multiselectlistfields'][$field->flid]['data']) as $opt )
                                            <div>{{$opt}}</div>
                                        @endforeach
                                    @endif
                                @endif
                            @elseif($field->type=='Generated List')
                                @if($data['generatedlistfields'][$field->flid]['data'] != $oldData['generatedlistfields'][$field->flid]['data'] || $revision->type == 'create' || $revision->type=='delete')
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($data['generatedlistfields']))
                                        @foreach(explode('[!]', $data['generatedlistfields'][$field->flid]['data']) as $opt)
                                            <div>{{$opt}}</div>
                                        @endforeach
                                    @endif
                                @endif
                            @elseif($field->type=='Date')
                                @if($data['datefields'][$field->flid]['data'] != $oldData['datefields'][$field->flid]['data'] || $revision->type == 'create' || $revision->type=='delete')
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($data['datefields']))
                                        @if($data['datefields'][$field->flid]['data']['circa'] && \App\Http\Controllers\FieldController::getFieldOption($field,'Circa')=='Yes')
                                            {{'circa '}}
                                        @endif
                                        @if($data['datefields'][$field->flid]['data']['month'] == 0 && $data['datefields'][$field->flid]['data']['day'])
                                            {{$data['datefields'][$field->flid]['data']['year']}}
                                        @elseif($data['datefields'][$field->flid]['data']['day'] == 0)
                                            {{$data['datefields'][$field->flid]['data']['month'].' '.$data['datefields'][$field->flid]['data']['year']}}
                                        @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='MMDDYYYY')
                                            {{$data['datefields'][$field->flid]['data']['month'].'-'.$data['datefields'][$field->flid]['data']['day'].'-'.$data['datefields'][$field->flid]['data']['year']}}
                                        @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='DDMMYYYY')
                                            {{$data['datefields'][$field->flid]['data']['day'].'-'.$data['datefields'][$field->flid]['data']['month'].'-'.$data['datefields'][$field->flid]['data']['year']}}
                                        @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='YYYYMMDD')
                                            {{$data['datefields'][$field->flid]['data']['year'].'-'.$data['datefields'][$field->flid]['data']['month'].'-'.$data['datefields'][$field->flid]['data']['day']}}
                                        @endif
                                        @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Era')=='Yes')
                                            {{' '.$data['datefields'][$field->flid]['data']['era']}}
                                        @endif
                                    @endif
                                @endif
                            @endif

                        </div>
                    @endforeach
                </div>

                @if($revision->type != 'delete' && $revision->type != 'create')
                <div><b>After</b></div>
                <div class="panel panel-default">
                    <div>
                        <b>Record: </b> {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                    </div>

                    @foreach($form->fields()->get() as $field)
                        <div>
                            @if($field->type=='Text')
                                @if($data['textfields'][$field->flid]['data'] != $oldData['textfields'][$field->flid]['data'])
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($oldData['textfields']))
                                        {{$oldData['textfields'][$field->flid]['data']}}
                                    @endif
                                @endif
                            @elseif($field->type=='Rich Text')
                                @if($data['richtextfields'][$field->flid]['data'] != $oldData['richtextfields'][$field->flid]['data'])
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($oldData['richtextfields']))
                                        <?php echo $oldData['richtextfields'][$field->flid]['data']; ?>
                                    @endif
                                @endif
                            @elseif($field->type=='Number')
                                @if($data['numberfields'][$field->flid]['data'] != $oldData['numberfields'][$field->flid]['data'])
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($oldData['numberfields']))
                                        <?php
                                        echo $oldData['numberfields'][$field->flid]['data'];
                                        if($oldData['numberfields'][$field->flid]['data'] != '')
                                            echo ' '.App\Http\Controllers\FieldController::getFieldOption($field,'Unit');
                                        ?>
                                    @endif
                                @endif
                            @elseif($field->type=='List')
                                @if($data['listfields'][$field->flid]['data'] != $oldData['listfields'][$field->flid]['data'])
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($oldData['listfields']))
                                        {{$oldData['listfields'][$field->flid]['data']}}
                                    @endif
                                @endif
                            @elseif($field->type=='Multi-Select List')
                                @if($data['multiselectlistfields'][$field->flid]['data'] != $oldData['multiselectlistfields'][$field->flid]['data'])
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($oldData['multiselectlistfields']))
                                        @foreach(explode('[!]', $oldData['multiselectlistfields'][$field->flid]['data']) as $opt )
                                            <div>{{$opt}}</div>
                                        @endforeach
                                    @endif
                                @endif
                            @elseif($field->type=='Generated List')
                                @if($data['generatedlistfields'][$field->flid]['data'] != $oldData['generatedlistfields'][$field->flid]['data'])
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($oldData['generatedlistfields']))
                                        @foreach(explode('[!]', $oldData['generatedlistfields'][$field->flid]['data']) as $opt)
                                            <div>{{$opt}}</div>
                                        @endforeach
                                    @endif
                                @endif
                            @elseif($field->type=='Date')
                                @if($data['datefields'][$field->flid]['data'] != $oldData['datefields'][$field->flid]['data'])
                                    <span><b>{{$field->name}}:</b></span>
                                    @if(!is_null($oldData['datefields']))
                                        @if($oldData['datefields'][$field->flid]['data']['circa'] && \App\Http\Controllers\FieldController::getFieldOption($field,'Circa')=='Yes')
                                            {{'circa '}}
                                        @endif
                                        @if($oldData['datefields'][$field->flid]['data']['month'] == 0 && $oldData['datefields'][$field->flid]['data']['day'])
                                            {{$oldData['datefields'][$field->flid]['data']['year']}}
                                        @elseif($oldData['datefields'][$field->flid]['data']['day'] == 0)
                                            {{$oldData['datefields'][$field->flid]['data']['month'].' '.$oldData['datefields'][$field->flid]['data']['year']}}
                                        @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='MMDDYYYY')
                                            {{$oldData['datefields'][$field->flid]['data']['month'].'-'.$oldData['datefields'][$field->flid]['data']['day'].'-'.$oldData['datefields'][$field->flid]['data']['year']}}
                                        @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='DDMMYYYY')
                                            {{$oldData['datefields'][$field->flid]['data']['day'].'-'.$oldData['datefields'][$field->flid]['data']['month'].'-'.$oldData['datefields'][$field->flid]['data']['year']}}
                                        @elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='YYYYMMDD')
                                            {{$oldData['datefields'][$field->flid]['data']['year'].'-'.$oldData['datefields'][$field->flid]['data']['month'].'-'.$oldData['datefields'][$field->flid]['data']['day']}}
                                        @endif
                                        @if(\App\Http\Controllers\FieldController::getFieldOption($field,'Era')=='Yes')
                                            {{' '.$oldData['datefields'][$field->flid]['data']['era']}}
                                        @endif
                                    @endif
                                @endif
                            @endif

                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
@endforeach