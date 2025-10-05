<?php

use App\Console\Commands\AutoOrderingCommand;
use App\Console\Commands\BranchSyncCommand;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            $lang = getLang();

            Route::middleware('api')
                ->prefix('api' . $lang)
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api' . $lang)
                ->group(base_path('routes/role_perm.php'));

            Route::middleware('web')
                ->prefix($lang)
                ->group(base_path('routes/web.php'));

            Route::middleware('admin')
                ->prefix('test')
                ->group(base_path('routes/test.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'          => \App\Http\Middleware\AdminMiddleware::class,
            'projects_token' => \App\Http\Middleware\ProjectsTokenMiddleware::class,
        ]);

        $middleware->group('api', [
            'throttle:150,1'
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('telescope:prune')->dailyAt('00:00');
        $schedule->command(BranchSyncCommand::class)->dailyAt('00:00');
        $schedule->command(AutoOrderingCommand::class)->between('18:00', '23:00')->everyThirtyMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
