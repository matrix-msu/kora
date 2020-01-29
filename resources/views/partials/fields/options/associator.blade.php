@extends('fields.show')

@section('fieldOptions')
    @include('partials.fields.options.defaults.associator')

    @include('partials.fields.options.config.associator')
@stop

@section('fieldOptionsJS')
    assocSearchURI = "{{ action('AssociatorSearchController@assocSearch',['pid' => $form->project_id,'fid'=>$form->id, 'flid'=>$flid]) }}";
    csfrToken = "{{ csrf_token() }}";

    Kora.Fields.Options('Associator');
@stop
