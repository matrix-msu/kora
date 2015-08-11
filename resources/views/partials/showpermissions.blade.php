@if($admin)
    <ul class="list-group">
        <li class="list-group-item">You are a super admin.</li>
    </ul>
@else
    <ul class="list-group">
        <li class="list-group-item" style="background-color:#eee"><span>Projects</span><span class="pull-right">Permissions</span></li>
        @foreach($projects as $project)
            <li class="list-group-item"><a href="{{action('ProjectController@show',[$project['pid']])}}">{{$project['name']}}</a>
                <span class="pull-right">{{$project['permissions']}}</span>
            </li>
        @endforeach
    </ul>
    <ul class="list-group">
        <li class="list-group-item" style="background-color:#eee"><span>Forms</span><span class="pull-right">Permissions</span></li>
        @foreach($forms as $form)
            <li class="list-group-item"><a href="{{action('FormController@show', ['pid' => $form['pid'], 'fid' => $form['fid']])}}">{{$form['name']}}</a>
                <span class="pull-right">{{$form['permissions']}}</span>
            </li>
        @endforeach
    </ul>
    <ul class="list-group">
        <li class="list-group-item" style="background-color:#eee"><span>Owned Records</span><span class="pull-right">Last Edited</span></li>
        @foreach($records as $record)
            <li class="list-group-item"><a href="{{action('RecordController@show', ['pid' => $record['pid'], 'fid' => $record['fid'], 'rid' => $record['rid']])}}">
                    {{$record['pid']}}-{{$record['fid']}}-{{$record['rid']}}
                </a>
                <span class="pull-right">{{$record['updated_at']}}</span>
            </li>
        @endforeach
    </ul>
@endif
