<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function(Blueprint $t) {
            $t->id(); $t->string('name')->unique(); $t->string('slug')->unique();
            $t->text('description')->nullable(); $t->string('icon')->nullable(); $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('products', function(Blueprint $t) {
            $t->id(); $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->string('sku')->unique(); $t->string('name'); $t->text('description')->nullable();
            $t->decimal('price',12,2)->default(0); $t->decimal('purchase_price',12,2)->default(0);
            $t->string('unit',30)->default('pcs'); $t->unsignedInteger('stock')->default(0);
            $t->unsignedInteger('min_stock')->default(5); $t->string('image')->nullable();
            $t->boolean('is_active')->default(true); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('suppliers', function(Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('pic')->nullable(); $t->string('phone',30)->nullable();
            $t->string('email')->nullable(); $t->text('address')->nullable(); $t->text('notes')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('product_batches', function(Blueprint $t) {
            $t->id(); $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $t->string('batch_code'); $t->unsignedInteger('quantity')->default(0);
            $t->date('production_date')->nullable(); $t->date('received_date'); $t->date('expiry_date')->index();
            $t->decimal('purchase_price',12,2)->default(0); $t->string('status',20)->default('active');
            $t->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('checked_at')->nullable(); $t->text('notes')->nullable(); $t->timestamps();
            $t->index(['product_id','expiry_date']);
        });
        Schema::create('stock_movements', function(Blueprint $t) {
            $t->id(); $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type',30); $t->integer('quantity'); $t->unsignedInteger('balance')->default(0);
            $t->string('reference')->nullable(); $t->text('notes')->nullable(); $t->timestamps();
        });
        Schema::create('goods_receipts', function(Blueprint $t) {
            $t->id(); $t->string('receipt_number')->unique(); $t->foreignId('supplier_id')->constrained();
            $t->foreignId('user_id')->constrained(); $t->date('received_at'); $t->decimal('total',14,2)->default(0);
            $t->string('proof')->nullable(); $t->text('notes')->nullable(); $t->timestamps();
        });
        Schema::create('goods_receipt_details', function(Blueprint $t) {
            $t->id(); $t->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained(); $t->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $t->unsignedInteger('quantity'); $t->decimal('purchase_price',12,2); $t->timestamps();
        });
        Schema::create('returns', function(Blueprint $t) {
            $t->id(); $t->string('return_number')->unique(); $t->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->constrained(); $t->string('status',25)->default('pending');
            $t->string('proof')->nullable(); $t->text('reason'); $t->text('admin_note')->nullable();
            $t->timestamp('approved_at')->nullable(); $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamps();
        });
        Schema::create('return_details', function(Blueprint $t) {
            $t->id(); $t->foreignId('return_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained(); $t->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $t->unsignedInteger('quantity'); $t->decimal('unit_price',12,2)->default(0); $t->timestamps();
        });
        Schema::create('orders', function(Blueprint $t) {
            $t->id(); $t->string('order_number')->unique(); $t->foreignId('user_id')->constrained();
            $t->string('status',30)->default('pending')->index(); $t->string('delivery_method',30)->default('internal');
            $t->string('payment_method',30)->default('cod'); $t->string('payment_status',30)->default('unpaid');
            $t->decimal('subtotal',14,2); $t->decimal('shipping_cost',14,2)->default(0); $t->decimal('total',14,2);
            $t->text('shipping_address'); $t->string('phone',30); $t->text('notes')->nullable();
            $t->timestamp('confirmed_at')->nullable(); $t->timestamp('completed_at')->nullable(); $t->timestamps();
        });
        Schema::create('order_details', function(Blueprint $t) {
            $t->id(); $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained(); $t->unsignedInteger('quantity');
            $t->decimal('unit_price',12,2); $t->decimal('subtotal',14,2); $t->timestamps();
        });
        Schema::create('payments', function(Blueprint $t) {
            $t->id(); $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('method',30); $t->decimal('amount',14,2); $t->string('status',30)->default('pending');
            $t->string('proof')->nullable(); $t->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('paid_at')->nullable(); $t->text('note')->nullable(); $t->timestamps();
        });
        Schema::create('deliveries', function(Blueprint $t) {
            $t->id(); $t->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $t->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status',30)->default('waiting'); $t->string('tracking_number')->nullable();
            $t->text('courier_note')->nullable(); $t->timestamp('assigned_at')->nullable();
            $t->timestamp('started_at')->nullable(); $t->timestamp('delivered_at')->nullable(); $t->timestamps();
        });
        Schema::create('conversations', function(Blueprint $t) {
            $t->id(); $t->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->string('subject')->nullable(); $t->string('status',20)->default('open');
            $t->timestamp('last_message_at')->nullable(); $t->timestamps(); $t->index(['buyer_id','status']);
        });
        Schema::create('messages', function(Blueprint $t) {
            $t->id(); $t->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $t->text('body'); $t->timestamp('read_at')->nullable(); $t->timestamps();
        });
        Schema::create('notifications', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type',50); $t->string('title'); $t->text('body')->nullable();
            $t->string('url')->nullable(); $t->timestamp('read_at')->nullable(); $t->timestamps();
        });
        Schema::create('activity_logs', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action'); $t->string('subject_type')->nullable(); $t->unsignedBigInteger('subject_id')->nullable();
            $t->text('description')->nullable(); $t->string('ip_address',45)->nullable(); $t->timestamps();
        });
        Schema::create('store_settings', function(Blueprint $t) {
            $t->id(); $t->string('key')->unique(); $t->longText('value')->nullable(); $t->timestamps();
        });
    }
    public function down(): void {
        foreach (['store_settings','activity_logs','notifications','messages','conversations','deliveries','payments','order_details','orders','return_details','returns','goods_receipt_details','goods_receipts','stock_movements','product_batches','suppliers','products','categories'] as $table) Schema::dropIfExists($table);
    }
};
