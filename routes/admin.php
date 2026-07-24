<?php

// routes/admin.php

use App\Http\Controllers\Admin\Ajax\LookupController;
use App\Http\Controllers\Admin\BanknoteController;
use App\Http\Controllers\Admin\BulkActionController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\CoinController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\LookupIndexController;
use App\Http\Controllers\Admin\MagazineController;
use App\Http\Controllers\Admin\MapLocationController;
use App\Http\Controllers\Admin\MediaFileController;
use App\Http\Controllers\Admin\NewspaperController;
use App\Http\Controllers\Admin\PostcardController;
use App\Http\Controllers\Admin\StampController;
use App\Http\Controllers\Admin\PdfController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('books', BookController::class);
Route::resource('items', ItemController::class);
Route::resource('map-locations', MapLocationController::class)->except('show');

Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
Route::post('blog', [BlogController::class, 'store'])->name('blog.store');
Route::get('blog/create', [BlogController::class, 'create'])->name('blog.create');
Route::get('blog/{id}/edit', [BlogController::class, 'edit'])->name('blog.edit');
Route::put('blog/{id}/edit', [BlogController::class, 'update'])->name('blog.update');
Route::delete('blog/{id}', [BlogController::class, 'destroy'])->name('blog.destroy');

// PDF download
Route::get('{type}/{id}/pdf', [PdfController::class, 'download'])
    ->whereIn('type', array_keys(PdfController::TYPES))
    ->name('pdf');

// Polymorphic media
Route::post('{type}/{id}/media', [MediaFileController::class, 'store'])
    ->whereIn('type', ['books', 'items', 'banknotes', 'coins', 'magazines', 'newspapers', 'postcards', 'stamps', 'map-locations'])
    ->name('media.store');

Route::delete('{type}/media/{file}', [MediaFileController::class, 'destroy'])
    ->whereIn('type', ['books', 'items', 'banknotes', 'coins', 'magazines', 'newspapers', 'postcards', 'stamps', 'map-locations'])
    ->name('media.destroy');

Route::patch('{type}/media/{file}/main', [MediaFileController::class, 'makeMain'])
    ->whereIn('type', ['books', 'items', 'banknotes', 'coins', 'magazines', 'newspapers', 'postcards', 'stamps', 'map-locations'])
    ->name('media.main');

Route::post('{type}/{id}/media/reorder', [MediaFileController::class, 'reorder'])
    ->whereIn('type', ['books', 'items', 'banknotes', 'coins', 'magazines', 'newspapers', 'postcards', 'stamps', 'map-locations'])
    ->name('media.reorder');

Route::post('{type}/bulk', BulkActionController::class)
    ->whereIn('type', ['items', 'banknotes', 'coins', 'magazines', 'newspapers', 'postcards', 'stamps'])
    ->name('bulk');

Route::resource('newspapers', NewspaperController::class)->except('show');
Route::resource('magazines', MagazineController::class)->except('show');
Route::resource('banknotes', BanknoteController::class)->except('show');
Route::resource('coins', CoinController::class)->except('show');
Route::resource('postcards', PostcardController::class)->except('show');
Route::resource('stamps', StampController::class)->except('show');

Route::resource('profile', UserController::class);

Route::get('lookups/{type}', [LookupIndexController::class, 'index'])
    ->whereIn('type', [
        'book-topics', 'book-covers', 'book-series', 'origins', 'locations',
        'item-categories', 'item-nationalities', 'item-organizations',
        'magazine-series', 'newspaper-series',
        'countries', 'currencies', 'nominal-values',
        'banknote-series', 'banknote-time-periods', 'banknote-designers', 'banknote-watermarks',
        'heads-of-state', 'colours', 'print-types',
        'coin-shapes', 'coin-materials', 'coin-occasions', 'coin-designers',
        'coin-strike-marks', 'coin-front-images', 'coin-front-texts',
        'coin-reverse-images', 'coin-reverse-texts', 'coin-rims', 'coin-rim-texts',
        'postcard-types', 'postcard-valuation-images',
        'stamp-types', 'stamp-designers', 'stamp-watermarks', 'stamp-gums',
        'stamp-perforations', 'stamp-printing-houses',
    ])
    ->name('lookups.index');
Route::post('lookups/{type}', [LookupIndexController::class, 'store'])
    ->whereIn('type', [
        'book-topics', 'book-covers', 'book-series', 'origins', 'locations',
        'item-categories', 'item-nationalities', 'item-organizations',
        'magazine-series', 'newspaper-series',
        'countries', 'currencies', 'nominal-values',
        'banknote-series', 'banknote-time-periods', 'banknote-designers', 'banknote-watermarks',
        'heads-of-state', 'colours', 'print-types',
        'coin-shapes', 'coin-materials', 'coin-occasions', 'coin-designers',
        'coin-strike-marks', 'coin-front-images', 'coin-front-texts',
        'coin-reverse-images', 'coin-reverse-texts', 'coin-rims', 'coin-rim-texts',
        'postcard-types', 'postcard-valuation-images',
        'stamp-types', 'stamp-designers', 'stamp-watermarks', 'stamp-gums',
        'stamp-perforations', 'stamp-printing-houses',
    ])
    ->name('lookups.store');
Route::patch('lookups/{type}/{id}', [LookupIndexController::class, 'update'])
    ->whereIn('type', [
        'book-topics', 'book-covers', 'book-series', 'origins', 'locations',
        'item-categories', 'item-nationalities', 'item-organizations',
        'magazine-series', 'newspaper-series',
        'countries', 'currencies', 'nominal-values',
        'banknote-series', 'banknote-time-periods', 'banknote-designers', 'banknote-watermarks',
        'heads-of-state', 'colours', 'print-types',
        'coin-shapes', 'coin-materials', 'coin-occasions', 'coin-designers',
        'coin-strike-marks', 'coin-front-images', 'coin-front-texts',
        'coin-reverse-images', 'coin-reverse-texts', 'coin-rims', 'coin-rim-texts',
        'postcard-types', 'postcard-valuation-images',
        'stamp-types', 'stamp-designers', 'stamp-watermarks', 'stamp-gums',
        'stamp-perforations', 'stamp-printing-houses',
    ])
    ->name('lookups.update');
Route::delete('lookups/{type}/{id}', [LookupIndexController::class, 'destroy'])
    ->whereIn('type', [
        'book-topics', 'book-covers', 'book-series', 'origins', 'locations',
        'item-categories', 'item-nationalities', 'item-organizations',
        'magazine-series', 'newspaper-series',
        'countries', 'currencies', 'nominal-values',
        'banknote-series', 'banknote-time-periods', 'banknote-designers', 'banknote-watermarks',
        'heads-of-state', 'colours', 'print-types',
        'coin-shapes', 'coin-materials', 'coin-occasions', 'coin-designers',
        'coin-strike-marks', 'coin-front-images', 'coin-front-texts',
        'coin-reverse-images', 'coin-reverse-texts', 'coin-rims', 'coin-rim-texts',
        'postcard-types', 'postcard-valuation-images',
        'stamp-types', 'stamp-designers', 'stamp-watermarks', 'stamp-gums',
        'stamp-perforations', 'stamp-printing-houses',
    ])
    ->name('lookups.destroy');

Route::get('lookups/ajax/{type}/parents', [LookupController::class, 'parents'])
    ->name('lookups.ajax.parents');

Route::post('lookups/ajax/{type}', [LookupController::class, 'store'])
    ->name('lookups.ajax.store');
