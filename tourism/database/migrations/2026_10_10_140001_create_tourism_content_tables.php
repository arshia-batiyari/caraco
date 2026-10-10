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
            $table->string('locale', 5)->unique();
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_lede')->nullable();
            $table->string('intro_heading')->nullable();
            $table->text('intro_body')->nullable();
            $table->string('destinations_heading')->nullable();
            $table->string('categories_heading')->nullable();
            $table->string('popular_heading')->nullable();
            $table->string('discounted_heading')->nullable();
            $table->string('domestic_heading')->nullable();
            $table->string('international_heading')->nullable();
            $table->string('experiences_heading')->nullable();
            $table->string('medical_heading')->nullable();
            $table->text('medical_body')->nullable();
            $table->string('cta_heading')->nullable();
            $table->text('cta_text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
        });

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('destinations')->nullOnDelete();
            $table->string('type', 20);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'is_featured', 'type']);
        });

        Schema::create('destination_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['destination_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index('is_published');
        });

        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['category_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('travel_styles', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('travel_style_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_style_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->timestamps();
            $table->unique(['travel_style_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('transport_options', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 20);
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['is_published', 'mode']);
        });

        Schema::create('transport_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_option_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['transport_option_id', 'locale'], 'transport_locale_unique');
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['type', 'is_published']);
        });

        Schema::create('service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['service_id', 'locale']);
        });

        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('accommodation_category')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'is_featured']);
        });

        Schema::create('hotel_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->text('address')->nullable();
            $table->text('room_details')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['hotel_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('hotel_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('hotel_facility_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_facility_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->timestamps();
            $table->unique(['hotel_facility_id', 'locale'], 'facility_locale_unique');
        });

        Schema::create('hotel_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 20);
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('travel_style_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('cover_path')->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->date('discount_starts_at')->nullable();
            $table->date('discount_ends_at')->nullable();
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->unsignedSmallInteger('duration_nights')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'scope']);
            $table->index('category_id');
            $table->index('destination_id');
            $table->index(['currency', 'base_price']);
            $table->index(['is_published', 'is_featured']);
        });

        Schema::create('tour_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('departure_location')->nullable();
            $table->text('meeting_instructions')->nullable();
            $table->text('transport_details')->nullable();
            $table->text('meals')->nullable();
            $table->text('notes')->nullable();
            $table->text('requirements')->nullable();
            $table->text('restrictions')->nullable();
            $table->text('terms')->nullable();
            $table->text('cancellation')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['tour_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('tour_departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->date('departure_date');
            $table->date('return_date');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->index(['tour_id', 'is_published', 'departure_date'], 'tour_departure_lookup');
        });

        Schema::create('tour_transport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_option_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->unique(['tour_id', 'transport_option_id']);
        });

        Schema::create('tour_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->unique(['tour_id', 'service_id']);
        });

        Schema::create('tour_hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('tour_hotel_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_hotel_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('arrangement')->nullable();
            $table->timestamps();
            $table->unique(['tour_hotel_id', 'locale'], 'tour_hotel_locale_unique');
        });

        Schema::create('tour_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();
        });

        Schema::create('itinerary_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->timestamps();
            $table->unique(['tour_id', 'day_number']);
        });

        Schema::create('itinerary_day_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_day_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title')->nullable();
            $table->longText('body')->nullable();
            $table->timestamps();
            $table->unique(['itinerary_day_id', 'locale'], 'itinerary_locale_unique');
        });

        Schema::create('route_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('itinerary_day_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->index(['tour_id', 'position']);
        });

        Schema::create('route_point_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_point_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['route_point_id', 'locale'], 'route_point_locale_unique');
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['tour_id', 'is_published']);
        });

        Schema::create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('question');
            $table->text('answer')->nullable();
            $table->timestamps();
            $table->unique(['faq_id', 'locale']);
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

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('locale', 5);
            $table->string('preferred_language', 5);
            $table->foreignId('tour_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('destination_text')->nullable();
            $table->date('travel_start')->nullable();
            $table->date('travel_end')->nullable();
            $table->unsignedSmallInteger('travelers')->nullable();
            $table->text('message');
            $table->boolean('consent');
            $table->string('status', 20)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('faq_translations');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('route_point_translations');
        Schema::dropIfExists('route_points');
        Schema::dropIfExists('itinerary_day_translations');
        Schema::dropIfExists('itinerary_days');
        Schema::dropIfExists('tour_images');
        Schema::dropIfExists('tour_hotel_translations');
        Schema::dropIfExists('tour_hotels');
        Schema::dropIfExists('tour_service');
        Schema::dropIfExists('tour_transport');
        Schema::dropIfExists('tour_departures');
        Schema::dropIfExists('tour_translations');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('hotel_images');
        Schema::dropIfExists('hotel_facility_translations');
        Schema::dropIfExists('hotel_facilities');
        Schema::dropIfExists('hotel_translations');
        Schema::dropIfExists('hotels');
        Schema::dropIfExists('service_translations');
        Schema::dropIfExists('services');
        Schema::dropIfExists('transport_option_translations');
        Schema::dropIfExists('transport_options');
        Schema::dropIfExists('travel_style_translations');
        Schema::dropIfExists('travel_styles');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('destination_translations');
        Schema::dropIfExists('destinations');
        Schema::dropIfExists('homepage_translations');
        Schema::dropIfExists('settings');
    }
};
