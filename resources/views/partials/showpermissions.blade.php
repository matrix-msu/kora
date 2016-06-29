<div class="panel panel-default">
    <div class="panel-heading">
        <span>{{trans('partials_showpermissions.projects')}}</span><span class="pull-right">{{trans('partials_showpermissions.permissions')}}</span>
    </div>
    <div class="collapseTest" style="display: none">
        <div class="panel-body">
            <ul class="list-group">
                @if($admin)
                    <li class="list-group-item">
                        <span>{{trans('partials_showpermissions.all')}}</span> <span class="pull-right">{{trans('partials_showpermissions.super')}}</span>
                    </li>
                @else
                    @foreach($projects as $project)
                        <li class="list-group-item"><a href="{{action('ProjectController@show',[$project['pid']])}}">{{$project['name']}}</a>
                            <span class="pull-right">{{$project['permissions']}}</span>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <span>{{trans('partials_showpermissions.forms')}}</span><span class="pull-right">{{trans('partials_showpermissions.permissions')}}</span>
    </div>
    <div class="collapseTest" style="display: none">
        <div class="panel-body">
            <ul class="list-group">
                @if($admin)
                    <li class="list-group-item">
                        <span>{{trans('partials_showpermissions.all')}}</span> <span class="pull-right">{{trans('partials_showpermissions.super')}}</span>
                    </li>
                @else
                    @foreach($forms as $form)
                        <li class="list-group-item"><a href="{{action('FormController@show', ['pid' => $form['pid'], 'fid' => $form['fid']])}}">{{$form['name']}}</a>
                            <span class="pull-right">{{$form['permissions']}}</span>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <span>{{trans('partials_showpermissions.records')}}</span><span class="pull-right">{{trans('partials_showpermissions.last')}}</span>
    </div>
    <div class="collapseTest" style="display: none">
        <div class="panel-body">
            <ul class="list-group">
                @foreach($records as $record)
                    <li class="list-group-item">
                        <a href="{{action('RecordController@show', ['pid' => $record['pid'], 'fid' => $record['fid'], 'rid' => $record['rid']])}}">
                            {{$record['pid']}}-{{$record['fid']}}-{{$record['rid']}}
                        </a>
                        <span class="pull-right">{{$record['updated_at']}}</span>
                        {{--@include('forms.layout.logic',['form' => \App\Http\Controllers\FormController::getForm($record['fid']), 'fieldview' => 'records.layout.displayfield'])--}}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>