<section class="pagination center">
    <div class="previous page {{$form->onFirstPage() ? 'disabled' : ''}}">
        <a href="{{$form->previousPageUrl()}}">
            <i class="icon icon-chevron left"></i>
            <span class="name underline-middle-hover">Previous Page</span>
        </a>
    </div>
    <div class="pages">
        @if (!$form->onFirstPage())
            <a href="{{$form->url(1)}}" class="page-link">1</a>
            @if ($form->currentPage() > 2)
                @if ($form->currentPage() > 4)
                    <span class="page-link">...</span>
                @endif
                @if ($form->currentPage() > 3)
                    <a href="{{$form->url($form->currentPage()-2)}}" class="page-link">{{$form->currentPage() - 2}}</a>
                @endif
                <a href="{{$form->previousPageUrl()}}" class="page-link">{{$form->currentPage() - 1}}</a>
            @endif
        @endif
        <a href="" class="page-link active">{{$form->currentPage()}}</a>
        @if ($form->lastPage() !== $form->currentPage())
            @if ($form->lastPage() - 1 > $form->currentPage())
                <a href="{{$form->url($form->currentPage()+1)}}" class="page-link">{{$form->currentPage() + 1}}</a>
                @if ($form->lastPage() - 2 > $form->currentPage())
                    <a href="{{$form->url($form->currentPage()+2)}}" class="page-link">{{$form->currentPage() + 2}}</a>
                @endif
                @if ($form->lastPage() - 3 > $form->currentPage())
                    <span class="page-link">...</span>
                @endif
            @endif
            <a href="{{$form->url($form->lastPage())}}" class="page-link">{{$form->lastPage()}}</a>
        @endif
    </div>
    <div class="next page {{$form->hasMorePages() ? '' : 'disabled'}}">
        <a href="{{$form->nextPageUrl()}}">
            <i class="icon icon-chevron right"></i>
            <span class="name underline-middle-hover">Next Page</span>
        </a>
    </div>
</section>