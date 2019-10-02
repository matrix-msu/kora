<section class="pagination">
    <div class="previous page {{$records->onFirstPage() ? 'disabled' : ''}}">
        <a href="{{$records->appends(\Illuminate\Http\Request::except('page'))->previousPageUrl()}}">
            <i class="icon icon-chevron left"></i>
            <span class="name">Previous</span>
        </a>
    </div>
    <div class="pages">
        @if (!$records->onFirstPage())
            <a href="{{$records->url(1)}}" class="page-link first-page">1</a>
            @if ($records->currentPage() > 2)
                @if ($records->currentPage() > 4)
                    <a href="{{$records->url($records->currentPage()-3)}}" class="page-link dots-backwards">...</a>
                @endif
                @if ($records->currentPage() > 3)
                    <a href="{{$records->url($records->currentPage()-2)}}" class="page-link">{{$records->currentPage() - 2}}</a>
                @endif
                <a href="{{$records->previousPageUrl()}}" class="page-link">{{$records->currentPage() - 1}}</a>
            @endif
        @endif
        <a href="" class="page-link active">{{$records->currentPage()}}</a>
        @if ($records->lastPage() !== $records->currentPage())
            @if ($records->lastPage() - 1 > $records->currentPage())
                <a href="{{$records->url($records->currentPage()+1)}}" class="page-link">{{$records->currentPage() + 1}}</a>
                @if ($records->lastPage() - 2 > $records->currentPage())
                    <a href="{{$records->url($records->currentPage()+2)}}" class="page-link">{{$records->currentPage() + 2}}</a>
                @endif
                @if ($records->lastPage() - 3 > $records->currentPage())
                    <a href="{{$records->url($records->currentPage()+3)}}" class="page-link dots-forwards">...</a>
                @endif
            @endif
            @if ($records->lastPage() != 0)
              <a href="{{$records->url($records->lastPage())}}" class="page-link last-page">{{$records->lastPage()}}</a>
            @endif
        @endif
    </div>
    <div class="next page {{$records->hasMorePages() ? '' : 'disabled'}}">
        <a href="{{$records->appends(\Illuminate\Http\Request::except('page'))->nextPageUrl()}}">
            <i class="icon icon-chevron right"></i>
            <span class="name">Next</span>
        </a>
    </div>
</section>