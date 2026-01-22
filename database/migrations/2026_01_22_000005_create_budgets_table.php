<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->string('department')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('limit_amount', 15, 2);
            $table->decimal('used_amount', 15, 2)->default(0);
            $table->decimal('warning_threshold', 5, 2)->default(80.00); // percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['category_id', 'department', 'year', 'month'], 'budget_unique');
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
