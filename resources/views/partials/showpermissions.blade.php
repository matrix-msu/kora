<div class="panel panel-default">
    <div class="panel-heading">
        <span>Projects</span><span class="pull-right">Permissions</span>
    </div>
    <div class="collapseTest" style="display: none">
        <div class="panel-body">
            <ul class="list-group">
                @if($admin)
                    <li class="list-group-item">
                        <span>ALL</span> <span class="pull-right">Super Admin</span>
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
        <span>Forms</span><span class="pull-right">Permissions</span>
    </div>
    <div class="collapseTest" style="display: none">
        <div class="panel-body">
            <ul class="list-group">
                @if($admin)
                    <li class="list-group-item">
                        <span>ALL</span> <span class="pull-right">Super Admin</span>
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
        <span>Owned Records</span><span class="pull-right">Last Edited</span>
    </div>
    <div class="collapseTest" style="display: none">
        <div class="panel-body">
            <ul class="list-group">
                @foreach($records as $record)
                    <li class="list-group-item"><a href="{{action('RecordController@show', ['pid' => $record['pid'], 'fid' => $record['fid'], 'rid' => $record['rid']])}}">
                            {{$record['pid']}}-{{$record['fid']}}-{{$record['rid']}}
                        </a>
                        <span class="pull-right">{{$record['updated_at']}}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>