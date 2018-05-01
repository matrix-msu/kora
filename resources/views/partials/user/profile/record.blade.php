<div class="record card {{ $index == 0 ? 'active' : '' }}" id="{{$record->rid}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            <a class="title underline-middle-hover mr-xl" href="{{ action("ProjectController@show",["pid" => $record['pid']]) }}">
                <span class="name">{{$record->owner}}</span>
            </a>
            <a class="group" href="">
                <span>Group</span>
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
            <p>Permissions</p>
        </div>
    </div>
</div>