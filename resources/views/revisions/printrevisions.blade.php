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
            <div class="collapseTest" style="display: none; padding: 0.5em">
                <div>{{trans('revisions_printrevisions.type')}}: {{$revision->type}}</div>
                <div>{{trans('revisions_printrevisions.rollback')}}: @if($revision->rollback)True @else False @endif</div>
                @if($revision->rollback)
                    <a href="javascript:void(0)" onclick="rollback({{$revision->id}})">[{{trans('revisions_printrevisions.rollrecord')}}]</a>
                @endif
                @if($revision->type != App\Revision::CREATE)
                    <div><b>{{trans('revisions_printrevisions.before')}}:</b></div>
                @else
                    <div><b>{{trans('revisions_printrevisions.after')}}:</b></div>
                @endif

                <!--- Print Post Revision Data --->

                <div class="panel panel-default" style="margin: 0.5em; padding: 0.5em">
                    <div>
                        <b> {{trans('revisions_printrevisions.record')}}: </b> {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                    </div>
                    @foreach(App\Field::$ENUM_TYPED_FIELDS as $field_type)
                        <?php
                            $new = isset($data[$field_type]) ? array_values($data[$field_type]) : null;
                            $old = isset($oldData[$field_type]) ? array_values($oldData[$field_type]) : null;
                        ?>

                        @for($i = 0; $i < count($new); $i++)
                            @if($new[$i] != $old[$i] || $revision->type == App\Revision::CREATE
                                                     || $revision->type == App\Revision::DELETE)
                                @include('revisions.displays.' . strtolower($field_type), ['data' => $new[$i]])
                            @endif
                        @endfor
                    @endforeach
                </div>

                <!--- Print Pre Revision Data --->

                @if($revision->type != App\Revision::DELETE && $revision->type != App\Revision::CREATE)
                    <div><b>{{trans('revisions_printrevisions.after')}}</b></div>
                    <div class="panel panel-default" style="margin: 0.5em; padding: 0.5em">
                        <div>
                            <b>{{trans('revisions_printrevisions.record')}}: </b> {{$form->pid}}-{{$revision->fid}}-{{$revision->rid}}
                        </div>
                        @foreach(App\Field::$ENUM_TYPED_FIELDS as $field_type)
                            <?php
                                $new = isset($data[$field_type]) ? array_values($data[$field_type]) : [];
                                $old = isset($oldData[$field_type]) ? array_values($oldData[$field_type]) : [];
                            ?>

                            @for($i = 0; $i < count($old); $i++)
                                @if($new[$i] != $old[$i] || $revision->type == App\Revision::CREATE
                                                         || $revision->type == App\Revision::DELETE)
                                    @include('revisions.displays.' . strtolower($field_type), ['data' => $old[$i]])
                                @endif
                            @endfor
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
@endforeach