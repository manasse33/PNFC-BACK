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
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('resume')->nullable();
            $table->text('programme')->nullable();
            $table->string('sector')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duree')->nullable(); // Durée en heures ou jours
            $table->date('end_date')->nullable();
            $table->string('image_couverture')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations');
        
    }
};
