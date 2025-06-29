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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('id_type');
            $table->string('id_no');
            $table->date('dob');
            $table->text('address');
            $table->string('school_name')->nullable();
            $table->string('company_name')->nullable();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('referral_source_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email')->unique();
            $table->string('phone_home')->nullable();
            $table->string('phone_residence')->nullable();
            $table->string('phone_whatsapp');
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
