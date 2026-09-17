<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\DevCommands;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\ErrorPages;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        DevCommands::except('server');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureHead();

        JsonResource::withoutWrapping();
        Model::shouldBeStrict();
    }

    /**
     * Configure default document head metadata and social sharing tags.
     */
    protected function configureHead(): void
    {
        Head::defaults(function (HeadBuilder $head): void {
            $head
                ->title(config('app.name'), suffix: ' - '.config('app.name'))
                ->canonical()
                ->og(
                    siteName: config('app.name'),
                    type: OgType::Website,
                )
                ->twitter(
                    card: TwitterCard::SummaryWithLargeImage,
                );
        });

        Head::errors(function (ErrorPages $errors): void {
            $errors->status(404, fn (HeadBuilder $head): HeadBuilder => $head
                ->title('Page Not Found')
                ->description('找不到該頁面。')
            );
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
