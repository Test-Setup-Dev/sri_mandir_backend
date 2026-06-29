<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\FirebaseController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminMediaController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminDonationController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\BennerController;
use App\Http\Controllers\Admin\AboutUsController;
use App\Http\Controllers\Admin\PrivacyPolicyController;
use App\Http\Controllers\Admin\TermsConditionController;
use App\Http\Controllers\Admin\UserSupportController;
use App\Http\Controllers\Api\UserNotificationController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



// User authentication routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// social login routes
Route::post('/social-login', [UserController::class, 'socialLogin']);
Route::post('/forget-Password', [UserController::class, 'forgetPassword']);
Route::post('/reset-Password', [UserController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/profile-update', [UserController::class, 'updateProfile']);
    Route::get('/my-notifications', [UserNotificationController::class, 'index']);
    Route::delete('/my-notifications/{notification}', [UserNotificationController::class, 'destroy']);
    Route::post('/rate', [RatingController::class, 'rateMedia']);
    Route::get('/my-faverate', [RatingController::class, 'myRatings']);
    Route::post('/media/favorite', [MediaController::class, 'toggleFavorite']);
    Route::get('/media/my-favorites', [MediaController::class, 'myFavorite']);

    // Blog routes
    Route::post('/blogs', [BlogController::class, 'addBlog']);
    Route::get('/my-blogs', [BlogController::class, 'myBlogs']);
    Route::post('/blogs/{id}', [BlogController::class, 'updateBlog']);
    Route::delete('/blogs/{id}', [BlogController::class, 'deleteBlog']);

    // Donation routes
    Route::post('/donation/create-order', [DonationController::class, 'createOrder']);
    Route::post('/donation/verify-payment', [DonationController::class, 'verifyPayment']);
    Route::get('/my-donations', [DonationController::class, 'getAllDonations']);
});





Route::post('/admin/register', [AdminAuthController::class, 'register']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::get('/login', function () {
    return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
})->name('login');

// Admin Routes Group
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Dashboard Stats
    Route::get('/stats', [AdminDashboardController::class, 'getStats']);

    // User Management
    Route::apiResource('users', AdminUserController::class);

    // Media Management
    Route::apiResource('media', AdminMediaController::class);
    Route::post('media/{id}', [AdminMediaController::class, 'update']); // For multipart update

    // Blog Management
    Route::apiResource('blogs', AdminBlogController::class);
    Route::post('blogs/{id}', [AdminBlogController::class, 'update']); // For multipart update

    // Donation Management
    Route::apiResource('donations', AdminDonationController::class);

    // Notification Management
    Route::post('/notifications/send-all', [AdminNotificationController::class, 'sendToAllUsers']);
    Route::post('/notifications/users/{user}', [AdminNotificationController::class, 'sendToUser']);
    Route::post('/notifications/store', [AdminNotificationController::class, 'storeNotificationRecords']);
    Route::get('/notification-templates', [AdminNotificationController::class, 'listTemplates']);
    Route::post('/notification-templates', [AdminNotificationController::class, 'storeTemplate']);
    Route::put('/notification-templates/{template}', [AdminNotificationController::class, 'updateTemplate']);
    Route::delete('/notification-templates/{template}', [AdminNotificationController::class, 'destroyTemplate']);

    // Banner Management
    Route::get('/banners', [BennerController::class, 'getBanners']);
    Route::post('/banners', [BennerController::class, 'addBanner']);
    Route::delete('/banners/{id}', [BennerController::class, 'deleteBanner']);
    Route::post('/banners/{id}', [BennerController::class, 'updateBanner']);

    // Static Content Management
    Route::apiResource('about-us', AboutUsController::class);
    Route::apiResource('privacy-policy', PrivacyPolicyController::class);
    Route::apiResource('terms-condition', TermsConditionController::class);
    Route::apiResource('user-support', UserSupportController::class);
});

Route::get('/today-top-donors', [DonationController::class, 'getTodayTopDonors']);

//admin category routes
Route::get('/category', [CategoryController::class, 'index']);
Route::post('/category', [CategoryController::class, 'store']);
Route::put('/category/{id}', [CategoryController::class, 'update']);
Route::delete('/category/{id}', [CategoryController::class, 'destroy']);


// Notification route
Route::post('/sendnotification', [FirebaseController::class, 'sendNotificationToAllUsers']);


// Media routes
Route::get('/home-data', [MediaController::class, 'getHomeData']);
Route::get('/items', [MediaController::class, 'getItems']);
Route::get('/admin/banners', [BennerController::class, 'getBanners']);

// Public routes
Route::get('/blogs', [BlogController::class, 'getBlogs']);
Route::get('/blogs/{id}', [BlogController::class, 'getBlogDetail']);



// User Support routes
Route::get('/user-support', [UserSupportController::class, 'index']);
Route::post('/user-support', [UserSupportController::class, 'store']);



// About Us routes
Route::get('/about-us-page', [AboutUsController::class, 'showAboutUsPage']);
Route::get('/about-us', [AboutUsController::class, 'getAboutUs']);
Route::post('/about-us/save', [AboutUsController::class, 'saveAboutUs']);

// Privacy Policy routes
Route::get('/privacy-policy', [PrivacyPolicyController::class, 'getPrivacyPolicy']);
Route::post('/privacy-policy/save', [PrivacyPolicyController::class, 'savePrivacyPolicy']); // admin only
Route::get('/privacy-policy-page', [PrivacyPolicyController::class, 'showPrivacyPolicyPage']);


// terms and condition routes 
Route::get('/conditions', [TermsConditionController::class, 'showTermsPage']);
Route::get('/terms-conditions', [TermsConditionController::class, 'getTermsApi']);
Route::post('/terms-conditions/save', [TermsConditionController::class, 'saveOrUpdateTerms']);

Route::any('/clear', function () {
    Artisan::call('route:cache');
    Artisan::call('config:cache');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return 'Cache Cleared';
});
