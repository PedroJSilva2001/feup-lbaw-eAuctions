<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Home
Route::get('/', 'HomepageController@show')->name('homepage');

// Static Pages
Route::get('/about', function() { return view('pages.about'); })->name('about');
Route::get('/services', function() { return view('pages.services'); })->name('services');
Route::get('/contacts', function() { return view('pages.contacts'); })->name('contacts');
Route::get('/banned', function() { return view('pages.banned'); })->name('banned');

// Search
Route::get('/search', 'SearchController@showAll')->name('search');
Route::post('/search', 'SearchController@showFiltered');

// Authentication
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('/register', 'Auth\RegisterController@register');

// Reset Password
Route::get('/forgot-password', 'Auth\SendPasswordResetController@showEmailForm')->name('password.request');
Route::post('/forgot-password', 'Auth\SendPasswordResetController@emailForm')->name('password.email');
Route::get('/reset-password/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/reset-password', 'Auth\ResetPasswordController@resetForm')->name('password.update');

// User
Route::get('/users/{id}', 'UserController@showProfile')->name('show_profile');
Route::get('/users/{id}/settings/account', 'UserController@showEditProfile')->name('show_settings');
Route::post('/users/{id}/settings/update', 'UserController@update')->name('update');
Route::get('/users/{id}/settings/update', 'UserController@showRedirect');
Route::get('/users/{id}/settings/payments', 'UserController@showPaymentsProfile')->name('show_payments');
Route::get('/users/{id}/settings/delete-account', 'UserController@showDeleteProfile')->name('show_delete');
Route::post('/users/{id}/settings/delete-account', 'UserController@delete')->name('delete_account');

Route::get('/users/{id}/auctions', 'UserController@showOwned')->name('owned');
Route::get('/users/{id}/followed', 'UserController@showFollowed');
Route::get('/users/{id}/bids', 'UserController@showBids')->name('bids');
Route::get('/users/{id}/drafts', 'UserController@showDrafts');
Route::get('/users/{id}/notifications', 'UserController@showNotifications');

Route::get('/users/{id}/createAuction', 'AuctionController@showCreateForm')->name('show_form');
Route::post('/users/{id}/createAuction', 'AuctionController@create')->name('create');


// Moderation
Route::get('/admins/', 'AdminController@showPage')->name('show_admins_page');
Route::post('/admins/block/users/{user_id}', 'AdminController@blockUser')->name('block_user');
Route::get('/admins/block/users/{user_id}', 'AdminController@showBlockPage')->name('show_block_page');
Route::post('/admins/unblock/users/{user_id}', 'AdminController@unblockUser')->name('unblock_user');
Route::get('/admins/unblock/users/{user_id}', 'AdminController@showRedirect');
Route::get('/admins/blocked', 'AdminController@showBlockedUsers')->name('show_blocked_users');
Route::get('/admins/transactions-pending', 'AdminController@showTransactionsPending')->name('show_transactions_pending');
Route::get('/admins/transactions-accepted', 'AdminController@showTransactionsAccepted')->name('show_transactions_accepted');
Route::get('/admins/transactions-declined', 'AdminController@showTransactionsDeclined')->name('show_transactions_declined');
Route::post('/admins/transactions/{id}', 'AdminController@requestTransaction')->name('transaction_request');
Route::get('/admins/transactions/{id}', 'AdminController@showRedirect');
Route::post('/admins/transactions-confirm/{id}', 'AdminController@confirmTransaction')->name('transaction_confirm');
Route::get('/admins/transactions-confirm/{id}', 'AdminController@showRedirect');

// PayPal
Route::get('handle-payment/{id}/{amount}', 'PayPalPaymentController@handlePayment')->name('make.payment');
Route::get('payment-success/{id}/{amount}', 'PayPalPaymentController@paymentSuccess')->name('success.payment');

// Auctions
Route::get('/auctions/{id}', 'AuctionController@show')->name('auction');
Route::get('/auctions/{id}/bid/current', 'AuctionController@getCurrentBid')->name('current_bid');
Route::post('/auctions/{id}/bid', 'AuctionController@bid')->name('bid');
Route::get('/auctions/{id}/bid', 'AuctionController@showRedirect');
Route::get('/auctions/{id}/edit', 'AuctionController@showEditForm')->name('edit');
Route::post('/auctions/{id}/edit', 'AuctionController@edit');

Route::get('/auctions/{id}/follow', 'AuctionController@followAuction')->name('followAuction');
Route::get('/auctions/{id}/unfollow', 'AuctionController@unfollowAuction')->name('unfollowAuction');

Route::post('/auctions/{id}/rating', 'AuctionController@rateSeller')->name('rateSeller');
Route::get('/auctions/{id}/rating', 'AuctionController@showRedirect');

Route::post('/auctions/{id}/comments', 'CommentController@store')->name('comments.store');
Route::get('/auctions/{id}/comments', 'AuctionController@showRedirect');
Route::post('/auctions/{id}/comments/{comment_id}', 'CommentController@delete')->name('comments.user.delete');
Route::get('/auctions/{id}/comments/{comment_id}', 'CommentController@showComment');

// Categories
Route::get('/category/{category}', 'CategoryController@show')->name('category');
