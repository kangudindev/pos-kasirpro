<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();
            $table->char('language', 7)->default('en');
            $table->boolean('is_active')->default(true);
            $table->enum('user_type', ['user', 'sales_commission_agent'])->default('user');
            $table->decimal('max_sale_discount', 5, 2)->nullable();
            $table->boolean('is_commission_agent')->default(false);
            $table->decimal('commission_percent', 4, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->text('bank_details')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
