<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomInvitationController;
use App\Http\Controllers\RoomStreamController;
use App\Http\Controllers\RoomVideoController;
use App\Http\Controllers\VideoControlController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/send-message', [ChatMessageController::class, 'store'])->name('rooms.sendMessage');

Route::post('/video-control', [VideoControlController::class, 'store'])->name('rooms.videoControl');

Route::get('/messages', [HomeController::class, 'messages']);

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/verify-email', [EmailVerificationController::class, 'show'])->name('verification.notice');
Route::post('/verify-email', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');

Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

Route::get('/logout', [AuthController::class, 'logout']);

Route::get('rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('rooms/owned', [RoomController::class, 'owned'])->name('rooms.owned');
Route::get('rooms/create', [RoomController::class, 'create'])->name('rooms.create');
Route::post('rooms/store', [RoomController::class, 'store'])->name('rooms.store');
Route::get('rooms/show/{room}', [RoomController::class, 'show'])->name('rooms.show');

Route::post('rooms/uploadVideo/{room}', [RoomVideoController::class, 'upload'])->name('rooms.uploadVideo');
Route::post('rooms/upload/init/{room}', [RoomVideoController::class, 'init'])->name('rooms.uploadInit');
Route::post('rooms/upload/chunk/{room}', [RoomVideoController::class, 'chunk'])->name('rooms.uploadChunk');
Route::post('rooms/upload/finish/{room}', [RoomVideoController::class, 'finish'])->name('rooms.uploadFinish');
Route::get('rooms/video/{room}', [RoomVideoController::class, 'stream'])->name('rooms.video');

Route::get('rooms/watch/{room}', [RoomStreamController::class, 'watch'])->name('rooms.watch');
Route::get('rooms/startStream/{room}', [RoomStreamController::class, 'start'])->name('rooms.startStream');
Route::get('rooms/stopStream/{room}', [RoomStreamController::class, 'stop'])->name('rooms.stopStream');

Route::get('rooms/generate-invite-link/{room}', [RoomInvitationController::class, 'generateLink'])->name('rooms.generateInviteLink');
Route::get('rooms/join/{room}/{token}', [RoomInvitationController::class, 'join'])->name('rooms.join');
Route::get('rooms/invitations/{room}', [RoomInvitationController::class, 'index'])->name('rooms.invitations');
Route::post('rooms/generateInvitation/{room}', [RoomInvitationController::class, 'store'])->name('rooms.generateInvitation');
Route::delete('rooms/deleteInvitation/{room}/{invitation}', [RoomInvitationController::class, 'destroy'])->name('rooms.deleteInvitation');
