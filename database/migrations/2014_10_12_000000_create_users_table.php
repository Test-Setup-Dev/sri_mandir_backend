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
        Schema::table('users', function (Blueprint $table) {
    $table->string('phone', 20)->nullable()->after('email');
    $table->string('image', 500)->nullable()->after('phone');
    $table->date('date_of_birth')->nullable()->after('image');
    $table->string('city', 100)->nullable()->after('date_of_birth');
    $table->string('address', 255)->nullable()->after('city');
    $table->string('pincode', 20)->nullable()->after('address');
    $table->string('state', 100)->nullable()->after('pincode');
    $table->string('country', 100)->nullable()->after('state');
    $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('country');
    $table->text('token')->nullable()->after('gender');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
