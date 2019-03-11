<div class="project card {{ $index == 0 ? 'active' : '' }}" id="{{$project->pid}}">
  <div class="header {{ $index == 0 ? 'active' : '' }}">
    <div class="left {{ !$isCustom ? 'pl-m' : null}}">
      @if ($isCustom)
        <div class="move-actions">
          <a class="action move-action-js up-js" href="">
            <i class="icon icon-arrow-up"></i>
          </a>

          <a class="action move-action-js down-js" href="">
            <i class="icon icon-arrow-down"></i>
          </a>
        </div>
      @endif

      <a class="title {{$archived ? 'inactive': 'underline-middle-hover'}}" href="{{ action("ProjectController@show",["pid" => $project->pid]) }}">
        <span class="name">{{$project->name}}</span>
        @if (!$archived)
          <i class="icon icon-arrow-right"></i>
        @endif
      </a>
    </div>

    <div class="card-toggle-wrap">
      <a href="#" class="card-toggle project-toggle-js">
        <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
      </a>
    </div>
  </div>

  <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
    <div class="id mb-m">
      <span class="attribute">Unique Project ID: </span>
      <span>{{$project->slug}}</span>
    </div>

    <div class="description mb-m">
      {{$project->description}}
    </div>

    <div class="admins mb-m">
      <span class="attribute">Project Admins: </span>
      @foreach($project->adminGroup()->get() as $adminGroup)
        <span>
          @foreach($adminGroup->users()->get()->all() as $index => $user)
            @if ( $index != count($adminGroup->users()->get()->all()) - 1 )
            <a href='#' class='admin-name admin-name-js'
               data-name="{{$user->getFullNameAttribute()}}"
               data-username="{{$user->username}}"
               data-email="{{$user->email}}"
               data-organization="{{$user->organization}}"
               data-profile="{{$user->getProfilePicUrl()}}"
               data-profile-url="{{action('Auth\UserController@index', ['uid' => $user->id])}}">
                {{ $user->getFullNameAttribute() }},
            </a>
            @else
            <a href='#' class='admin-name admin-name-js'
               data-name="{{$user->getFullNameAttribute()}}"
               data-username="{{$user->username}}"
               data-email="{{$user->email}}"
               data-organization="{{$user->organization}}"
               data-profile="{{$user->getProfilePicUrl()}}"
               data-profile-url="{{action('Auth\UserController@index', ['uid' => $user->id])}}">
                {{ $user->getFullNameAttribute() }}
            </a>
            @endif
          @endforeach
        </span>
      @endforeach
    </div>

    <div class="forms mb-m">
      <span class="attribute">Project Forms:</span>

      @foreach($project->forms()->get() as $form)
        <span class="form">
          <a class="form-link {{$archived ? 'inactive': 'underline-middle-hover'}}" href="{{action("FormController@show",["pid" => $project->pid,"fid" => $form->fid])}}">
            {{$form->name}}
          </a>
        </span>
      @endforeach

	  @if ($project->forms()->count() == 0)
		<span class="form">
		  <a class="form-link inactive">This project does not have any forms</a>
		</span>
	  @endif
    </div>

    @if (!$archived)
      <div class="footer">
        <a class="quick-action underline-middle-hover" href="{{ action('ProjectController@edit',['pid' => $project->pid]) }}">
          <i class="icon icon-edit-little"></i>
          <span>Edit Project Info</span>
        </a>

        <a class="quick-action underline-middle-hover" href="{{ action('ProjectSearchController@keywordSearch', ['pid'=>$project->pid]) }}">
          <i class="icon icon-search"></i>
          <span>Search Project Records</span>
        </a>

        <a class="quick-action underline-middle-hover" href="{{ action('ProjectController@show',['pid' => $project->pid]) }}">
          <span>Go to Project</span>
          <i class="icon icon-arrow-right"></i>
        </a>
      </div>
    @else
      <div class="footer">
        <a class="quick-action underline-middle-hover unarchive-js" href="#">
          <i class="icon icon-edit-little"></i>
          <span>Unarchive</span>
        </a>
      </div>
    @endif
  </div>
</div>
