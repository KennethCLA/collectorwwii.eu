<?php

// routes/web.php

use App\Http\Controllers\Public\BanknoteController as PublicBanknoteController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\BookController as PublicBookController;
use App\Http\Controllers\Public\CoinController as PublicCoinController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\ForSaleController;
use App\Http\Controllers\Public\ItemController as PublicItemController;
use App\Http\Controllers\Public\MagazineController as PublicMagazineController;
use App\Http\Controllers\Public\MapController;
use App\Http\Controllers\Public\NewspaperController as PublicNewspaperController;
use App\Http\Controllers\Public\PostcardController as PublicPostcardController;
use App\Http\Controllers\Public\SectionController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\StampController as PublicStampController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogController::class, 'index'])->name('home');

Route::get('/change-language/{language}', function (string $language) {
    session(['language' => $language]);

    return back();
})->name('changeLanguage');

Route::get('/blog', [BlogController::class, 'showAllPosts'])->name('blog');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/for-sale', [ForSaleController::class, 'index'])->name('for-sale.index');
Route::get('/map', [MapController::class, 'index'])->name('map.index');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');

// PUBLIC: Books
Route::get('/books', [PublicBookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [PublicBookController::class, 'show'])->name('books.show');

// PUBLIC: Items
Route::get('/items', [PublicItemController::class, 'index'])->name('items.index');
Route::get('/items/{item}', [PublicItemController::class, 'show'])->name('items.show');

// PUBLIC: Magazines
Route::get('/magazines', [PublicMagazineController::class, 'index'])->name('magazines.index');
Route::get('/magazines/{magazine}', [PublicMagazineController::class, 'show'])->name('magazines.show');

// PUBLIC: Newspapers
Route::get('/newspapers', [PublicNewspaperController::class, 'index'])->name('newspapers.index');
Route::get('/newspapers/{newspaper}', [PublicNewspaperController::class, 'show'])->name('newspapers.show');

// PUBLIC: Banknotes
Route::get('/banknotes', [PublicBanknoteController::class, 'index'])->name('banknotes.index');
Route::get('/banknotes/{banknote}', [PublicBanknoteController::class, 'show'])->name('banknotes.show');

// PUBLIC: Coins
Route::get('/coins', [PublicCoinController::class, 'index'])->name('coins.index');
Route::get('/coins/{coin}', [PublicCoinController::class, 'show'])->name('coins.show');

// PUBLIC: Postcards
Route::get('/postcards', [PublicPostcardController::class, 'index'])->name('postcards.index');
Route::get('/postcards/{postcard}', [PublicPostcardController::class, 'show'])->name('postcards.show');

// PUBLIC: Stamps
Route::get('/stamps', [PublicStampController::class, 'index'])->name('stamps.index');
Route::get('/stamps/{stamp}', [PublicStampController::class, 'show'])->name('stamps.show');

Auth::routes(['register' => false]);

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }

    return view('auth.login');
})->middleware('guest')->name('login');

Route::get('/{section}', [SectionController::class, 'index'])
    ->whereIn('section', array_keys(config('collector.enabled_sections')))
    ->name('sections.index');
