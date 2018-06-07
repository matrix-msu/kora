@extends('app', ['page_title' => "Backups", 'page_class' => 'backup-management'])

@section('aside-content')
  <?php $openManagement = true ?>
  @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-backup path3"></i>
                <span>Backup Management</span>
            </h1>
            @include('partials.backups.support')
            <p class="description">Brief backup management intro will go here. A backup file will be created and saved
                as a restore point on the server. You can download this file afterwards and save it somewhere safe. You
                can include a name or short description, the start date and time will be added for you. Depending on the
                size of your database, this may take a few minutes to finish.</p>
            <div class="content-sections">
              <div class="content-sections-scroll">
                <a href="#backups" class="backups-link section underline-middle underline-middle-hover toggle-by-name active">Your Backups</a>
                <a href="#filerestore" class="filerestore-link section underline-middle underline-middle-hover toggle-by-name">Restore From Local File</a>
              </div>
            </div>
        </div>
    </section>
@stop

@section('body')
    @include("partials.backups.createBackupModal")
    @include("partials.backups.deleteBackupModal")

    <section class="backups-section">
        <section class="filters center">
            <div class="underline-middle search search-js">
                <i class="icon icon-search"></i>
                <input type="text" placeholder="Find a Backup">
                <i class="icon icon-cancel icon-cancel-js"></i>
            </div>
            <div class="pagination-options pagination-options-js">
                <select class="order option-dropdown-js" id="order-dropdown">
                    <option value="nod">Newest to Oldest</option>
                    <option value="noa" {{app('request')->input('order') === 'noa' ? 'selected' : ''}}>Oldest to Newest</option>
                    <option value="nma" {{app('request')->input('order') === 'nma' ? 'selected' : ''}}>Name Ascending</option>
                    <option value="nmd" {{app('request')->input('order') === 'nmd' ? 'selected' : ''}}>Name Descending</option>
                </select>
            </div>
        </section>

        <section class="new-object-button center">
            <input type="button" value="Create New Backup File" class="create-backup-js">
        </section>

        <section class="backupcards-selection center">
            <?php $index=0; ?>
            @foreach($savedBackups as $backup)
                <div class="backup card all {{ $index == 0 ? 'active' : '' }}">
                    <div class="header {{ $index == 0 ? 'active' : '' }}">
                        <div class="left pl-m">
                            <a class="title">
                                <span class="name">{{$backup["name"]}}</span>
                                <?php
                                $carbon = new \Carbon\Carbon($backup["date"]);
                                $d = $carbon->subDay()->format('m.d.Y');
                                $t = $carbon->format('g:i A');
                                ?>
                                <span class="time">{{$d}}</span><span class="time">{{$t}}</span>
                                <span class="size">Size: {{$backup["size"]}}</span>
                            </a>
                        </div>

                        <div class="card-toggle-wrap">
                            <a href="#" class="card-toggle backup-toggle-js">
                                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
                            </a>
                        </div>
                    </div>

                    <div class="content {{ $index == 0 ? 'active' : '' }}">
                        <div class="id">
                            <span class="attribute">Data Backup: </span>
                            @if($backup["data"])
                                <span>True</span>
                            @else
                                <span>False</span>
                            @endif
                        </div>

                        <div class="id">
                            <span class="attribute">File Backup: </span>
                            @if($backup["files"])
                                <span>True</span>
                            @else
                                <span>False</span>
                            @endif
                        </div>

                        <div class="footer">
                            <a class="quick-action trash-container left danger delete-backup-open-js" href="#" backup-label="{{$backup["label"]}}">
                                <i class="icon icon-trash"></i>
                            </a>
                            <a class="quick-action underline-middle-hover" href="{{action("BackupController@download",['path'=>$backup["label"]])}}">
                                <i class="icon icon-download"></i>
                                <span>Download Backup File</span>
                            </a>
                            {!! Form::open(['url' => action('BackupController@startRestore'), 'class' => 'restore-form restore-form-js']) !!}
                                <input type="hidden" name="label" value="{{ $backup["label"] }}">
                                <input type="hidden" name="source" value="server">
                                <a class="quick-action underline-middle-hover restore-backup-js" href="#">
                                    <i class="icon icon-backup-little"></i>
                                    <span>Restore To This Backup</span>
                                </a>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <?php $index++; ?>
            @endforeach
        </section>
    </section>

    <section class="filerestore-section hidden">
        COMING SOON ...
    </section>
@stop

@section('javascripts')
    @include('partials.backups.javascripts')

    <script type="text/javascript">
        var deleteBackupUrl = '{{action('BackupController@delete')}}';
        var CSRFToken = '{{ csrf_token() }}';

        Kora.Backups.Index();
    </script>
@stop
