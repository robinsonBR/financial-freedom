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
        // Add Plaid fields to cash_accounts table
        Schema::table('cash_accounts', function (Blueprint $table) {
            $table->string('connection_type')->default('manual')->after('balance'); // 'manual' or 'plaid'
            $table->string('plaid_access_token')->nullable()->after('connection_type');
            $table->string('plaid_item_id')->nullable()->after('plaid_access_token');
            $table->string('plaid_account_id')->nullable()->after('plaid_item_id');
            $table->string('plaid_institution_id')->nullable()->after('plaid_account_id');
            $table->timestamp('last_synced_at')->nullable()->after('plaid_institution_id');
            $table->string('sync_status')->default('idle')->after('last_synced_at'); // idle, syncing, success, error
            $table->text('sync_error')->nullable()->after('sync_status');
        });

        // Add Plaid fields to credit_cards table
        Schema::table('credit_cards', function (Blueprint $table) {
            $table->string('connection_type')->default('manual')->after('balance');
            $table->string('plaid_access_token')->nullable()->after('connection_type');
            $table->string('plaid_item_id')->nullable()->after('plaid_access_token');
            $table->string('plaid_account_id')->nullable()->after('plaid_item_id');
            $table->string('plaid_institution_id')->nullable()->after('plaid_account_id');
            $table->timestamp('last_synced_at')->nullable()->after('plaid_institution_id');
            $table->string('sync_status')->default('idle')->after('last_synced_at');
            $table->text('sync_error')->nullable()->after('sync_status');
        });

        // Add Plaid fields to loans table
        Schema::table('loans', function (Blueprint $table) {
            $table->string('connection_type')->default('manual')->after('payment_amount');
            $table->string('plaid_access_token')->nullable()->after('connection_type');
            $table->string('plaid_item_id')->nullable()->after('plaid_access_token');
            $table->string('plaid_account_id')->nullable()->after('plaid_item_id');
            $table->string('plaid_institution_id')->nullable()->after('plaid_account_id');
            $table->timestamp('last_synced_at')->nullable()->after('plaid_institution_id');
            $table->string('sync_status')->default('idle')->after('last_synced_at');
            $table->text('sync_error')->nullable()->after('sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'connection_type',
                'plaid_access_token',
                'plaid_item_id',
                'plaid_account_id',
                'plaid_institution_id',
                'last_synced_at',
                'sync_status',
                'sync_error'
            ]);
        });

        Schema::table('credit_cards', function (Blueprint $table) {
            $table->dropColumn([
                'connection_type',
                'plaid_access_token',
                'plaid_item_id',
                'plaid_account_id',
                'plaid_institution_id',
                'last_synced_at',
                'sync_status',
                'sync_error'
            ]);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'connection_type',
                'plaid_access_token',
                'plaid_item_id',
                'plaid_account_id',
                'plaid_institution_id',
                'last_synced_at',
                'sync_status',
                'sync_error'
            ]);
        });
    }
};
