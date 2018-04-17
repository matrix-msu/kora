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
            <a href="#" class="card-toggle project-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
        <div class="permissions pb-m">
            @if ($project['permissions'] == 'Admin')
                <p>You are an admin for this project.</p>
            @else
                <p>You can {{$project['permissions']}} within this project</p>
            @endif
        </div>
    </div>
</div>