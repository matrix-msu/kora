<section class="pagination">
    <div class="previous page {{$records->onFirstPage() ? 'disabled' : ''}}">
        <a href="{{$records->previousPageUrl()}}">
            <i class="icon icon-chevron left"></i>
            <span class="name underline-middle-hover">Previous Page</span>
        </a>
    </div>
    <div class="pages">
        @if (!$records->onFirstPage())
            <a href="{{$records->url(1)}}" class="page-link">1</a>
            @if ($records->currentPage() > 2)
                @if ($records->currentPage() > 4)
                    <span class="page-link">...</span>
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
                    <span class="page-link">...</span>
                @endif
            @endif
            <a href="{{$records->url($records->lastPage())}}" class="page-link">{{$records->lastPage()}}</a>
        @endif
    </div>
    <div class="next page {{$records->hasMorePages() ? '' : 'disabled'}}">
        <a href="{{$records->nextPageUrl()}}">
            <i class="icon icon-chevron right"></i>
            <span class="name underline-middle-hover">Next Page</span>
        </a>
    </div>
</section>