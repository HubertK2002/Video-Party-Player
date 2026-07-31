<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_public')->default(false);
            $table->string('name');
            $table->timestamps();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade')->notNullable();
        });

        Schema::create('room_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('room_invitations', function (Blueprint $table) {
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('invitation_code')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_users');
        Schema::dropIfExists('room_invitations');
    }
};
