@extends('app', ['page_title' => 'Dashboard', 'page_class' => 'dashboard'])

@section('aside-content')
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => true, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-dashboard"></i>
                <span>Your Dashboard</span>
            </h1>
            <div class="content-sections">
                <div class="content-sections-scroll">
                    <a href="#" class="content create-block-js">
                        <i class="icon icon-block-add"></i>
                        <span>Add a New Block</span>
                    </a>
                    <a href="#" class="content edit-blocks-js">
                        <i class="icon icon-edit"></i>
                        <span>Edit your Dashboard</span>
                    </a>
                    <a href="#" class="content done-editing-dash-js hidden">
                        <i class="icon icon-edit"></i>
                        <span>Select to Finish Editing</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@stop

@section('body')
    @include('partials.projects.notification')
    @include('partials.dashboard.addBlockModal')
    @include('partials.dashboard.deleteBlockModal')
    @include('partials.dashboard.editBlockModal')
    @include('partials.dashboard.editQuickOptionsModal')

    <div class="floating-buttons">
        <div class="form-group">
            <a class="btn dot-btn create-block-js tooltip" tooltip="Add New Block">
                <i class="icon icon-block-add"></i>
            </a>
        </div>
        <div class="form-group edit-blocks">
            <a class="btn dot-btn edit-blocks-js tooltip" tooltip="Edit Dashboard">
                <i class="icon icon-edit"></i>
            </a>
        </div>
    </div>

    <div class="sections" id="sections">
    @foreach($sections as $section)
		<section class="grid section-js {{ $section['title'] == 'No Section' ? 'add-section' : null }}" id="{{ $section['id'] }}">
			<h1 class="header {{ $section['title'] == 'No Section' ? 'add-section hidden' : null }}">
				<span class="left title">
					@if ($section['title'] != 'No Section')
						{{ $section['title'] }}
					@else
						<input class="add-section-input-js" type="text" name="sectionTitle" value="" placeholder="Type Here and Hit Enter to Add a New Section">
					@endif
				</span>
				@if ($section['title'] != 'No Section')
					<input class="edit-section-title edit-section-title-js hidden" type="text" value="{{ $section['title'] }}" placeholder="{{ $section['title'] }}" secID="{{ $section['id'] }}">
				@endif
				<div class="line-container"><span class="line"></span></div>
				@if ($section['title'] != 'No Section')
					<div class="section-quick-actions">
						<a href="#" class="move-action-js down-js tooltip" tooltip="Move Section Down">
							<i class="icon icon-chevron-down-dark-large"></i>
						</a>
						<a href="#" class="up move-action-js up-js tooltip" tooltip="Move Section Up">
							<i class="icon icon-chevron-up-dark-large"></i>
						</a>
						<a href="#" class="delete-section-js tooltip" tooltip="Delete Section" data-id="{{ $section['id'] }}">
							<i class="icon icon-cancel"></i>
						</a>
					</div>
				@endif
			</h1>
			<div class="container">
				@foreach($section['blocks'] as $block)
					@if($block["type"]=="Project")
						<div class="element" id="{{ $block['id'] }}">
							<div class="title-container">
								<i class="icon icon-project"></i>
								<a class="name underline-middle-hover"
								href="{{ action('ProjectController@show',['pid' => $block['pid']]) }}">
									<span>{{ $block['name'] }}</span>
									<i class="icon icon-arrow-right"></i>
								</a>
							</div>
							<p class="description">
								{{ $block['description'] }}
							</p>
							<div class="element-link-container">
								@foreach($block['displayedOpts'] as $link)
									<a href="{{ $link['href'] }}" class="element-link tooltip {{ $link['type'] }}"
									tooltip="{{ $link['tooltip'] }}" quickAction="{{ $link['type'] }}">
										<i class="icon {{ $link['icon-class']}}"></i>
									</a>
								@endforeach
								<a href="#" class="element-link right options-modal-js">
									<i class="icon icon-more"></i>
								</a>
								<div class="element-link-right-tooltips"><ul>
									@foreach($block['hiddenOpts'] as $opt)
										<li><a quickaction="{{ $opt['type'] }}" href="{{ $opt['href'] }}">{{ $opt['text'] }}</a></li>
									@endforeach
								</ul></div>
							</div>
							<div class="edit-block">
								<div class="wrap">
									<p>Drag & drop blocks to reorganize</p>
									<section class="new-object-button">
										<input class="edit-block-js" type="button" value="Edit Block" blkID="{{ $block['id'] }}" blockType="{{ $block['type'] }}" blockProject="{{ $block['pid'] }}" secID="{{ $section['id'] }}">
									</section>
									<div class="bottom">
										<a class="remove-block remove-block-js tooltip" tooltip="Delete Block" blkID="{{ $block['id'] }}" secID="{{ $section['id'] }}">
											<i class="icon icon-trash"></i>
										</a>
										<p class="edit-quick-options-js">Edit Quick Actions</p>
									</div>
								</div>
							</div>
						</div>
					@elseif($block["type"]=="Form")
						<div class="element" id="{{ $block['id'] }}">
							<div class="title-container">
								<i class="icon icon-form"></i>
								<a class="name underline-middle-hover"
								href="{{ action('FormController@show',['pid' => $block['pid'],'fid' => $block['fid']]) }}">
									<span>{{ $block['name'] }}</span>
									<i class="icon icon-arrow-right"></i>
								</a>
							</div>
							<p class="description fp-style">
								<span class="fp-header">Project: </span>{{ $block['projName'] }}
							</p>
							<p class="description form-desc">
								{{ $block['description'] }}
							</p>
							<div class="element-link-container">
								@foreach($block['displayedOpts'] as $link)
									<a href="{{ $link['href'] }}" class="element-link tooltip {{ $link['type'] }}"
									tooltip="{{ $link['tooltip'] }}" quickAction="{{ $link['type'] }}">
										<i class="icon {{ $link['icon-class']}}"></i>
									</a>
								@endforeach
								<a href="#" class="element-link right options-modal-js">
									<i class="icon icon-more"></i>
								</a>
								<div class="element-link-right-tooltips">
									<ul>
										@foreach($block['hiddenOpts'] as $opt)
											<li><a quickaction="{{ $opt['type'] }}" href="{{ $opt['href'] }}">{{ $opt['text'] }}</a></li>
										@endforeach
									</ul>
								</div>
							</div>
							<div class="edit-block">
								<div class="wrap">
									<p>Drag & drop blocks to reorganize</p>
									<section class="new-object-button">
										<input class="edit-block-js" type="button" value="Edit Block" blkID="{{ $block['id'] }}" blockType="{{ $block['type'] }}" blockForm="{{ $block['fid'] }}" secID="{{ $section['id'] }}">
									</section>
									<div class="bottom">
										<a class="remove-block remove-block-js tooltip" tooltip="Delete Block" blkID="{{ $block['id'] }}" secID="{{ $section['id'] }}">
											<i class="icon icon-trash"></i>
										</a>
										<p class="edit-quick-options-js">Edit Quick Actions</p>
									</div>
								</div>
							</div>
						</div>
					@elseif($block["type"]=="Record")
						<div class="element" id="{{ $block['id'] }}">
							<div class="title-container">
								<i class="icon icon-form"></i>
								<a class="name underline-middle-hover"
								href="{{ action('RecordController@show',['pid' => $block['pid'],'fid' => $block['fid'], 'rid' => $block['rid']]) }}">
									<span>{{ $block['kid'] }}</span>
									<i class="icon icon-arrow-right"></i>
								</a>
							</div>
							<p class="description fp-style">
								<span class="fp-header">Project: </span>{{ $block['projName'] }}
							</p>
							<p class="description fp-style">
								<span class="fp-header">Form: </span>{{ $block['formName'] }}
							</p>
							<p class="description fp-style">
								<span class="fp-header">{{ $block['fieldName'] }}: </span>{{ $block['fieldData'] }}
							</p>
							<div class="element-link-container">
								@foreach($block['displayedOpts'] as $link)
									<a href="{{ $link['href'] }}" class="element-link tooltip"
									tooltip="{{ $link['tooltip'] }}">
										<i class="icon {{ $link['icon-class']}}"></i>
									</a>
								@endforeach
							</div>
							<div class="edit-block">
								<div class="wrap">
									<p>Drag & drop blocks to reorganize</p>
									<section class="new-object-button">
										<input class="edit-block-js" type="button" value="Edit Block" blkID="{{ $block['id'] }}" blockType="{{ $block['type'] }}" blockRecord="{{ $block['rid'] }}" secID="{{ $section['id'] }}">
									</section>
									<div class="bottom">
										<a class="remove-block remove-block-js tooltip" tooltip="Delete Block" blkID="{{ $block['id'] }}" secID="{{ $section['id'] }}">
											<i class="icon icon-trash"></i>
										</a>
										<!-- <p class="edit-quick-options-js">Edit Quick Actions</p> -->
									</div>
								</div>
							</div>
						</div>
					@elseif($block["type"]=="Quote")
						<div class="element" id="{{ $block['id'] }}">
							<div class="title-container">
								<span class="no-link-no-icon">Today's Inspiration</span>
							</div>
							<p class="description quote-text">
								{{ $block['quote'] }}
							</p>
							<p class="description quote-author">
								{{ $block['author'] }}
							</p>
							<div class="edit-block">
								<div class="wrap">
									<p>Drag & drop blocks to reorganize</p>
									<section class="new-object-button">
										<input class="edit-block-js" type="button" value="Edit Block" blkID="{{ $block['id'] }}" blockType="{{ $block['type'] }}" secID="{{ $section['id'] }}">
									</section>
									<div class="bottom">
										<a class="remove-block remove-block-js tooltip" tooltip="Delete Block" blkID="{{ $block['id'] }}" secID="{{ $section['id'] }}">
											<i class="icon icon-trash"></i>
										</a>
										<!-- <p class="edit-quick-options-js">Edit Quick Actions</p> -->
									</div>
								</div>
							</div>
						</div>
					@elseif($block["type"]=="Note")
						<div class="element note-block" id="{{ $block['id'] }}">
							<div class="title-container">
								<input type="text" name="block_note_title" class="no-link-no-icon note-title note-title-js" placeholder="{{ $block['title'] }}" value="{{ $block['title'] }}" maxlength="40">
							</div>
							<textarea class="description note-desc note-desc-js" name="block_note_content" placeholder="{{ $block['content'] }}">{{ $block['content'] }}</textarea>
							<div class="edit-block">
								<div class="wrap">
									<p>Drag & drop blocks to reorganize</p>
									<section class="new-object-button">
										<input class="edit-block-js" type="button" value="Edit Block" blkID="{{ $block['id'] }}" blockType="{{ $block['type'] }}" secID="{{ $section['id'] }}">
									</section>
									<div class="bottom">
										<a class="remove-block remove-block-js tooltip" tooltip="Delete Block" blkID="{{ $block['id'] }}" secID="{{ $section['id'] }}">
											<i class="icon icon-trash"></i>
										</a>
										<!-- <p class="edit-quick-options-js">Edit Quick Actions</p> -->
									</div>
								</div>
							</div>
						</div>
					@elseif($block["type"]=="Twitter")
						<div class="element tweets" id="{{ $block['id'] }}">
							<p class="description tweets">
								<a class="twitter-timeline" href="https://twitter.com/kora_matrix?ref_src=twsrc%5Etfw">Tweets by kora_matrix</a>
								<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
							</p>
							<div class="edit-block">
								<div class="wrap">
									<p>Drag & drop blocks to reorganize</p>
									<section class="new-object-button">
										<input class="edit-block-js" type="button" value="Edit Block" blkID="{{ $block['id'] }}" blockType="{{ $block['type'] }}" secID="{{ $section['id'] }}">
									</section>
									<div class="bottom">
										<a class="remove-block remove-block-js tooltip" tooltip="Delete Block" blkID="{{ $block['id'] }}" secID="{{ $section['id'] }}">
											<i class="icon icon-trash"></i>
										</a>
										<!-- <p class="edit-quick-options-js">Edit Quick Actions</p> -->
									</div>
								</div>
							</div>
						</div>
					@endif
				@endforeach
			</div>
		</section>
	@endforeach
	</div>

	<div class="form-group dashboard-submit">
		<input class="hidden btn fixed-bottom fixed-bottom-slide done-editing-dash-js" type="submit" value="Finish Editing Dashboard">
	</div>

	<section class="grid section-js ui-sortable-handle hidden">
		<h1 class="header">
			<span class="left title"></span>
			<input type="text" class="edit-section-title edit-section-title-js hidden">
			<div class="line-container"><span class="line"></span></div>
			<div class="section-quick-actions show">
				<a class="move-action-js down-js tooltip" tooltip="Move Section Down">
					<i class="icon icon-chevron-down-dark-large"></i>
				</a>
				<a class="move-action-js up-js tooltip" tooltip="Move Section Up">
					<i class="icon icon-chevron-up-dark-large"></i>
				</a>
				<a class="delete-section-js tooltip" tooltip="Delete Section">
					<i class="icon icon-cancel"></i>
				</a>
			</div>
		</h1>
		<div class="container ui-sortable"></div>
	</section>
@stop

@section('javascripts')
    @include('partials.dashboard.javascripts')

    <script>
		var addSectionUrl = '{{ action('DashboardController@addSection',['sectionTitle' => '']) }}';
        var editSectionUrl = '{{ action('DashboardController@editSection') }}';
        var editNoteBlockUrl = '{{ action('DashboardController@editNoteBlock') }}';
		var editBlockOrderUrl = '{{ action('DashboardController@editBlockOrder') }}';
		var removeSectionUrl = '{{ action('DashboardController@deleteSection',['sectionID' => '']) }}';
		var removeBlockUrl = '{{ action('DashboardController@deleteBlock',['blkID' => '', 'secID' => '']) }}';
        var validationUrl = '{{ action('DashboardController@validateBlockFields') }}';
        var state = {{$state}};

        Kora.Dashboard.Index();
    </script>
@stop
