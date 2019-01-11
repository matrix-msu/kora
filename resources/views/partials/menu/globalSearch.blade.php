<div class="navigation-right-wrap">
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
          placeholder="&nbsp;Start Typing to Search ..."
          name="keywords">
        <input type="hidden" name="method" value="2">
        <input type="hidden" name="projects[]" value="ALL">
        <button class="global-search-submit global-search-submit-js">
          <i class="icon icon-chevron"></i>
        </button>
      </form>
    </li>
    {{--TODO::CASTLE--}}
{{--@if (\Auth::user()->gsCaches()->orderby("id","desc")->count() > 0 )--}}
    {{--<li class="recent-search-results-container">--}}
      {{--<ul class="recent-search-results recent-search-results-js">--}}
        {{--@foreach(\Auth::user()->gsCaches()->orderby("id","desc")->get() as $cache)--}}
          {{--{!! $cache->html !!}--}}
        {{--@endforeach--}}
      {{--</ul>--}}
    {{--</li>--}}
{{--@endif--}}
    <li class="search-results-container">
      <ul class="search-results search-results-js"></ul>
    </li>
    {{--TODO::CASTLE--}}
{{--@if (\Auth::user()->gsCaches()->orderby("id","desc")->count() >0 )--}}
    {{--<li class="clear-search-results-container ">--}}
      {{--<button class="clear-search-results clear-search-results-js">--}}
        {{--<span>Clear Recent Search History</span>--}}
      {{--</button>--}}
    {{--</li>--}}
{{--@endif--}}
  </ul>
</li>
