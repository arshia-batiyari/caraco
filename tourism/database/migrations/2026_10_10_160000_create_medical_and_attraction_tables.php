<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_pages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('medical_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('body')->nullable();
            $table->text('travel_assistance')->nullable();
            $table->text('stay_assistance')->nullable();
            $table->text('contact_note')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['medical_page_id', 'locale'], 'medical_page_locale_unique');
        });

        Schema::create('medical_categories', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index('is_published');
        });

        Schema::create('medical_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['medical_category_id', 'locale'], 'medical_category_locale_unique');
            $table->unique(['locale', 'slug'], 'medical_category_slug_unique');
        });

        Schema::create('medical_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_category_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('cover_path')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'medical_category_id'], 'medical_service_listing');
        });

        Schema::create('medical_service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_service_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_alt')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['medical_service_id', 'locale'], 'medical_service_locale_unique');
            $table->unique(['locale', 'slug'], 'medical_service_slug_unique');
        });

        Schema::create('medical_service_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_service_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['medical_service_id', 'position'], 'medical_service_image_order');
        });

        Schema::create('medical_service_image_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_service_image_id')->constrained(indexName: 'ms_image_translation_fk')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('alt')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->unique(['medical_service_image_id', 'locale'], 'medical_image_locale_unique');
        });

        Schema::create('medical_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('cover_path')->nullable();
            $table->timestamps();
            $table->index('is_published');
        });

        Schema::create('medical_provider_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_provider_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_alt')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['medical_provider_id', 'locale'], 'medical_provider_locale_unique');
            $table->unique(['locale', 'slug'], 'medical_provider_slug_unique');
        });

        Schema::create('medical_provider_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medical_service_id')->constrained()->cascadeOnDelete();
            $table->unique(['medical_provider_id', 'medical_service_id'], 'medical_provider_service_unique');
        });

        Schema::create('medical_doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'medical_provider_id'], 'medical_doctor_listing');
        });

        Schema::create('medical_doctor_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_doctor_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->string('specialty')->nullable();
            $table->text('biography')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['medical_doctor_id', 'locale'], 'medical_doctor_locale_unique');
            $table->unique(['locale', 'slug'], 'medical_doctor_slug_unique');
        });

        Schema::create('medical_steps', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['is_published', 'position'], 'medical_step_order');
        });

        Schema::create('medical_step_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_step_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamps();
            $table->unique(['medical_step_id', 'locale'], 'medical_step_locale_unique');
        });

        Schema::create('medical_faqs', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index('is_published');
        });

        Schema::create('medical_faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_faq_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('question');
            $table->text('answer')->nullable();
            $table->timestamps();
            $table->unique(['medical_faq_id', 'locale'], 'medical_faq_locale_unique');
        });

        Schema::create('medical_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('locale', 5);
            $table->string('preferred_language', 5);
            $table->string('country', 80)->nullable();
            $table->foreignId('medical_service_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->boolean('consent')->default(false);
            $table->string('status', 20)->default('new');
            $table->text('admin_note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at'], 'medical_inquiry_status');
        });

        Schema::create('attraction_categories', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index('is_published');
        });

        Schema::create('attraction_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attraction_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->timestamps();
            $table->unique(['attraction_category_id', 'locale'], 'attraction_category_locale_unique');
            $table->unique(['locale', 'slug'], 'attraction_category_slug_unique');
        });

        Schema::create('attractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attraction_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('classification', 20)->nullable();
            $table->string('unesco_designation', 20)->default('none');
            $table->boolean('unesco_verified')->default(false);
            $table->date('inscribed_on')->nullable();
            $table->string('unesco_reference', 40)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('cover_path')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'destination_id'], 'attraction_place_listing');
            $table->index(['is_published', 'attraction_category_id'], 'attraction_category_listing');
            $table->index(['unesco_designation', 'unesco_verified'], 'attraction_unesco_lookup');
        });

        Schema::create('attraction_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attraction_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->text('visitor_info')->nullable();
            $table->string('cover_alt')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['attraction_id', 'locale'], 'attraction_locale_unique');
            $table->unique(['locale', 'slug'], 'attraction_slug_unique');
        });

        Schema::create('attraction_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attraction_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['attraction_id', 'position'], 'attraction_image_order');
        });

        Schema::create('attraction_image_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attraction_image_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('alt')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->unique(['attraction_image_id', 'locale'], 'attraction_image_locale_unique');
        });

        Schema::create('attraction_tour', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attraction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->unique(['attraction_id', 'tour_id'], 'attraction_tour_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attraction_tour');
        Schema::dropIfExists('attraction_image_translations');
        Schema::dropIfExists('attraction_images');
        Schema::dropIfExists('attraction_translations');
        Schema::dropIfExists('attractions');
        Schema::dropIfExists('attraction_category_translations');
        Schema::dropIfExists('attraction_categories');
        Schema::dropIfExists('medical_inquiries');
        Schema::dropIfExists('medical_faq_translations');
        Schema::dropIfExists('medical_faqs');
        Schema::dropIfExists('medical_step_translations');
        Schema::dropIfExists('medical_steps');
        Schema::dropIfExists('medical_doctor_translations');
        Schema::dropIfExists('medical_doctors');
        Schema::dropIfExists('medical_provider_service');
        Schema::dropIfExists('medical_provider_translations');
        Schema::dropIfExists('medical_providers');
        Schema::dropIfExists('medical_service_image_translations');
        Schema::dropIfExists('medical_service_images');
        Schema::dropIfExists('medical_service_translations');
        Schema::dropIfExists('medical_services');
        Schema::dropIfExists('medical_category_translations');
        Schema::dropIfExists('medical_categories');
        Schema::dropIfExists('medical_page_translations');
        Schema::dropIfExists('medical_pages');
    }
};
