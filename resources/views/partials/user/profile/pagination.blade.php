@php
    $lastPage = ceil($totalCount/$pageCount);
@endphp
<section class="pagination pagination-js center">
    <div class="previous page page-js {{$page == 1 ? 'disabled' : ''}}">
        <a href="#{{$page-1}}" class="{{$page == 1 ? '' : 'page-link-js'}}">
            <i class="icon icon-chevron left"></i>
            <span class="name underline-middle-hover">Previous Page</span>
        </a>
    </div>
    <div class="pages">
        @if($page != 1)
{{--CURRENT SPOT--}}
            <a href="#1" class="page-link first-page page-link-js">1</a>
            @if ($page > 2)
                @if ($page > 4)
                    <span class="page-link dots-backwards">...</span>
                @endif
                @if ($page > 3)
                    <a href="#{{$page-2}}" class="page-link page-link-js">{{$page - 2}}</a>
                @endif
                <a href="#{{$page-1}}" class="page-link page-link-js">{{$page - 1}}</a>
            @endif
        @endif
        <a href="" class="page-link active">{{$page}}</a>
        @if ($lastPage != $page)
            @if ($lastPage - 1 > $page)
                <a href="#{{$page+1}}" class="page-link page-link-js">{{$page + 1}}</a>
                @if ($lastPage - 2 > $page)
                    <a href="#{{$page+2}}" class="page-link page-link-js">{{$page + 2}}</a>
                @endif
                @if ($lastPage - 3 > $page)
                    <span class="page-link dots-forwards">...</span>
                @endif
            @endif
            <a href="#{{$lastPage}}" class="page-link last-page page-link-js">{{$lastPage}}</a>
        @endif
    </div>
    <div class="next page {{$lastPage >$page ? '' : 'disabled'}}">
        <a href="#{{$page+1}}" class="{{$lastPage >$page ? 'page-link-js' : ''}}">
            <i class="icon icon-chevron right"></i>
            <span class="name underline-middle-hover">Next Page</span>
        </a>
    </div>
</section>