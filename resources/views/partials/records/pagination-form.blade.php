<section class="pagination center">
    <div class="previous page disabled">
        <a href="#">
            <i class="icon icon-chevron left"></i>
            <span class="name underline-middle-hover">Previous Page</span>
        </a>
    </div>
    <div class="pages">
        @foreach ($layout as $pageNumber => $page)
            <a href="#" class="page-link {{$pageNumber === 0 ? 'active' : ''}}">{{$pageNumber + 1}}</a>
        @endforeach
    </div>
    <div class="next page {{count($layout) > 1 ? '' : 'disabled'}}">
        <a href="#">
            <i class="icon icon-chevron right"></i>
            <span class="name underline-middle-hover">Next Page</span>
        </a>
    </div>
</section>