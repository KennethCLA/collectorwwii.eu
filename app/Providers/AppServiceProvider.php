<?php

namespace App\Providers;

use App\Models\Banknote;
use App\Models\Book;
use App\Models\Coin;
use App\Models\Item;
use App\Models\Magazine;
use App\Models\MapLocation;
use App\Models\Newspaper;
use App\Models\Postcard;
use App\Models\Stamp;
use App\Policies\BanknotePolicy;
use App\Policies\BookPolicy;
use App\Policies\CoinPolicy;
use App\Policies\ItemPolicy;
use App\Policies\MagazinePolicy;
use App\Policies\MapLocationPolicy;
use App\Policies\NewspaperPolicy;
use App\Policies\PostcardPolicy;
use App\Policies\StampPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Keep this deliberately separate from the login limiter. Contact-form
        // traffic is anonymous, so its client IP is the only stable identifier.
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(5)
            ->by('contact:'.$request->ip()));

        Gate::policy(Book::class, BookPolicy::class);
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Banknote::class, BanknotePolicy::class);
        Gate::policy(Coin::class, CoinPolicy::class);
        Gate::policy(Magazine::class, MagazinePolicy::class);
        Gate::policy(MapLocation::class, MapLocationPolicy::class);
        Gate::policy(Newspaper::class, NewspaperPolicy::class);
        Gate::policy(Postcard::class, PostcardPolicy::class);
        Gate::policy(Stamp::class, StampPolicy::class);
    }
}
