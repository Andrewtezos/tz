<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use App\Helpers\RequestApi;
use App\Helpers\Pec;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // dd($argv);
        $schedule->call(function () {
            $bakers = DB::table('nodes')->where('sleep', 0)->skip(0)->take(40)->get();
            foreach ($bakers as $key => $baker) {
                    $update = new Pec;
                    $update->update($baker->tezosid);
            }
        })->cron('*/20 * * * *');

        $schedule->call(function () {
            $bakers = DB::table('nodes')->where('sleep', 0)->skip(40)->take(40)->get();
            foreach ($bakers as $key => $baker) {
                    $update = new Pec;
                    $update->update($baker->tezosid);
            }
        })->cron('*/20 * * * *');

        $schedule->call(function () {
            $bakers = DB::table('nodes')->where('sleep', 0)->skip(80)->take(30)->get();
            foreach ($bakers as $key => $baker) {
                    $update = new Pec;
                    $update->update($baker->tezosid);
            }
        })->cron('*/20 * * * *');

        $schedule->call(function () {
            $valute = new RequestApi;
            $valute->loadValute();
        })->everyMinute();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
