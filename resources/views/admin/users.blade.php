@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">

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

        window.onload = function() {
            var selector = $('#dropdown option:selected');

            var admin = selector.attr('admin');
            var active = selector.attr('active');

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
            var selector = $('#dropdown option:selected');

            var id = selector.attr('value');
            var name = selector.text();

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
            var selector = $('#dropdown option:selected');

            var admin = selector.attr('admin');
            var active = selector.attr('active');

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