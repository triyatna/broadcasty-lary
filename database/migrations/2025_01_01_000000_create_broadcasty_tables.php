<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('broadcasty_channels', function (Blueprint $t) {
            $t->id();
            $t->string('tenant_id')->index();
            $t->string('name')->index();
            $t->json('policy')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id','name']);
        });

        Schema::create('broadcasty_api_keys', function (Blueprint $t) {
            $t->id();
            $t->string('tenant_id')->index();
            $t->string('name');
            $t->string('key')->unique();
            $t->string('hash')->unique();
            $t->json('scopes')->nullable();
            $t->timestamp('rotated_at')->nullable();
            $t->timestamps();
        });

        Schema::create('broadcasty_events', function (Blueprint $t) {
            $t->id();
            $t->string('tenant_id')->index();
            $t->string('channel')->index();
            $t->unsignedBigInteger('partition')->index();
            $t->unsignedBigInteger('sequence')->index();
            $t->string('envelope_id')->index();
            $t->binary('payload');
            $t->json('meta')->nullable();
            $t->timestamp('published_at')->index();
            $t->unique(['tenant_id','channel','partition','sequence']);
        });

        Schema::create('broadcasty_audit_logs', function (Blueprint $t) {
            $t->id();
            $t->string('tenant_id')->index();
            $t->string('action');
            $t->string('actor_id')->nullable();
            $t->string('ip')->nullable();
            $t->json('ctx')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('broadcasty_audit_logs');
        Schema::dropIfExists('broadcasty_events');
        Schema::dropIfExists('broadcasty_api_keys');
        Schema::dropIfExists('broadcasty_channels');
    }
};