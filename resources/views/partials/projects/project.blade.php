<div class="project card {{ $index == 0 ? 'active' : '' }}">
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

      <a class="title underline-middle-hover" href="{{action("ProjectController@show",["pid" => $project->pid])}}">
        <span class="name">{{$project->name}}</span>
        <i class="icon icon-arrow-right"></i>
      </a>
    </div>

    <div class="card-toggle-wrap">
      <a href="#" class="card-toggle project-toggle-js">
        <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
      </a>
    </div>
  </div>

  <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
    <div class="id">
      <span class="attribute">Unique Project ID: </span>
      <span>{{$project->slug}}</span>
    </div>

    <div class="description">
      {{$project->description}}
    </div>

    <div class="admins">
      <span class="attribute">Project Admins: </span>
      @foreach($project->adminGroup()->get() as $adminGroup)
        <span>
          {{$adminGroup->users()->lists("name")->implode(", ")}}
        </span>
      @endforeach
    </div>

    <div class="forms">
      <span class="attribute">Project Forms:</span>
      @foreach($project->forms()->get() as $form)
        <span class="form">
          <a class="form-link underline-middle-hover" href="{{action("FormController@show",["pid" => $project->pid,"fid" => $form->fid])}}">
            {{$form->name}}
          </a>
        </span>
      @endforeach
    </div>

    <div class="footer">
      <a class="quick-action underline-middle-hover" href="">
        <i class="icon icon-edit"></i>
        <span>Edit Project Info</span>
      </a>

      <a class="quick-action underline-middle-hover" href="">
        <i class="icon icon-search"></i>
        <span>Search Project Records</span>
      </a>

      <a class="quick-action underline-middle-hover" href="">
        <span>Go to Project</span>
        <i class="icon icon-arrow-right"></i>
      </a>
    </div>
  </div>
</div>
