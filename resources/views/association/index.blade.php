@extends('app', ['page_title' => "{$form->name} Association Permissions", 'page_class' => 'form-association-permissions'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->project_id])
    @include('partials.menu.form', ['pid' => $form->project_id, 'fid' => $form->id])
    @include('partials.menu.static', ['name' => 'Form Associations'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->project_id, 'fid' => $form->id, 'openDrawer' => true])
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-form-associations"></i>
                <span>Form Association Permissions</span>
            </h1>
            <p class="description">This page allows you to grant association access for other forms. Associating other forms will allow them to search within this form. Select "Create a New Form Association" below, to begin creating a new form association. The newly associated form will then appear in the list below. You may also request association permission for this form to associate with other forms.</p>
            <div class="content-sections">
              <div class="content-sections-scroll">
                <a href="#create" class="section underline-middle underline-middle-hover toggle-by-name active">Create Form Association</a>
                <a href="#request" class="section underline-middle underline-middle-hover toggle-by-name">Request Form Association</a>
              </div>
            </div>
        </div>
    </section>
@stop

@section('body')
    @include('partials.projects.notification')
    @include("partials.formAssociations.newPermissionModal")
    @include("partials.formAssociations.requestPermissionModal")
    @include("partials.formAssociations.deletePermissionModal")

    <section class="create-section">
        <section class="new-object-button center">
            @if(\Auth::user()->isProjectAdmin($project))
                <form action="#">
                    <input class="new-permission-js" type="submit" value="Create a New Form Association">
                </form>
            @endif
        </section>

      @if (count($assocs) > 0)
        <section class="permission-association-selection center permission-association-js create">
            <p class="description create-description-js {{count($assocs) === 0 ? 'hidden' : ''}}">The following forms are allowed to associate with and can search within this form:</p>
            @foreach ($assocs as $index=>$a)
                <?php $f = \App\Form::where('id', '=', $a->assoc_form)->first() ?>
                <div class="association association-js card" id="create-{{$f->id}}">
                    <div class="header">
                        <div class="left pl-m">
                            <a class="title association-toggle-by-name-js" href="#">
                                <span class="name name-js">{{ $f->name }}</span>
                            </a>
                        </div>

                        <div class="card-toggle-wrap">
                            <a href="#" class="card-toggle association-toggle-js">
                                <span class="chevron-text">{{ $f->project()->get()->first()->name }}</span>
                                <i class="icon icon-chevron"></i>
                            </a>
                        </div>
                    </div>
                    <div class="content content-js">
                        <div class="description">
                            {{ $f->description }}
                        </div>
                        <div class="footer">
                            <a class="quick-action trash-container delete-permission-association-js left tooltip" href="#" data-form="{{$a->assocForm}}" data-reverse="false" tooltip="Remove Form Association">
                                <i class="icon icon-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
      @else
        @include('partials.formAssociations.no-assocs')
      @endif
    </section>

    <section class="request-section hidden">
        <p class="description center">You may also request association permissions for this form to associate with other forms. Select "Request Form Association" to begin. Once requested, a notification will be sent to the admins of the selected form to allow association from your form.</p>
        <section class="new-object-button center">
            @if(\Auth::user()->isProjectAdmin($project))
                <form action="#">
                    <input class="request-permission-js" type="submit" value="Request Form Association">
                </form>
            @endif
        </section>
        @if(count($available_associations) > 0)
            <section class="permission-association-selection center permission-association-js request">
                <p class="description request-description-js {{count($available_associations) === 0 ? 'hidden' : ''}}">{{$form->name}} is allowed to associate with and can search within the following forms:</p>
                @foreach ($available_associations as $index=>$a)
                    <?php $f = \App\Form::where('id', '=', $a->data_form)->first() ?>
                    <div class="association association-js card" id="request-{{$f->id}}">
                        <div class="header {{ $index == 0 ? 'active' : '' }}">
                            <div class="left pl-m">
                                <a class="title association-toggle-by-name-js" href="#">
                                    <span class="name name-js">{{ str_replace($f->project()->get()->first()->name." ", "", $f->name) }}</span>
                                </a>
                            </div>

                            <div class="card-toggle-wrap">
                                <a href="#" class="card-toggle association-toggle-js">
                                    <span class="chevron-text">{{ $f->project()->get()->first()->name }}</span>
                                    <i class="icon icon-chevron"></i>
                                </a>
                            </div>
                        </div>
                        <div class="content content-js">
                            <div class="description">
                              {{ $f->description }}
                            </div>
                            <div class="footer">
                                <a class="quick-action trash-container delete-permission-association-js left tooltip" href="#" data-form="{{$a->data_form}}" data-reverse='true' tooltip="Remove Form Association">
                                    <i class="icon icon-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>
        @else
          @include('partials.formAssociations.no-reqAssocs')
        @endif
    </section>
@stop

@section('javascripts')
    @include('partials.formAssociations.javascripts')

    <script type="text/javascript">
        var CSRFToken = '{{ csrf_token() }}';
        var pid = '{{ $project->pid }}';
        var createAssociationPath = '{{ action('AssociationController@create', ["pid" => $form->project_id, "fid" => $form->id]) }}';
        var requestAssociationPath = '{{ action('AssociationController@requestAccess', ["pid" => $form->project_id, "fid" => $form->id]) }}';
        var destroyAssociationPath = '{{ action('AssociationController@destroy', ["pid" => $form->project_id, "fid" => $form->id]) }}';
        var destroyReverseAssociationPath = '{{ action('AssociationController@destroyReverse', ["pid" => $form->project_id, "fid" => $form->id]) }}';
        Kora.FormAssociations.Index();
    </script>
@stop
