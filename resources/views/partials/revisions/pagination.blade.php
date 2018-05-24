<section class="pagination center">
    <div class="previous page {{$revisions->onFirstPage() ? 'disabled' : ''}}">
        <a href="{{$revisions->previousPageUrl()}}">
            <i class="icon icon-chevron left"></i>
            <span class="name underline-middle-hover">Previous Page</span>
        </a>
    </div>
    <div class="pages">
        @if (!$revisions->onFirstPage())
            <a href="{{$revisions->url(1)}}" class="page-link">1</a>
            @if ($revisions->currentPage() > 2)
                @if ($revisions->currentPage() > 4)
                    <span class="page-link">...</span>
                @endif
                @if ($revisions->currentPage() > 3)
                    <a href="{{$revisions->url($revisions->currentPage()-2)}}" class="page-link">{{$revisions->currentPage() - 2}}</a>
                @endif
                <a href="{{$revisions->previousPageUrl()}}" class="page-link">{{$revisions->currentPage() - 1}}</a>
            @endif
        @endif
        <a href="" class="page-link active">{{$revisions->currentPage()}}</a>
        @if ($revisions->lastPage() !== $revisions->currentPage())
            @if ($revisions->lastPage() - 1 > $revisions->currentPage())
                <a href="{{$revisions->url($revisions->currentPage()+1)}}" class="page-link">{{$revisions->currentPage() + 1}}</a>
                @if ($revisions->lastPage() - 2 > $revisions->currentPage())
                    <a href="{{$revisions->url($revisions->currentPage()+2)}}" class="page-link">{{$revisions->currentPage() + 2}}</a>
                @endif
                @if ($revisions->lastPage() - 3 > $revisions->currentPage())
                    <span class="page-link">...</span>
                @endif
            @endif
            <a href="{{$revisions->url($revisions->lastPage())}}" class="page-link">{{$revisions->lastPage()}}</a>
        @endif
    </div>
    <div class="next page {{$revisions->hasMorePages() ? '' : 'disabled'}}">
        <a href="{{$revisions->nextPageUrl()}}">
            <i class="icon icon-chevron right"></i>
            <span class="name underline-middle-hover">Next Page</span>
        </a>
    </div>
</section>