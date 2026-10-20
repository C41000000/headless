<?php

declare(strict_types=1);

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
        Schema::create('visual_identities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('primary_color', 9)->default('#0D6EFD');
            $table->string('secondary_color', 9)->default('#6C757D');
            $table->string('accent_color', 9)->nullable();
            $table->string('logo_url', 2048)->nullable();
            $table->string('logo_dark_url', 2048)->nullable();
            $table->string('favicon_url', 2048)->nullable();
            $table->string('font_family', 100)->nullable();
            $table->text('custom_css')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visual_identities');
    }
};
