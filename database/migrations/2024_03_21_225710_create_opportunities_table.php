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
        Schema::create('opportunities', function (Blueprint $table) {
          $table->id();
          $table->unsignedBigInteger('user_id');
          $table->unsignedBigInteger('prospect_id');
          $table->unsignedBigInteger('step_id');
          $table->timestamps();

          // Définition des clés étrangères
          $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
          $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
          $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
