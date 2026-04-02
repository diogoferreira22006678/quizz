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
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_player_id')->constrained()->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->string('answer_choice')->nullable();
            $table->boolean('is_correct')->nullable()->index();
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->unique(['quiz_session_id', 'quiz_question_id', 'quiz_player_id'], 'quiz_answers_unique_per_question');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
