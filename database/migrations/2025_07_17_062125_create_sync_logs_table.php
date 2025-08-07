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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // Model apa yang dikirim (e.g., 'Asset', 'Incident')
            $table->unsignedBigInteger('model_id'); // ID dari data yang dikirim
            $table->string('status'); // 'success' atau 'failed'
            $table->integer('response_code')->nullable(); // Kode status HTTP (e.g., 201, 422, 500)
            $table->text('response_body')->nullable(); // Pesan error dari Aplikasi 1
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
