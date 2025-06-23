<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hero_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('avatar')->nullable();
            $table->string('full_name');
            $table->date('birth_date');
            $table->string('city');
            $table->string('education');
            $table->string('specialization');
            $table->string('stack');
            $table->date('experience_start');
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('hero_profiles');
    }
}; 