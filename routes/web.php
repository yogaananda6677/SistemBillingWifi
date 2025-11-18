<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    // Route::get('tampil-produk', [ProdukController::class, 'index'])->name('produk.index');
    // Route::get('create-produk', [ProdukController::class, 'create'])->name('produk.create');
    // Route::post('tampil-produk', [ProdukController::class, 'store'])->name('produk.store');
    // Route::get('produk/edit/{id}', [ProdukController::class, 'edit'])->name('produk.edit');
    // Route::post('produk/update/{id}', [ProdukController::class, 'update'])->name('produk.update');
    // Route::post('produk/delete/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');
    // Route::get('produk/export/excel', [ProdukController::class, 'excel'])->name('produk.excel');
    // Route::get('produk/export/pdf', [ProdukController::class, 'pdf'])->name('produk.pdf');
    // Route::get('produk/chart', [ProdukController::class, 'chart'])->name('produk.chart');
});

