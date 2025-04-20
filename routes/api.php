<?php

use Illuminate\Support\Facades\Route;
use Luinuxscl\AiPosts\Http\Controllers\AiPostController;
use Luinuxscl\AiPosts\Http\Controllers\AiPostTransitionController;

// Rutas CRUD básicas
Route::get('/ai-posts', [AiPostController::class, 'index'])->name('ai-posts.index');
Route::post('/ai-posts', [AiPostController::class, 'store'])->name('ai-posts.store');
Route::get('/ai-posts/{aiPost}', [AiPostController::class, 'show'])->name('ai-posts.show');
Route::put('/ai-posts/{aiPost}', [AiPostController::class, 'update'])->name('ai-posts.update');
Route::delete('/ai-posts/{aiPost}', [AiPostController::class, 'destroy'])->name('ai-posts.destroy');

// Rutas para transiciones
Route::post('/ai-posts/{aiPost}/advance', [AiPostTransitionController::class, 'advance'])->name('ai-posts.advance');
Route::post('/ai-posts/{aiPost}/set-title', [AiPostTransitionController::class, 'setTitle'])->name('ai-posts.set-title');
Route::post('/ai-posts/{aiPost}/set-content', [AiPostTransitionController::class, 'setContent'])->name('ai-posts.set-content');
Route::post('/ai-posts/{aiPost}/set-summary', [AiPostTransitionController::class, 'setSummary'])->name('ai-posts.set-summary');
Route::post('/ai-posts/{aiPost}/set-image-prompt', [AiPostTransitionController::class, 'setImagePrompt'])->name('ai-posts.set-image-prompt');
Route::post('/ai-posts/{aiPost}/mark-as-exported', [AiPostTransitionController::class, 'markAsExported'])->name('ai-posts.mark-as-exported');

// Rutas adicionales
Route::get('/ai-posts/{aiPost}/transitions', [AiPostTransitionController::class, 'getAvailableTransitions'])->name('ai-posts.transitions');

// Rutas de exportación
Route::get('/ai-posts/export/ready', [\Luinuxscl\AiPosts\Http\Controllers\AiPostExportController::class, 'getReadyPosts'])->name('ai-posts.export.ready');
Route::get('/ai-posts/export/json', [\Luinuxscl\AiPosts\Http\Controllers\AiPostExportController::class, 'exportAsJson'])->name('ai-posts.export.json');
Route::post('/ai-posts/export/mark-batch', [\Luinuxscl\AiPosts\Http\Controllers\AiPostExportController::class, 'markBatchAsExported'])->name('ai-posts.export.mark-batch');
Route::get('/ai-posts/filter', [\Luinuxscl\AiPosts\Http\Controllers\AiPostExportController::class, 'filter'])->name('ai-posts.filter');
