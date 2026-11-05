<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::redirect('/login', '/admin/login')->name('login');

Route::view('/terms', 'legal.terms')->name('legal.terms');

Route::view('/privacy', 'legal.privacy')->name('legal.privacy');
