@extends('app', ['page_title' => 'Users', 'page_class' => 'admin-users'])

@section('header')
  <section class="head">
    <div class="inner-wrap center">
      <h1 class="title">
        <i class="icon icon-user"></i>
        <span>User Management</span>
      </h1>
      <p class="description">Brief info on user management,
        followed by instructions on how to use the user management page will go here.
        If you would to manage users on a project or form level,
        go to the according permissions page within the project or form.</p>
    </div>
  </section>
@stop

@section('body')
  <section class="filters center">
    <div class="underline-middle search search-js">
      <i class="icon icon-search"></i>
      <input type="text" placeholder="Find a User">
      <i class="icon icon-cancel icon-cancel-js"></i>
    </div>
    <div class="sort-options sort-options-js">
      <a href="#" class="text dropdown-white-toggle dropdown-white-toggle-js underline-middle-hover">
          <span>Alphabetical (A-Z)</span>
          <i class="icon icon-chevron"></i>
      </a>
      <ul class="dropdown-white dropdown-white-js mt-xl">
        <li><a href="#az">Alphabetical (A-Z)</a></li>
        <li><a href="#za">Alphabetical (Z-A)</a></li>
        <li><a href="#nto">Newest to Oldest</a></li>
        <li><a href="#otn">Oldest to Newest</a></li>
      </ul>
    </div>
  </section>

  <section class="new-object-button center">
    <form action="{{ action('AdminController@batch') }}">
      @if(\Auth::user()->admin)
        <input type="submit" value="Invite New User(s)">
      @endif
    </form>
  </section>

  <section class="user-selection user-selection-js center">
    @include('partials.admin.userManagement.users-sorted')
  </section>
@stop


@section('javascripts')
  @include('partials.admin.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    Kora.Admin.Users();
  </script>
@stop


@section('footer')
    <script>

        /**
         * Check the appropriate boxes based on the initially loaded user.
         */
        window.onload = function() {
            checker();
            initializeDeleteUser();
        };

        /**
         * Deletes a user.
         */
        function initializeDeleteUser() {
          $('.delete-user').click(function(e) {
            e.preventDefault();

            var card = $(this).parent().parent().parent();
            var id = card.attr('id').substring(5);
            var name = card.find('.username').html();

            var encode = $('<div/>').html("{{ trans('admin_users.deleteconfirm') }}").text();
            var response = confirm(encode + name + '?');

            if(response) {
              $.ajax({
                url: "{{ action('AdminController@deleteUser',['']) }}/" + id,
                type: 'DELETE',
                data: {
                  "_token": "{{ csrf_token() }}"
                },
                success: function(data) {
                  // TODO: Handle messages sent back from controller
                  location.reload();
                }
              });
            }
          });
        }

        /**
         * Check the boxes for a particular user.
         */
        function checker(){
            var selector = $('#dropdown option:selected');

            var admin = selector.attr('admin');
            var active = selector.attr('active');
            var picurl = selector.attr('picurl');

            // If they are an admin, check the admin box.
            if (admin==1)
                $('#admin').prop('checked', true);

            else
                $('#admin').prop('checked', false);

            // If they are an active user, check the active box.
            if (active==1)
                $('#active').prop('checked', true);

            else
                $('#active').prop('checked', false);

            $('#current_profile_pic').attr('src',picurl);
        }

    </script>
@stop
