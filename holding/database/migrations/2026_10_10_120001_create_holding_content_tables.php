<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        Schema::create('homepage_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5);
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_lede')->nullable();
            $table->string('intro_heading')->nullable();
            $table->text('intro_body')->nullable();
            $table->string('divisions_heading')->nullable();
            $table->string('approach_heading')->nullable();
            $table->text('approach_body')->nullable();
            $table->string('projects_heading')->nullable();
            $table->string('cta_heading')->nullable();
            $table->text('cta_text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique('locale');
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->unsignedSmallInteger('position');
            $table->boolean('is_published')->default(false);
            $table->string('destination_url')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('division_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->text('introduction')->nullable();
            $table->longText('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['division_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable()->unique();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->longText('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['page_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('project_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('location', 20);
            $table->string('kind', 20);
            $table->string('route_name')->nullable();
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('navigation_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('navigation_item_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('label');
            $table->timestamps();
            $table->unique(['navigation_item_id', 'locale'], 'nav_item_locale_unique');
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30);
            $table->string('url');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('contact_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5)->unique();
            $table->text('address')->nullable();
            $table->string('hours')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->string('locale', 5);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('contact_translations');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('navigation_item_translations');
        Schema::dropIfExists('navigation_items');
        Schema::dropIfExists('project_translations');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('division_translations');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('homepage_translations');
        Schema::dropIfExists('settings');
    }
};
