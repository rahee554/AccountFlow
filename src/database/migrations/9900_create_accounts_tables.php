<?php

use Faker\Provider\ar_EG\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 = InActive , 1 = Active');

            $table->decimal('balance', 10, 2)->default(0.00);
            $table->timestamps(0);
        });
        Schema::create('ac_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('info')->comment('WYSIWYG Data of the Payment Method')->nullable();
            $table->string('logo_icon')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts');
            $table->tinyInteger('status')->default(1)->comment('1 = active, 2=inactive');
            $table->timestamps(0);
        });

        Schema::create('ac_categories', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('flow_type')->nullable()->comment('1 = Income, 2 = Expense');
            $table->string('name')->nullable();
            $table->integer('parent_id')->nullable()->comment('Its id from account_categories table. If parent_id is null, then it is the main category, else it is a sub-category');
            $table->tinyInteger('privacy')->default(1)->comment('1 = locked, 2 = unlocked');
            $table->string('icon')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 = Inactive, 1 = Active');
            $table->integer('added_by')->nullable();
            $table->timestamps(0);
        });
        Schema::create('ac_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->index();
            $table->foreignId('category_id')->constrained('ac_categories');
            $table->string('unique_id', 30)->unique(); // Add unique constraint here
            $table->integer('invoice_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('date')->nullable()->index();
            $table->longText('description')->nullable();
            $table->foreignId('payment_method')->nullable()->constrained('ac_payment_methods');
            $table->tinyInteger('type')->default(1)->comment('1 = Income, 2 = Expense');
            $table->foreignId('added_by')->nullable()->constrained('users');
            $table->timestamps(0);
        });

        Schema::create('ac_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('value', 10, 2)->default(0.00);
            $table->foreignId('category_id')->nullable()->constrained('ac_categories');
            $table->tinyInteger('status')->default(1)->comment('1 = Operating / Active, 2 = Not Operating / InActive, 3 = SoldOut');
            $table->date('acquisition_date');
            $table->timestamps(0);
        });
        Schema::create('ac_assets_trx', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->foreignId('asset_id')->nullable()->constrained('ac_assets');
            $table->foreignId('trx_id')->nullable()->constrained('ac_transactions');
            $table->timestamps(0);
        });
        Schema::create('ac_planned_payments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('ac_categories');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->foreignId('trx_id')->nullable()->constrained('ac_transactions');
            $table->date('due_date')->nullable();
            $table->string('period')->nullable()->comment('1 = monthly, 2 = quarterly, 3 = Half_Yearly, 4 = Annualy');
            $table->timestamps(0);
        });
        Schema::create('ac_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('type')->default(1)->comment('1 = Advance, 2 = Installments');
            $table->integer('installments')->nullable();
            $table->integer('repayment')->nullable()->comment('1 = monthly, 2 = quarterly, 3=halfYearly, 4 = Annualy');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->tinyInteger('status')->nullable()->comment('1 = piad, 2 = Partially paid, 3 = Not Paid');
            $table->date('date');
            $table->foreignId('category_id')->nullable()->constrained('ac_categories');
            $table->timestamps(0);
        });
        Schema::create('ac_purchase_trx', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->foreignId('purchase_id')->nullable()->constrained('ac_purchases');
            $table->foreignId('trx_id')->nullable()->constrained('ac_transactions');
            $table->timestamps(0);
        });
        Schema::create('ac_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps(0);
        });
        Schema::create('ac_loan_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact');
            $table->string('cnic');
            $table->string('company')->nullable();
            $table->text('note')->nullable();
            $table->timestamps(0);
        });

        Schema::create('ac_loans', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id', 10)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->tinyInteger('loan_type')->comment('1 = Lended, 2 = Borrowed');
            $table->unsignedBigInteger('loan_user_id');
            $table->tinyInteger('roi')->nullable();
            $table->tinyInteger('installments')->nullable()->comment('Number of Installments');
            $table->tinyInteger('installment_type')->nullable()->comment('1 = Monthly, 2 = Quarterly, 3 = Half Yearly, 4 = Annually');
            $table->tinyInteger('status')->nullable()->comment('1 = Returned, 2 = Partially Returned, 3 = Not Returned');
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->timestamps(0);
        });
        Schema::create('ac_loan_trx', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->foreignId('loan_id')->nullable()->constrained('ac_loans');
            $table->foreignId('trx_id')->nullable()->constrained('ac_transactions');
            $table->timestamps(0);
        });
        Schema::create('ac_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id', 10)->unique();

            $table->decimal('amount', 10, 2)->default(0.00);
            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('from_account');
            $table->foreign('from_account')->references('id')->on('accounts');

            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('to_account');
            $table->foreign('to_account')->references('id')->on('accounts');

            $table->text('description')->nullable();
            $table->date('date');

            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');

            $table->timestamps(0);
        });

        Schema::create('ac_user_wallets', function (Blueprint $table) {
            $table->id();

            $table->decimal('balance', 10, 2)->default(0.00);
            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->tinyInteger('status')->nullable()->comment('1 = Active, 2 = Freezed');


            $table->timestamps(0);
        });
        Schema::create('ac_user_transfers', function (Blueprint $table) {
            $table->id();

            $table->decimal('amount', 10, 2)->default(0.00);
            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('from');

            $table->unsignedBigInteger('to');

            $table->foreign('from')->references('id')->on('users');
            $table->foreign('to')->references('id')->on('users');
            $table->date('date');
            $table->timestamps(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('ac_assets');
        Schema::dropIfExists('ac_assets_trx');
        Schema::dropIfExists('ac_categories');
        Schema::dropIfExists('ac_loans');
        Schema::dropIfExists('ac_loan_trx');
        Schema::dropIfExists('ac_loan_users');
        Schema::dropIfExists('ac_payment_methods');
        Schema::dropIfExists('ac_planned_payments');
        Schema::dropIfExists('ac_purchases');
        Schema::dropIfExists('ac_purchase_trx');
        Schema::dropIfExists('ac_settings');
        Schema::dropIfExists('ac_transactions');
        Schema::dropIfExists('ac_transfers');
        Schema::dropIfExists('ac_user_transfers');
        Schema::dropIfExists('ac_user_wallets');

    }
};
