<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('farmer_name')->index();
            $table->string('variety_name')->index();
            $table->date('survey_date');
            $table->decimal('temperature', 4, 1);
            $table->text('growth_status');
            $table->decimal('latitude', 11, 8);
            $table->decimal('longitude', 11, 8);
            $table->json('photos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
