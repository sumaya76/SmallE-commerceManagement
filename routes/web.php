<?php

use Illuminate\Support\Facades\Route;

// Vue SPA: api ar storage chhara shob URL Vue router handle korbe
Route::get('/{any?}', fn () => view('app'))
    ->where('any', '^(?!api|storage).*$');