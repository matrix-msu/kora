<div class="project card {{ $index == 0 ? 'active' : '' }}" id="{{$project['pid']}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            <a class="title underline-middle-hover mr-xl" href="{{ action("ProjectController@show",["pid" => $project['pid']]) }}">
                <span class="name">{{$project['name']}}</span>
            </a>
            <a class="group" href="{{ action("ProjectGroupController@index",["pid" => $project['pid'], 'active' => $project['group']['id']]) }}">
                <span>{{$project['group']['name']}}</span>
            </a>
        </div>

        <div class="card-toggle-wrap">
            <a href="#" class="card-toggle card-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
        <div class="pb-m">
            @if ($project['permissions'] == 'Admin')
                <p>{{ (Auth::user()->id == $user->id ? 'You are' : $user->first_name . ' is') }} an Admin for this project.</p>
            @else
                <p>{{ (Auth::user()->id == $user->id ? 'You can' : $user->first_name . ' can') }} {{$project['permissions']}} within this project</p>
            @endif
        </div>
    </div>
</div>