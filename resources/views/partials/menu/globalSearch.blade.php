<li class="navigation-search">
  <a href="#" class="global-search-toggle navigation-toggle-js">
    <i class="icon icon-search"></i>
  </a>
  <ul class="navigation-sub-menu navigation-sub-menu-js">
    <li>
      <form
        class="global-search-form global-search-form-js"
        action="{{action("ProjectSearchController@globalSearch")}}"
      >
        <input
          class="global-search-input global-search-input-js"
          autocomplete="off" value=""
          placeholder="&nbsp;&nbsp;&nbsp;&nbsp;Start Typing to Search ..."
          name="gsQuery">
        <button class="global-search-submit global-search-submit-js">
          <i class="icon icon-chevron"></i>
        </button>
      </form>
    </li>

    <li class="recent-search-results-container">
      <ul class="recent-search-results recent-search-results-js">
        @foreach(\Auth::user()->gsCaches()->orderby("id","desc")->get() as $cache)
          {!! $cache->html !!}
        @endforeach
      </ul>
    </li>

    <li class="search-results-container">
      <ul class="search-results search-results-js"></ul>
    </li>

    <li class="clear-search-results-container ">
      <button class="clear-search-results clear-search-results-js">
        <span>Clear Recent Search History</span>
      </button>
    </li>
  </ul>
</li>
