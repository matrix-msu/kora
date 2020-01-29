<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
        'App\Console\Commands\ConvertField',
        'App\Console\Commands\DisableRollbacks',
		'App\Console\Commands\ExodusScript',
		'App\Console\Commands\FileUrlFix',
        'App\Console\Commands\GenerateThumbs',
        'App\Console\Commands\Inspire',
		'App\Console\Commands\InstallKora',
		'App\Console\Commands\RebuildRecordPresets',
		'App\Console\Commands\RecordFileZipExport',
		'App\Console\Commands\ReverseAssocCache',
		'App\Console\Commands\UpdateKora',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('inspire')
				 ->hourly();
	}

}
