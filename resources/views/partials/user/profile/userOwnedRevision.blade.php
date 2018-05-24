<div class="record card {{ $index == 0 ? 'active' : '' }}" id="{{$revision->rid}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            <a class="title underline-middle-hover mr-m" href="{{ action("ProjectController@show",["pid" => $revision['pid']]) }}">
                <span>{{$revision->kid}}</span>
            </a>
            <span class="mr-m">{{$revision->type}}</span>
            <span class="mr-m">{{date_format($revision->created_at, "g:i")}}</span>
            <span class="mr-m">{{date_format($revision->created_at, "n.j.Y")}}</span>
            <span class="mr-m">{{$revision->username}}</span>
        </div>

        <div class="card-toggle-wrap">
            <a href="#" class="card-toggle project-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
        <div class="permissions pb-m">
            <p>{{print_r($revision)}}</p>
        </div>
    </div>
</div>