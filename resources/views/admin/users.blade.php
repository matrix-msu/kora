@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">

                        <img id="current_profile_pic" style="width:auto;height:200px" src="{{env('BASE_URL') . 'public/logos/blank_profile.jpg'}}">

                        @include('admin.form')

                        <hr/>

                        @include('admin.batch')

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>

        /**
         * Check the appropriate boxes based on the initially loaded user.
         */
        window.onload = function() {
            checker();
        };

        /**
         * Deletes a user.
         */
        function deleteUser(){
            var selector = $('#dropdown option:selected');

            var id = selector.attr('value');
            var name = selector.text();

            var encode = $('<div/>').html("{{ trans('admin_users.deleteconfirm') }}").text();
            var response = confirm(encode + name + '?');

            if(response) {
                $.ajax({
                    url: '{{action('AdminController@deleteUser',[''])}}/'+id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function() {
                        location.reload();
                    }
                });
            }
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