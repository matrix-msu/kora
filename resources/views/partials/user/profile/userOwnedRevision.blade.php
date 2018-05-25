<div class="record card {{ $index == 0 ? 'active' : '' }}" id="{{$revision->rid}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            <a class="title underline-middle-hover" href="{{ action("RevisionController@show",["pid" => $revision['pid'], "fid" => $revision['fid'], "rid" => $revision['rid']]) }}">
                <span>{{$revision->kid}}</span>
            </a>

        </div>

        <div class="card-toggle-wrap">
            <div class="left">
                <span class="sub-title">{{$revision->type}}</span>
                <span class="sub-title">{{date_format($revision->created_at, "g:i")}}</span>
                <span class="sub-title">{{date_format($revision->created_at, "n.j.Y")}}</span>
                <span class="sub-title">{{$revision->username}}</span>
            </div>
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