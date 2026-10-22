<?php

namespace App\Providers;

use App\Application\Rental\Contracts\Clock;
use App\Application\Rental\Contracts\RentalRepository;
use App\Application\Rental\Contracts\TransactionRunner;
use App\Application\Rental\Contracts\UnitRepository;
use App\Domain\Rental\HoldQuota;
use App\Infrastructure\Rental\DatabaseTransactionRunner;
use App\Infrastructure\Rental\EloquentRentalRepository;
use App\Infrastructure\Rental\EloquentUnitRepository;
use App\Infrastructure\Rental\SystemClock;
use App\Models\User;
use App\Support\Cart;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Kontrak inti Application dan implementasi Infrastructure-nya.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        RentalRepository::class => EloquentRentalRepository::class,
        UnitRepository::class => EloquentUnitRepository::class,
        TransactionRunner::class => DatabaseTransactionRunner::class,
        Clock::class => SystemClock::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());
        Gate::define('member', fn (User $user): bool => $user->isMember());

        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(10)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        View::composer(['layouts.app', 'catalog.*'], function (ViewInstance $view): void {
            $user = auth()->user();
            $isMember = $user?->isMember() ?? false;

            $view->with([
                'navHeld' => $isMember ? $user->heldUnitCount() : 0,
                'navMaxUnits' => HoldQuota::MAX_UNITS,
                'navCartCount' => $isMember ? app(Cart::class)->count() : 0,
            ]);
        });

        Blade::directive('rupiah', fn (string $expression): string => "<?php echo e(\\App\\Support\\Format::rupiah({$expression})); ?>");
        Blade::directive('wib', fn (string $expression): string => "<?php echo e(\\App\\Support\\Format::wib({$expression})); ?>");
    }
}
