@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{ trans("help_search.title") }}</h1>
                    </div>
                    <div class="panel-body">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                {{ trans("help_search.parameters") }}
                            </div>
                            <div class="collapseTest" style="display: none">
                                <br/>
                                <ul>
                                    <li>
                                        {{ trans("help_search.engine") }}
                                    </li>
                                    <li>
                                        {{ trans("help_search.characters") }}
                                    </li>
                                    <li>
                                        {{ trans("help_search.stopwords") }} <a target="_blank" href="https://dev.mysql.com/doc/refman/5.5/en/fulltext-stopwords.html">{{ trans("help_search.here") }}</a>.
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>
        /**
         * The collapsing display jQuery.
         */
        $( ".panel-heading" ).on( "click", function() {
            if ($(this).siblings('.collapseTest').css('display') == 'none' ){
                $(this).siblings('.collapseTest').slideDown();
            }else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });
    </script>
@stop