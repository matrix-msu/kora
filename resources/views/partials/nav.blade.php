<div id="kora_nav_bar">
    <ul id="kora_nav_left">
        @if(Auth::guest())
            <li class="kora_nav_item">
                <a href="{{ url('/auth/register') }}" class="kora_nav_item_title">Register</a>
            </li>
        @else
            <li class="kora_nav_logo">
                <a href="{{ url('/') }}" class="kora_nav_item_title"><img src="{{ env('BASE_URL') }}logos/KoraIII-Logo.gif"></a>
            </li>
            @include("partials.menu.dashboard")
            @yield('leftNavLinks')
        @endif
    </ul>
    <ul id="kora_nav_right">
        @if(Auth::guest())
            <li class="kora_nav_item">
                <a href="#" class="kora_nav_item_title">Current Language</a>
                <ul class="kora_nav_sub_menu">
                    <li class="kora_nav_sub_menu_item">Current Language</li>
                    <li class="kora_nav_sub_menu_item">Another Language</li>
                    <li class="kora_nav_sub_menu_item">Another Language</li>
                </ul>
            </li>
        @else
            <li class="kora_nav_search">
                <a href="#" id="kora_nav_search_cmdk" class="kora_nav_item_title"><img src="{{ env('BASE_URL') }}images/menu_search.svg"></a>
                <ul class="kora_nav_sub_menu">
                    <li>
                        <form id="kora_global_search" action="{{action("ProjectSearchController@globalSearch")}}">
                            <input id="kora_global_search_input" autocomplete="off" value="" placeholder="    Start Typing to Search ..." name="gsQuery" />
                            <button id="kora_global_search_submit">
                                <img src="{{ env('BASE_URL') }}images/menu_gSubmit.svg" alt="submit global search" />
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
                        <ul id="kora_global_search_result">
                        </ul>
                    </li>
                    <li>
                        <button id="kora_global_search_clear">Clear Recent Search History</button>
                    </li>
                </ul>
            </li>
            <li class="kora_nav_profile">
                <a href="#" class="kora_nav_item_title"><img class="kora_nav_profile_pic" src="{{env('STORAGE_URL') . 'profiles/'.\Auth::user()->id.'/'.\Auth::user()->profile}}"></a>
                <ul class="kora_nav_sub_menu">
                    <li class="kora_nav_sub_menu_header">
                        Hello, {{ Auth::user()->username }}!
                    </li>
                    <li class="kora_nav_sub_menu_item">
                        <a href="{{ url('/user') }}">View My Profile</a>
                    </li>
                    <li class="kora_nav_sub_menu_item">
                        <a href="#">Edit My Profile</a>
                    </li>
                    <li class="kora_nav_sub_menu_item">
                        <a href="#">My Preferences</a>
                    </li>
                    <li class="kora_nav_sub_menu_item">
                        <a href="#">My User Permissions</a>
                    </li>
                    <li class="kora_nav_sub_menu_item">
                        <a href="#">My Record History</a>
                    </li>
                    <li class="kora_nav_sub_menu_spacer"></li>
                    <li class="kora_nav_sub_menu_item">
                        <a href="{{ url('/auth/logout') }}">Logout<img class="kora_nav_logout_button" src="{{ env('BASE_URL') }}images/menu_logout.svg"></a>
                    </li>
                </ul>
            </li>
            <li class="kora_nav_ham">
                <a href="#" class="kora_nav_item_title"><img src="{{ env('BASE_URL') }}images/menu_ham.svg"></a>
            </li>
        @endif
    </ul>
</div>

