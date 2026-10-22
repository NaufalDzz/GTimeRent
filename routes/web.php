<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RentalController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/katalog/{unit:code}', [CatalogController::class, 'show'])->name('catalog.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:login')->name('register');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('can:member')->group(function () {
        Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
        Route::delete('/keranjang/{unit:code}', [CartController::class, 'destroy'])->name('cart.destroy');
        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/checkout/gagal', [CheckoutController::class, 'failed'])->name('checkout.failed');
        Route::get('/pesanan', [RentalController::class, 'index'])->name('rentals.index');
        Route::get('/pesanan/{rental:number}', [RentalController::class, 'show'])->middleware('can:view,rental')->name('rentals.show');
    });

    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [Admin\RentalController::class, 'index'])->name('rentals.index');
        Route::get('/cetak', [Admin\PrintController::class, '__invoke'])->name('rentals.print');
        Route::get('/pesanan/{rental:number}', [Admin\RentalController::class, 'show'])->name('rentals.show');
        Route::post('/pesanan/{rental:number}/konfirmasi', [Admin\RentalStatusController::class, 'confirm'])->name('rentals.confirm');
        Route::post('/pesanan/{rental:number}/batal', [Admin\RentalStatusController::class, 'cancel'])->name('rentals.cancel');
        Route::post('/pesanan/{rental:number}/kirim', [Admin\RentalStatusController::class, 'dispatch'])->name('rentals.dispatch');
        Route::post('/pesanan/{rental:number}/terima', [Admin\RentalStatusController::class, 'receive'])->name('rentals.receive');
        Route::post('/pesanan/{rental:number}/kembali', [Admin\RentalStatusController::class, 'return'])->name('rentals.return');

        Route::get('/unit', [Admin\UnitController::class, 'index'])->name('units.index');
        Route::get('/unit/baru', [Admin\UnitController::class, 'create'])->name('units.create');
        Route::post('/unit', [Admin\UnitController::class, 'store'])->name('units.store');
        Route::get('/unit/{unit:code}/ubah', [Admin\UnitController::class, 'edit'])->withTrashed()->name('units.edit');
        Route::put('/unit/{unit:code}', [Admin\UnitController::class, 'update'])->withTrashed()->name('units.update');
        Route::delete('/unit/{unit:code}', [Admin\UnitController::class, 'destroy'])->name('units.destroy');
        Route::post('/unit/{unit:code}/pulihkan', [Admin\UnitController::class, 'restore'])->withTrashed()->name('units.restore');

        Route::get('/kategori', [Admin\CategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategori', [Admin\CategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategori/{category}', [Admin\CategoryController::class, 'update'])->withTrashed()->name('categories.update');
        Route::delete('/kategori/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/kategori/{category}/pulihkan', [Admin\CategoryController::class, 'restore'])->withTrashed()->name('categories.restore');

        Route::get('/anggota', [Admin\MemberController::class, 'index'])->name('members.index');
        Route::post('/anggota', [Admin\MemberController::class, 'store'])->name('members.store');
        Route::put('/anggota/{user}', [Admin\MemberController::class, 'update'])->withTrashed()->name('members.update');
        Route::delete('/anggota/{user}', [Admin\MemberController::class, 'destroy'])->name('members.destroy');
        Route::post('/anggota/{user}/pulihkan', [Admin\MemberController::class, 'restore'])->withTrashed()->name('members.restore');

        Route::get('/ongkir', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/ongkir', [Admin\SettingController::class, 'update'])->name('settings.update');
    });
});
