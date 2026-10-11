<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->boolean('is_anonymous')->default(false);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->boolean('wants_follow_up')->default(false);
            $table->foreignId('tour_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating_overall')->nullable();
            $table->unsignedTinyInteger('rating_accommodation')->nullable();
            $table->unsignedTinyInteger('rating_transport')->nullable();
            $table->unsignedTinyInteger('rating_organization')->nullable();
            $table->unsignedTinyInteger('rating_service')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('locale', 5);
            $table->string('preferred_language', 5);
            $table->boolean('consent')->default(false);
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index('type');
            $table->index('created_at');
            $table->index(['type', 'rating_overall'], 'feedback_public_lookup');
        });

        Schema::create('feedback_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_submission_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('feedback_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('complaint_status', 20)->nullable();
            $table->text('note')->nullable();
            $table->text('response')->nullable();
            $table->timestamps();
            $table->index(['feedback_submission_id', 'created_at'], 'feedback_follow_up_lookup');
        });

        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_anonymous')->default(true);
            $table->date('opens_at')->nullable();
            $table->date('closes_at')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'opens_at', 'closes_at'], 'survey_window_lookup');
        });

        Schema::create('survey_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['survey_id', 'locale'], 'survey_locale_unique');
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['survey_id', 'position'], 'survey_question_order');
        });

        Schema::create('survey_question_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('prompt');
            $table->timestamps();
            $table->unique(['survey_question_id', 'locale'], 'survey_question_locale_unique');
        });

        Schema::create('survey_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('survey_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_option_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('label');
            $table->timestamps();
            $table->unique(['survey_option_id', 'locale'], 'survey_option_locale_unique');
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->index(['survey_id', 'email'], 'survey_response_email');
        });

        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_option_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating_value')->nullable();
            $table->text('text_value')->nullable();
            $table->timestamps();
            $table->index(['survey_question_id', 'survey_option_id'], 'survey_answer_choice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_option_translations');
        Schema::dropIfExists('survey_options');
        Schema::dropIfExists('survey_question_translations');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('survey_translations');
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('feedback_follow_ups');
        Schema::dropIfExists('feedback_reviews');
        Schema::dropIfExists('feedback_submissions');
    }
};
