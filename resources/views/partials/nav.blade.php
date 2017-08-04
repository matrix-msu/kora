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
            <li class="kora_nav_item">
                <a href="#" class="kora_nav_item_title"><img src="{{ env('BASE_URL') }}images/menu_search.svg"></a>
                <ul class="kora_nav_sub_menu">
                    <li class="kora_nav_sub_menu_item">
                        <form id="global_search" action="{{action("ProjectSearchController@globalSearch")}}">
                            <input placeholder="Global Search" name="query" />
                            <button>Search</button>
                        </form>
                    </li>
                    <li class="kora_nav_sub_menu_spacer"></li>
                    <li class="kora_nav_sub_menu_item">Recent searches placeholder</li>
                    <li class="kora_nav_sub_menu_item">Recent searches placeholder</li>
                    <li class="kora_nav_sub_menu_item">
                        <button>Clear Recent Search History</button>
                    </li>
                </ul>
            </li>
            <li class="kora_nav_item">
                <a href="#" class="kora_nav_item_title"><img style="height: 35px;width: 35px;" src="{{env('STORAGE_URL') . 'profiles/'.\Auth::user()->id.'/'.\Auth::user()->profile}}"></a>
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
                        <a href="{{ url('/auth/logout') }}">Logout</a>
                    </li>
                </ul>
            </li>
            <li class="kora_nav_item">
                <a href="#" class="kora_nav_item_title">Dashboard</a>
            </li>
        @endif
    </ul>
</div>

<script>
    $("#kora_nav_bar").on("click", ".kora_nav_item_title", function(){
        $(this).next().toggle();
    });

    $("#kora_nav_bar").on("click", ".kora_nav_sub_menu_item_title", function(){
        $(this).next().toggle();
    });
</script>

