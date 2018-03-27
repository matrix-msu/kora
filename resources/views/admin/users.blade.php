@extends('app', ['page_title' => 'Users', 'page_class' => 'admin-users'])

@section('header')
  <section class="head">
    <div class="inner-wrap center">
      <h1 class="title">
        <i class="icon icon-users"></i>
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

    <div class="modal modal-js modal-mask users-cleanup-modal-js">
      <div class="content small">
        <div class="header">
          <span class="title title-js">Delete User?</span>
          <a href="#" class="modal-toggle modal-toggle-js">
            <i class="icon icon-cancel"></i>
          </a>
        </div>
        <div class="body">
          @include("partials.admin.userManagement.userDeleteForm")
        </div>
      </div>
    </div>
  </section>
@stop


@section('javascripts')
  @include('partials.admin.javascripts')

  <script type="text/javascript">
    var CSRFToken = '{{ csrf_token() }}';
    Kora.Admin.Users();

    /**
     * Deletes a user.
     * Use ajax for live update
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
     * Initialize event handling for each user for updating status or deletion
     */
    function initializeCardEvents() {
      $(".card").each(function() {
        var card = $(this);
        var form = card.find("form");
        var id = card.attr('id').substring(5);
        var name = card.find('.username').html();

        // Toggles activation for a user
        card.find('#active').click(function(e) {
          e.preventDefault();

          $.ajax({
            url: form.prop("action"),
            type: 'PATCH',
            data: {
              "_token": "{{ csrf_token() }}",
              "status": "active"
            },
            success: function(data) {
              // TODO: Handle messages sent back from controller
              if (data.status) {
                // User updated successfully
                checker(card, data.action);
              }
            }
          });
        });

        // Toggles administration status for a user
        card.find('#admin').click(function(e) {
          e.preventDefault();

          $.ajax({
            url: form.prop("action"),
            type: 'PATCH',
            data: {
              "_token": "{{ csrf_token() }}",
              "status": "admin"
            },
            success: function(data) {
              // TODO: Handle messages sent back from controller
              if (data.status) {
                // User updated successfully
                checker(card, data.action);
              }
            },
          });
        });

        // Deletes a user
        card.find('.delete-user').click(function(e) {
          e.preventDefault();

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
      });
    }

    /**
     * Check the boxes for a particular user.
     */
    function checker(card, action) {
        // If they are an admin, check the admin box.
        if (action == "admin") {
            var admin = card.find('#admin');
            admin.prop('checked', !admin.prop('checked'));
        }

        // If they are an active user, check the active box.
        if (action == "activation") {
            var check = card.find('#active')
            check.prop('checked', !check.prop('checked'));
        }
    }

    initializeCardEvents();
  </script>
@stop
