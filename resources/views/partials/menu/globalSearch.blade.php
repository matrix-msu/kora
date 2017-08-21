<li class="navigation-search">
  <a href="#" id="kora_nav_search_cmdk" class="kora_nav_item_title"><img src="{{ env('BASE_URL') }}assets/images/search-light.svg"></a>
  <ul class="navigation-sub-menu navigation-sub-menu-js">
    <li>
      <form id="kora_global_search" action="{{action("ProjectSearchController@globalSearch")}}">
        <input id="kora_global_search_input" autocomplete="off" value="" placeholder="Start Typing to Search ..." name="gsQuery" />
        <button id="kora_global_search_submit">
          <img src="{{ env('BASE_URL') }}assets/images/menu_gSubmit.svg" alt="submit global search" />
        </button>
      </form>
    </li>

    <li id="kora_global_search_spacer"></li>

    <li>
      <ul id="kora_global_search_recent">
        @foreach(\Auth::user()->gsCaches()->orderby("id","desc")->get() as $cache)
          {!! $cache->html !!}
        @endforeach
      </ul>
    </li>

    <li>
      <ul id="kora_global_search_result"></ul>
    </li>

    <li>
      <button id="kora_global_search_clear">Clear Recent Search History</button>
    </li>
  </ul>
</li>
