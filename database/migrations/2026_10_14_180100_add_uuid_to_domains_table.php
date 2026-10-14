<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->uuid()->nullable()->after('id');
        });

        // Preenche domínios que já existiam antes da coluna
        DB::table('domains')->whereNull('uuid')->orderBy('id')->lazyById()->each(
            fn (object $domain) => DB::table('domains')->where('id', $domain->id)->update(['uuid' => (string) Str::uuid7()]),
        );

        Schema::table('domains', function (Blueprint $table) {
            $table->uuid()->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
