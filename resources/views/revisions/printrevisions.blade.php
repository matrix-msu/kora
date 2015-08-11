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
                        <b> Record: </b> {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                    </div>

                    <?php
                        if(!is_null($data['textfields']))
                            $new = array_values($data['textfields']);
                        else
                            $new = null;
                        if(!is_null($oldData['textfields']))
                            $old = array_values($oldData['textfields']);
                        else
                            $old = null;
                    ?>
                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b> {{$new[$i]['data']}}</span>
                                <br/>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['richtextfields']))
                        $new = array_values($data['richtextfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['richtextfields']))
                        $old = array_values($oldData['richtextfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b><?php echo $new[$i]['data'] ?></span>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['numberfields']))
                        $new = array_values($data['numberfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['numberfields']))
                        $old = array_values($oldData['numberfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b> {{$new[$i]['data']['number']}} {{$new[$i]['data']['unit']}}</span>
                                <br/>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['listfields']))
                        $new = array_values($data['listfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['listfields']))
                        $old = array_values($oldData['listfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b> {{$new[$i]['data']}}</span>
                                <br>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['multiselectlistfields']))
                        $new = array_values($data['multiselectlistfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['multiselectlistfields']))
                        $old = array_values($oldData['multiselectlistfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $opt)
                                    <div>{{$opt}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['generatedlistfields']))
                        $new = array_values($data['generatedlistfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['generatedlistfields']))
                        $old = array_values($oldData['generatedlistfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $opt)
                                    <div>{{$opt}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['datefields']))
                        $new = array_values($data['datefields']);
                    else
                        $new = null;
                    if(!is_null($oldData['datefields']))
                        $old = array_values($oldData['datefields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @if($new[$i]['data']['circa'] != '')
                                    {{'circa '}}
                                @endif
                                @if($new[$i]['data']['month'] == 0 && $new[$i]['data']['day'] == 0)
                                    {{$new[$i]['data']['year']}}
                                @elseif ($new[$i]['data']['day'] == 0)
                                    {{$new[$i]['data']['month']. ' '. $new[$i]['data']['year']}}
                                @elseif ($new[$i]['data']['format'] == 'MMDDYYYY')
                                    {{$new[$i]['data']['month'].' '.$new[$i]['data']['day'].' '.$new[$i]['data']['year']}}
                                @elseif ($new[$i]['data']['format'] == 'DDMMYYYY')
                                    {{$new[$i]['data']['day'].' '.$new[$i]['data']['month'].' '.$new[$i]['data']['year']}}
                                @elseif ($new[$i]['data']['format'] == 'YYYYMMDD')
                                    {{$new[$i]['data']['year'].' '.$new[$i]['data']['month'].' '.$new[$i]['data']['day']}}
                                @endif
                                @if($new[$i]['data']['era'] != '')
                                    {{$new[$i]['data']['era']}}
                                @endif
                                <br/>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['schedulefields']))
                        $new = array_values($data['schedulefields']);
                    else
                        $new = null;
                    if(!is_null($oldData['schedulefields']))
                        $old = array_values($oldData['schedulefields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $event)
                                    <div>{{$event}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                </div>
                @if($revision->type != 'delete' && $revision->type != 'create')
                <div><b>After</b></div>
                <div class="panel panel-default">
                    <div>
                        <b>Record: </b> {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                    </div>

                    <?php
                    if(!is_null($data['textfields']))
                        $new = array_values($data['textfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['textfields']))
                        $old = array_values($oldData['textfields']);
                    else
                        $old = null;
                    ?>
                    @if(!is_null($old))
                        @for($i = 0; $i < count($old); $i++)
                            @if(isset($new[$i]))
                                @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                    <span><b>{{$old[$i]['name']}}:</b> {{$old[$i]['data']}}</span>
                                    <br/>
                                @endif
                            @elseif (!isset($new[$i]) && isset($old[$i]))
                                <span><b>{{$old[$i]['name']}}:</b> {{$old[$i]['data']}}</span>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['richtextfields']))
                        $new = array_values($data['richtextfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['richtextfields']))
                        $old = array_values($oldData['richtextfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($old); $i++)
                            @if(isset($new[$i]))
                                @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                    <span><b>{{$old[$i]['name']}}:</b><?php echo $old[$i]['data'] ?></span>
                                @endif
                            @elseif (!isset($new[$i]) && isset($old[$i]))
                                <span><b>{{$old[$i]['name']}}:</b><?php echo $old[$i]['data'] ?></span>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['numberfields']))
                        $new = array_values($data['numberfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['numberfields']))
                        $old = array_values($oldData['numberfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($old); $i++)
                            @if(isset($new[$i]))
                                @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                    <span><b>{{$old[$i]['name']}}:</b> {{$old[$i]['data']['number']}} {{$old[$i]['data']['unit']}}</span>
                                    <br/>
                                @endif
                            @elseif (!isset($new[$i]) && isset($old[$i]))
                                <span><b>{{$old[$i]['name']}}:</b> {{$old[$i]['data']['number']}} {{$old[$i]['data']['unit']}}</span>
                                <br/>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['listfields']))
                        $new = array_values($data['listfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['listfields']))
                        $old = array_values($oldData['listfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($old); $i++)
                            @if(isset($new[$i]))
                                @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                    <span><b>{{$old[$i]['name']}}:</b> {{$old[$i]['data']}}</span>
                                    <br/>
                                @endif
                            @elseif (!isset($new[$i]) && isset($old[$i]))
                                <span><b>{{$old[$i]['name']}}:</b> {{$old[$i]['data']}}</span>
                                <br/>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['multiselectlistfields']))
                        $new = array_values($data['multiselectlistfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['multiselectlistfields']))
                        $old = array_values($oldData['multiselectlistfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($old); $i++)
                            @if(isset($new[$i]))
                                @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                    <span><b>{{$old[$i]['name']}}:</b></span>
                                    @foreach(explode('[!]', $old[$i]['data']) as $opt)
                                        <div>{{$opt}}</div>
                                    @endforeach
                                @endif
                            @elseif (!isset($new[$i]) && isset($old[$i]))
                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $old[$i]['data']) as $opt)
                                    <div>{{$opt}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['generatedlistfields']))
                        $new = array_values($data['generatedlistfields']);
                    else
                        $new = null;
                    if(!is_null($oldData['generatedlistfields']))
                        $old = array_values($oldData['generatedlistfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($old); $i++)
                            @if(isset($new[$i]))
                                @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                    <span><b>{{$old[$i]['name']}}:</b></span>
                                    @foreach(explode('[!]', $old[$i]['data']) as $opt)
                                        <div>{{$opt}}</div>
                                    @endforeach
                                @endif
                            @elseif (!isset($new[$i]) && isset($old[$i]))
                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $old[$i]['data']) as $opt)
                                    <div>{{$opt}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['datefields']))
                        $new = array_values($data['datefields']);
                    else
                        $new = null;
                    if(!is_null($oldData['datefields']))
                        $old = array_values($oldData['datefields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($old); $i++)
                            @if(isset($new[$i]))
                                @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                    <span><b>{{$old[$i]['name']}}:</b></span>
                                    @if($old[$i]['data']['circa'] != '')
                                        {{'circa '}}
                                    @endif
                                    @if($old[$i]['data']['month'] == 0 && $old[$i]['data']['day'] == 0)
                                        {{$old[$i]['data']['year']}}
                                    @elseif ($old[$i]['data']['day'] == 0)
                                        {{$old[$i]['data']['month']. ' '. $old[$i]['data']['year']}}
                                    @elseif ($old[$i]['data']['format'] == 'MMDDYYYY')
                                        {{$old[$i]['data']['month'].' '.$old[$i]['data']['day'].' '.$old[$i]['data']['year']}}
                                    @elseif ($old[$i]['data']['format'] == 'DDMMYYYY')
                                        {{$old[$i]['data']['day'].' '.$old[$i]['data']['month'].' '.$old[$i]['data']['year']}}
                                    @elseif ($old[$i]['data']['format'] == 'YYYYMMDD')
                                        {{$old[$i]['data']['year'].' '.$old[$i]['data']['month'].' '.$old[$i]['data']['day']}}
                                    @endif
                                    @if($old[$i]['data']['era'] != '')
                                        {{$old[$i]['data']['era']}}
                                    @endif
                                    <br/>
                                @endif
                            @elseif (!isset($new[$i]) && isset($old[$i]))
                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @if($old[$i]['data']['circa'] != '')
                                    {{'circa '}}
                                @endif
                                @if($old[$i]['data']['month'] == 0 && $old[$i]['data']['day'] == 0)
                                    {{$old[$i]['data']['year']}}
                                @elseif ($old[$i]['data']['day'] == 0)
                                    {{$old[$i]['data']['month']. ' '. $old[$i]['data']['year']}}
                                @elseif ($old[$i]['data']['format'] == 'MMDDYYYY')
                                    {{$old[$i]['data']['month'].' '.$old[$i]['data']['day'].' '.$old[$i]['data']['year']}}
                                @elseif ($old[$i]['data']['format'] == 'DDMMYYYY')
                                    {{$old[$i]['data']['day'].' '.$old[$i]['data']['month'].' '.$old[$i]['data']['year']}}
                                @elseif ($old[$i]['data']['format'] == 'YYYYMMDD')
                                    {{$old[$i]['data']['year'].' '.$old[$i]['data']['month'].' '.$old[$i]['data']['day']}}
                                @endif
                                @if($old[$i]['data']['era'] != '')
                                    {{$old[$i]['data']['era']}}
                                @endif
                                <br/>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(!is_null($data['schedulefields']))
                        $new = array_values($data['schedulefields']);
                    else
                        $new = null;
                    if(!is_null($oldData['schedulefields']))
                        $old = array_values($oldData['schedulefields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($old); $i++)
                            @if(isset($new[$i]))
                                @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                    <span><b>{{$old[$i]['name']}}:</b></span>
                                    @foreach(explode('[!]', $old[$i]['data']) as $event)
                                        <div>{{$event}}</div>
                                    @endforeach
                                @endif
                            @elseif (!isset($new[$i]) && isset($old[$i]))
                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $old[$i]['data']) as $event)
                                    <div>{{$event}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                </div>
                @endif
            </div>
        </div>
@endforeach