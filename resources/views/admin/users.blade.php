@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">

                        @include('admin.form')

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>
        window.onload = function() {
            var admin = $('#dropdown option:selected').attr('admin');
            var active = $('#dropdown option:selected').attr('active');

            if (admin==1)
                $('#admin').prop('checked', true);

            else
                $('#admin').prop('checked', false);

            if (active==1)
                $('#active').prop('checked', true);

            else
                $('#active').prop('checked', false);

        }

        function deleteUser(){
            var id = $('#dropdown option:selected').attr('value');
            var name = $('#dropdown option:selected').text();

            var response = confirm('Are you sure you want to delete user '+name+'?');

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

        function checker(){
            var admin = $('#dropdown option:selected').attr('admin');
            var active = $('#dropdown option:selected').attr('active');

            if (admin==1)
                $('#admin').prop('checked', true);

            else
                $('#admin').prop('checked', false);

            if (active==1)
                $('#active').prop('checked', true);

            else
                $('#active').prop('checked', false);
        }


    </script>
@stop