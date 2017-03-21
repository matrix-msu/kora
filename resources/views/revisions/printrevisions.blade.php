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
                <div>{{trans('revisions_printrevisions.type')}}: {{$revision->type}}</div>
                <div>{{trans('revisions_printrevisions.rollback')}}: @if($revision->rollback)True @else False @endif</div>
                @if($revision->rollback)
                    <a href="javascript:void(0)" onclick="rollback({{$revision->id}})">[{{trans('revisions_printrevisions.rollrecord')}}]</a>
                @endif
                @if($revision->type != 'create')
                <div><b>{{trans('revisions_printrevisions.before')}}</b></div>
                @elseif($revision->type == 'create')
                <div><b>{{trans('revisions_printrevisions.after')}}</b></div>
                @endif
                <div class="panel panel-default">
                    <div>
                        <b> {{trans('revisions_printrevisions.record')}}: </b> {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                    </div>

                    <?php
                        if(isset($data['textfields']))
                            $new = array_values($data['textfields']);
                        else
                            $new = null;
                        if(isset($oldData['textfields']))
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
                    if(isset($data['richtextfields']))
                        $new = array_values($data['richtextfields']);
                    else
                        $new = null;
                    if(isset($oldData['richtextfields']))
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
                    if(isset($data['numberfields']))
                        $new = array_values($data['numberfields']);
                    else
                        $new = null;
                    if(isset($oldData['numberfields']))
                        $old = array_values($oldData['numberfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b> {{$new[$i]['data']['number'] + 0}} {{$new[$i]['data']['unit']}}</span>
                                <br/>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['listfields']))
                        $new = array_values($data['listfields']);
                    else
                        $new = null;
                    if(isset($oldData['listfields']))
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
                    if(isset($data['multiselectlistfields']))
                        $new = array_values($data['multiselectlistfields']);
                    else
                        $new = null;
                    if(isset($oldData['multiselectlistfields']))
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
                    if(isset($data['generatedlistfields']))
                        $new = array_values($data['generatedlistfields']);
                    else
                        $new = null;
                    if(isset($oldData['generatedlistfields']))
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
                    if(isset($data['datefields']))
                        $new = array_values($data['datefields']);
                    else
                        $new = null;
                    if(isset($oldData['datefields']))
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
                    if(isset($data['schedulefields']))
                        $new = array_values($data['schedulefields']);
                    else
                        $new = null;
                    if(isset($oldData['schedulefields']))
                        $old = array_values($oldData['schedulefields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @if(!is_null($new[$i]['data']))
                                    @foreach(explode('[!]', $new[$i]['data']) as $event)
                                        <div>{{$event}}</div>
                                    @endforeach
                                @else
                                @endif
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['geolocatorfields']))
                        $new = array_values($data['geolocatorfields']);
                    else
                        $new = null;
                    if(isset($oldData['geolocatorfields']))
                        $old = array_values($oldData['geolocatorfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $location)
                                    <div>{{$location}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['assocfields']))
                        $new = array_values($data['assocfields']);
                    else
                        $new = null;
                    if(isset($oldData['assocfields']))
                        $old = array_values($oldData['assocfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $arecords)
                                    <div>{{$arecords}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['documentsfields']))
                        $new = array_values($data['documentsfields']);
                    else
                        $new = null;
                    if(isset($oldData['documentsfields']))
                        $old = array_values($oldData['documentsfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $document)
                                    @if($document != '')
                                        <div>{{explode('[Name]',$document)[1]}}</div>
                                    @else
                                        <br/>
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['galleryfields']))
                        $new = array_values($data['galleryfields']);
                    else
                        $new = null;
                    if(isset($oldData['galleryfields']))
                        $old = array_values($oldData['galleryfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $image)
                                    @if($image != '')
                                        <div>{{explode('[Name]', $image)[1]}}</div>
                                    @else
                                        <br/>
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['modelfields']))
                        $new = array_values($data['modelfields']);
                    else
                        $new = null;
                    if(isset($oldData['modelfields']))
                        $old = array_values($oldData['modelfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $model)
                                    @if($model != '')
                                        <div>{{explode('[Name]', $model)[1]}}</div>
                                    @else
                                        <br/>
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['playlistfields']))
                        $new = array_values($data['playlistfields']);
                    else
                        $new = null;
                    if(isset($oldData['playlistfields']))
                        $old = array_values($oldData['playlistfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $song)
                                    @if($song != '')
                                        <div>{{explode('[Name]', $song)[1]}}</div>
                                    @else
                                        <br/>
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['videofields']))
                        $new = array_values($data['videofields']);
                    else
                        $new = null;
                    if(isset($oldData['videofields']))
                        $old = array_values($oldData['videofields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $video)
                                    @if($video != '')
                                        <div>{{explode('[Name]', $video)[1]}}</div>
                                    @else
                                        <br/>
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['combofields'])) {
                        $new = array_values($data['combofields']);
                    }
                    else
                        $new = null;
                    if(isset($oldData['combofields'])) {
                        $old = array_values($oldData['combofields']);
                    }
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['values'] != $old[$i]['values'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>

                                <?php $valArray = $new[$i]['values'];
                                    $oneType = $new[$i]['first']['type'];
                                    $twoType = $new[$i]['second']['type'];
                                    $options = $new[$i]['options'];
                                ?>

                                <div style="overflow: auto">
                                    <span style="float:left;width:50%;margin-bottom:10px"><b>{{$new[$i]['first']['name']}}</b></span>
                                    <span style="float:left;width:50%;margin-bottom:10px"><b>{{$new[$i]['second']['name']}}</b></span>

                                    @for($j = 0; $j < sizeof($valArray); $j++)
                                        <div>
                                            @if($oneType == 'Text' || $oneType == 'List')
                                                <?php $value1 = explode('[!f1!]', $valArray[$j])[1]; ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">{{$value1}}</span>
                                            @elseif($oneType == 'Number')
                                                <?php
                                                $value1 = explode('[!f1!]', $valArray[$j])[1];
                                                $unit = explode('[!Field1!]', $options)[1];
                                                $unit = explode('[!Unit!]', $unit)[1];

                                                if($unit != null && $unit != '') {
                                                    $value1 .= ' '.$unit;
                                                }
                                                ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">{{$value1}}</span>
                                            @elseif($oneType == 'Multi-Select List' || $oneType == 'Generated List')
                                                <?php
                                                $value1 = explode('[!f1!]', $valArray[$j])[1];
                                                $val1Array = explode('[!]', $value1);
                                                ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">
                                                    @foreach($value1Array as $val)
                                                        <div>{{$val}}</div>
                                                    @endforeach
                                                </span>
                                            @endif

                                            @if($twoType == 'Text' || $twoType == 'List')
                                                <?php $value2 = explode('[!f2!]', $valArray[$j])[1]; ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">{{$value2}}</span>
                                            @elseif($twoType == 'Number')
                                                <?php
                                                $value2 = explode('[!f2!]', $valArray[$j])[1];
                                                $unit = explode('[!Field2!]', $options)[1];
                                                $unit = explode('Unit', $unit)[1];

                                                if($unit != null && $unit != '') {
                                                    $value2 .= ' '.$unit;
                                                }
                                                ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">{{$value2}}</span>
                                            @elseif($twoType == 'Multi-Select List' || $twoType == 'Generated List')
                                                <?php
                                                $value2 = explode('[!f2!]', $valArray[$j])[1];
                                                $val2Array = explode('[!]', $value2);
                                                ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">
                                                    @foreach($val2Array as $val)
                                                        <div>{{$val}}</div>
                                                    @endforeach
                                                </span>
                                            @endif
                                        </div>
                                    @endfor
                                </div>
                            @endif
                        @endfor
                    @endif

                </div>
<!--- --- --- --- --- --- ---  ---  --- --- --- Begin Old Record Print --- --- --- --- --- --- ---  ---  --- --- --->
                @if($revision->type != 'delete' && $revision->type != 'create')
                <div><b>{{trans('revisions_printrevisions.after')}}</b></div>
                <div class="panel panel-default">
                    <div>
                        <b>{{trans('revisions_printrevisions.record')}}: </b> {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                    </div>

                    <?php
                    if(isset($data['textfields']))
                        $new = array_values($data['textfields']);
                    else
                        $new = null;
                    if(isset($oldData['textfields']))
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
                    if(isset($data['richtextfields']))
                        $new = array_values($data['richtextfields']);
                    else
                        $new = null;
                    if(isset($oldData['richtextfields']))
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
                    if(isset($data['numberfields']))
                        $new = array_values($data['numberfields']);
                    else
                        $new = null;
                    if(isset($oldData['numberfields']))
                        $old = array_values($oldData['numberfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($old); $i++)
                            @if(isset($new[$i]))
                                @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                    <span><b>{{$old[$i]['name']}}:</b> {{$old[$i]['data']['number'] + 0}} {{$old[$i]['data']['unit']}}</span>
                                    <br/>
                                @endif
                            @elseif (!isset($new[$i]) && isset($old[$i]))
                                <span><b>{{$old[$i]['name']}}:</b> {{$old[$i]['data']['number'] + 0}} {{$old[$i]['data']['unit']}}</span>
                                <br/>
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['listfields']))
                        $new = array_values($data['listfields']);
                    else
                        $new = null;
                    if(isset($oldData['listfields']))
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
                    if(isset($data['multiselectlistfields']))
                        $new = array_values($data['multiselectlistfields']);
                    else
                        $new = null;
                    if(isset($oldData['multiselectlistfields']))
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
                    if(isset($data['generatedlistfields']))
                        $new = array_values($data['generatedlistfields']);
                    else
                        $new = null;
                    if(isset($oldData['generatedlistfields']))
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
                    if(isset($data['datefields']))
                        $new = array_values($data['datefields']);
                    else
                        $new = null;
                    if(isset($oldData['datefields']))
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
                    if(isset($data['schedulefields']))
                        $new = array_values($data['schedulefields']);
                    else
                        $new = null;
                    if(isset($oldData['schedulefields']))
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

                    <?php
                    if(isset($data['geolocatorfields']))
                        $new = array_values($data['geolocatorfields']);
                    else
                        $new = null;
                    if(isset($oldData['geolocatorfields']))
                        $old = array_values($oldData['geolocatorfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $old[$i]['data']) as $location)
                                    <div>{{$location}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['assocfields']))
                        $new = array_values($data['assocfields']);
                    else
                        $new = null;
                    if(isset($oldData['assocfields']))
                        $old = array_values($oldData['assocfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($new))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$new[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $new[$i]['data']) as $arecords)
                                    <div>{{$arecords}}</div>
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['documentsfields']))
                        $new = array_values($data['documentsfields']);
                    else
                        $new = null;
                    if(isset($oldData['documentsfields']))
                        $old = array_values($oldData['documentsfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')

                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $old[$i]['data']) as $document)
                                    @if($document != '')
                                        <div>{{explode('[Name]',$document)[1]}}</div>
                                    @else
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['galleryfields']))
                        $new = array_values($data['galleryfields']);
                    else
                        $new = null;
                    if(isset($oldData['galleryfields']))
                        $old = array_values($oldData['galleryfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $old[$i]['data']) as $image)
                                    @if($image != '')
                                        <div>{{explode('[Name]', $image)[1]}}</div>
                                    @else
                                        <br/>
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['modelfields']))
                        $new = array_values($data['modelfields']);
                    else
                        $new = null;
                    if(isset($oldData['modelfields']))
                        $old = array_values($oldData['modelfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $old[$i]['data']) as $model)
                                    @if($model != '')
                                        <div>{{explode('[Name]', $model)[1]}}</div>
                                    @else
                                        <br/>
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['playlistfields']))
                        $new = array_values($data['playlistfields']);
                    else
                        $new = null;
                    if(isset($oldData['playlistfields']))
                        $old = array_values($oldData['playlistfields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $old[$i]['data']) as $song)
                                    @if($song != '')
                                        <div>{{explode('[Name]', $song)[1]}}</div>
                                    @else
                                        <br/>
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['videofields']))
                        $new = array_values($data['videofields']);
                    else
                        $new = null;
                    if(isset($oldData['videofields']))
                        $old = array_values($oldData['videofields']);
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['data'] != $old[$i]['data'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$old[$i]['name']}}:</b></span>
                                @foreach(explode('[!]', $old[$i]['data']) as $video)
                                    @if($video != '')
                                        <div>{{explode('[Name]', $video)[1]}}</div>
                                    @else
                                        <br/>
                                    @endif
                                @endforeach
                            @endif
                        @endfor
                    @endif

                    <?php
                    if(isset($data['combofields']))
                        $new = array_values($data['combofields']);
                    else
                        $new = null;
                    if(isset($oldData['combofields'])) {
                        $old = array_values($oldData['combofields']);
                    }
                    else
                        $old = null;
                    ?>

                    @if(!is_null($old))
                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i]['values'] != $old[$i]['values'] || $revision->type == 'create' || $revision->type == 'delete')
                                <span><b>{{$old[$i]['name']}}:</b></span>

                                <?php $valArray = $old[$i]['values'];
                                $oneType = $old[$i]['first']['type'];
                                $twoType = $old[$i]['second']['type'];
                                $options = $old[$i]['options'];
                                ?>

                                <div style="overflow: auto">
                                    <span style="float:left;width:50%;margin-bottom:10px"><b>{{$new[$i]['first']['name']}}</b></span>
                                    <span style="float:left;width:50%;margin-bottom:10px"><b>{{$new[$i]['second']['name']}}</b></span>

                                    @for($j = 0; $j < sizeof($valArray); $j++)
                                        <div>
                                            @if($oneType == 'Text' || $oneType == 'List')
                                                <?php $value1 = explode('[!f1!]', $valArray[$j])[1]; ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">{{$value1}}</span>
                                            @elseif($oneType == 'Number')
                                                <?php
                                                $value1 = explode('[!f1!]', $valArray[$j])[1];
                                                $unit = explode('[!Field1!]', $options)[1];
                                                $unit = explode('[!Unit!]', $unit)[1];

                                                if($unit != null && $unit != '') {
                                                    $value1 .= ' '.$unit;
                                                }
                                                ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">{{$value1}}</span>
                                            @elseif($oneType == 'Multi-Select List' || $oneType == 'Generated List')
                                                <?php
                                                $value1 = explode('[!f1!]', $valArray[$j])[1];
                                                $val1Array = explode('[!]', $value1);
                                                ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">
                                                    @foreach($value1Array as $val)
                                                        <div>{{$val}}</div>
                                                    @endforeach
                                                </span>
                                            @endif

                                            @if($twoType == 'Text' || $twoType == 'List')
                                                <?php $value2 = explode('[!f2!]', $valArray[$j])[1]; ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">{{$value2}}</span>
                                            @elseif($twoType == 'Number')
                                                <?php
                                                $value2 = explode('[!f2!]', $valArray[$j])[1];
                                                $unit = explode('[!Field2!]', $options)[1];
                                                $unit = explode('Unit', $unit)[1];

                                                if($unit != null && $unit != '') {
                                                    $value2 .= ' '.$unit;
                                                }
                                                ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">{{$value2}}</span>
                                            @elseif($twoType == 'Multi-Select List' || $twoType == 'Generated List')
                                                <?php
                                                $value2 = explode('[!f2!]', $valArray[$j])[1];
                                                $val2Array = explode('[!]', $value2);
                                                ?>
                                                <span style="float:left;width:50%;margin-bottom:10px">
                                                    @foreach($val2Array as $val)
                                                        <div>{{$val}}</div>
                                                    @endforeach
                                                </span>
                                            @endif
                                        </div>
                                    @endfor
                                </div>
                            @endif
                        @endfor
                    @endif

                </div>
                @endif
            </div>
        </div>
@endforeach