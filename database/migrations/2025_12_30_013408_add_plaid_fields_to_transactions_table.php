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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('plaid_transaction_id')->nullable()->unique()->after('original');
            $table->string('merchant_name')->nullable()->after('plaid_transaction_id');
            $table->boolean('pending')->default(false)->after('merchant_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['plaid_transaction_id', 'merchant_name', 'pending']);
        });
    }
};
