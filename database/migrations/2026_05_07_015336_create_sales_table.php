<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('product_category')->nullable();
            $table->integer('quantity');
            $table->decimal('price_per_unit', 15, 2);
            $table->decimal('total_revenue', 15, 2);
            $table->decimal('total_profit', 15, 2)->default(0);
            $table->timestamp('sold_at');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sales');
    }
};