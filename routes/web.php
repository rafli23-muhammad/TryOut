<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes - Bimbel Cemerlang Try Out & Webcam Proctoring
|--------------------------------------------------------------------------
*/

// Public & Landing
Route::get('/', function () {
    $data = \App\Services\TryoutDataStore::read();
    $packages = collect($data['packages'] ?? [])->where('is_active', true)->values();
    $questionsCount = collect($data['questions'] ?? [])->groupBy('package_id')->map->count();
    return view('welcome', compact('packages', 'questionsCount'));
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showStudentLogin'])->name('login');
Route::post('/login', [AuthController::class, 'studentLogin']);
Route::get('/register', [AuthController::class, 'showStudentRegister']);
Route::post('/register', [AuthController::class, 'studentRegister']);
Route::match(['get', 'post'], '/auth/google/callback', [AuthController::class, 'googleCallback'])->name('auth.google.callback');
Route::get('/auth/facebook/redirect', [AuthController::class, 'facebookRedirect'])->name('auth.facebook.redirect');
Route::match(['get', 'post'], '/auth/facebook/callback', [AuthController::class, 'facebookCallback'])->name('auth.facebook.callback');
Route::get('/admin/login', [AuthController::class, 'showAdminLogin']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::get('/backend', fn() => redirect('/admin'));

// Student Portal
Route::prefix('student')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard']);
    Route::get('/catalog', [StudentController::class, 'catalog']);
    Route::get('/profile', [StudentController::class, 'showProfile']);
    Route::post('/profile', [StudentController::class, 'updateProfile']);
    Route::post('/checkout/{packageId}', [StudentController::class, 'checkout']);
    Route::get('/camera-check/{packageId}', [StudentController::class, 'cameraCheck']);
});

// Exam Room & Real-time Proctoring
Route::get('/exam/{packageId}', [ExamController::class, 'startExam']);
Route::post('/exam/{sessionId}/submit', [ExamController::class, 'submitExam']);
Route::get('/exam/{sessionId}/result', [ExamController::class, 'showResult']);

// Proctoring & Exam APIs
Route::prefix('api/exam')->group(function () {
    Route::post('/{sessionId}/save-answer', [ExamController::class, 'saveAnswer']);
    Route::post('/{sessionId}/snapshot', [ExamController::class, 'uploadSnapshot']);
    Route::post('/{sessionId}/violation', [ExamController::class, 'recordViolation']);
});

// Administrator Portal
Route::prefix('admin')->group(function () {
    Route::get('/', function () {
        return redirect('/admin/dashboard');
    });
    Route::get('/dashboard', [AdminController::class, 'dashboard']);

    // Packages Management
    Route::get('/packages', [AdminController::class, 'packages']);
    Route::post('/packages', [AdminController::class, 'storePackage']);
    Route::post('/packages/{id}/update', [AdminController::class, 'updatePackage']);
    Route::post('/packages/{id}/delete', [AdminController::class, 'deletePackage']);

    // Questions Management & Templates
    Route::get('/packages/{packageId}/questions', [AdminController::class, 'questions']);
    Route::post('/packages/{packageId}/questions', [AdminController::class, 'storeQuestion']);
    Route::post('/packages/{packageId}/questions/{questionId}/delete', [AdminController::class, 'deleteQuestion']);
    Route::get('/questions/template/excel', [AdminController::class, 'downloadTemplateExcel']);
    Route::get('/questions/template/csv', [AdminController::class, 'downloadTemplateCsv']);
    Route::post('/packages/{packageId}/import-excel', [AdminController::class, 'importExcel']);

    // PDF & Word Question Generator
    Route::get('/packages/{packageId}/pdf', [AdminController::class, 'showPdfGenerator']);
    Route::post('/packages/{packageId}/pdf/parse', [AdminController::class, 'parsePdf']);
    Route::post('/packages/{packageId}/pdf/save', [AdminController::class, 'savePdfQuestions']);
    Route::post('/packages/{packageId}/pdf/ai-refine', [AdminController::class, 'aiRefinePdfQuestions']);
    Route::post('/packages/{packageId}/word/parse', [AdminController::class, 'parseWord']);

    // Payment Verification & Settings
    Route::get('/orders', [AdminController::class, 'orders']);
    Route::post('/orders/{orderId}/approve', [AdminController::class, 'approveOrder']);
    Route::post('/orders/{orderId}/reject', [AdminController::class, 'rejectOrder']);
    Route::get('/payment-settings', [AdminController::class, 'paymentSettings']);
    Route::post('/payment-settings', [AdminController::class, 'updatePaymentSettings']);

    // Live Webcam Monitoring
    Route::get('/proctoring', [AdminController::class, 'proctoring']);
    Route::get('/proctoring/poll', [AdminController::class, 'pollProctoring']);

    // User / Account Management
    Route::get('/users', [AdminController::class, 'users']);
    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::post('/users/{id}/update', [AdminController::class, 'updateUser']);
    Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus']);
    Route::post('/users/{id}/reset-password', [AdminController::class, 'resetUserPassword']);
    Route::post('/users/{id}/delete', [AdminController::class, 'deleteUser']);

    // Admin Profile & Security Settings (Ubah Akun Gmail & Password)
    Route::get('/profile', [AdminController::class, 'profile']);
    Route::post('/profile', [AdminController::class, 'updateProfile']);

    // Reports & Export
    Route::get('/reports', [AdminController::class, 'reports']);
    Route::get('/reports/export/excel', [AdminController::class, 'exportReportsExcel']);
    Route::get('/reports/export/csv', [AdminController::class, 'exportReportsCsv']);
});