<script>
    //The first section handles closing/opening of menus
    var menuTitleElement = $('#kora_nav_left .kora_nav_item_title');
    var menuIndex = menuTitleElement.length-1;
    menuTitleElement.each( function(index){
        if(index>0 && index != menuIndex)
            $(this).css( "opacity", 0.7 );
    });

    var navBarElement = $("#kora_nav_bar");
    var subMenuElement = $("#kora_nav_bar .kora_nav_sub_menu");
    var deepMenuElement = $("#kora_nav_bar .kora_nav_deep_menu");
    navBarElement.on("click", ".kora_nav_item_title", function(){
        var clicked = $(this).next();
        var parentElement = $(this).parent();

        deepMenuElement.each( function(){
            $(this).attr("style","display: none;");
        });
        subMenuElement.each( function(){
            if($(this).get(0) !== clicked.get(0))
                $(this).attr("style","display: none;");
        });
        clicked.toggle();

        //SPECIAL CASE FOR SEARCH
        if(parentElement.attr("class")=="kora_nav_search") {
            gsTextInput.focus();
        }
    });
    navBarElement.on("click", ".kora_nav_sub_menu_item_title", function(){
        $(this).next().toggle();
    });
    //If the nav isn't clicked, close all menus
    $(document).click(function(event) {
        if(!$(event.target).closest('#kora_nav_bar').length) {
            deepMenuElement.each( function(){
                $(this).attr("style","display: none;");
            });
            subMenuElement.each( function(){
                $(this).attr("style","display: none;");
            });
        }
    });

    //This section handles the global search
    var gsForm = $("#kora_global_search");
    var gsTextInput = $( "#kora_global_search_input" );
    var gsRecentSearch = $( "#kora_global_search_recent" );
    var gsQuickResult = $( "#kora_global_search_result" );
    gsQuickResult.attr("style","display: none;"); //INITIALIZE HERE
    var gsClearRecent = $( "#kora_global_search_clear" );
    var cmdKLink = $( "#kora_nav_search_cmdk" );

    //Quick opens global search menu
    $(document).keydown(function(e) {
        //CMD K, ctrl K
        if((e.metaKey || e.ctrlKey) && e.keyCode == 75) {
            e.preventDefault();
            // do stuff
            cmdKLink.click();
        }
    });

    //Performs quick search on typing
    gsTextInput.keyup(function() {
        var searchText = $(this).val();

        //We don't want to search the entire alphabet, need at least 2 characters
        if(searchText!='' && searchText.length>=2) {
            gsRecentSearch.attr("style","display: none;");
            gsQuickResult.attr("style","");
            gsClearRecent.attr("style","display: none;");

            //Perform quick search
            $.ajax({
                url: '{{ action('ProjectSearchController@globalQuickSearch') }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "searchText": searchText
                },
                success: function (result) {
                    var resultObj = JSON.parse(result);
                    var resultStr = resultObj.join("");
                    gsQuickResult.html(resultStr);
                }
            });
        } else {
            gsRecentSearch.attr("style","");
            gsQuickResult.attr("style","display: none;");
            gsClearRecent.attr("style","");
        }
    });

    //Caches a global search before submitting the search itself
    gsForm.submit(function(){
        var valToCache = gsTextInput.val();

        if(valToCache != "") {
            var html = "<li><a href=\"{{action("ProjectSearchController@globalSearch")}}?gsQuery="+encodeURI(valToCache)+"\">Search: "+valToCache+"</a></li>";
            cacheGlobalSearch(html);
        }
    });

    //Caches the use of a quick jump link
    gsQuickResult.on("click", "a", function(){
        var uri = $(this).attr("href");
        var type = $(this).attr("type");
        var html = "<li><a href=\""+uri+"\">"+type+": "+$(this).text()+"</a></li>";
        cacheGlobalSearch(html);
    });

    //Clears the user's recent cached results
    gsClearRecent.on("click", function(){
        $.ajax({
            url: '{{ action('ProjectSearchController@clearGlobalCache') }}',
            type: 'DELETE',
            data: {
                "_token": "{{ csrf_token() }}"
            },
            success: function (result) {
                //remove from page
                gsRecentSearch.text("");
            }
        });
    });

    //The function that actually does the caching
    function cacheGlobalSearch(htmlString) {
        $.ajax({
            url: '{{ action('ProjectSearchController@cacheGlobalSearch') }}',
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                "html": htmlString
            },
            success: function (result) {
                var resultObj = JSON.parse(result);
                var resultStr = resultObj.join("");
                gsQuickResult.html(resultStr);
            }
        });
    }
</script>

