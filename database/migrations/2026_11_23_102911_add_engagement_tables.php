<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengingat yang sudah terkirim (mencegah pengiriman ganda)
        Schema::create('booking_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 30);
            $table->timestamp('sent_at')->useCurrent();
            $table->unique(['booking_id', 'kind']);
        });

        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->json('title');
            $table->json('body');
            $table->boolean('is_published')->default(true);
            $table->boolean('show_in_footer')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('reviewer_name', 60);
            $table->boolean('is_visible')->default(true);
            $table->string('hidden_reason', 300)->nullable();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('hidden_at')->nullable();
            $table->text('partner_reply')->nullable();
            $table->dateTime('partner_replied_at')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'is_visible']);
            $table->index(['vehicle_id', 'is_visible']);
        });

        foreach (['hotels', 'vehicles'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('rating_avg', 3, 2)->default(0);
                $table->unsignedInteger('rating_count')->default(0);
            });
        }

        Schema::create('issue_reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30);
            $table->text('description');
            $table->string('evidence_path')->nullable();
            $table->enum('status', ['open', 'in_review', 'resolved', 'rejected'])->default('open');
            $table->text('resolution')->nullable();            // jawaban admin untuk pelanggan
            $table->text('partner_response')->nullable();
            $table->dateTime('partner_responded_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_reports');

        foreach (['hotels', 'vehicles'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['rating_avg', 'rating_count']));
        }

        Schema::dropIfExists('reviews');
        Schema::dropIfExists('static_pages');
        Schema::dropIfExists('booking_reminders');
    }
};