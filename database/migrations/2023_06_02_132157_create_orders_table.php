<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('by_id')->constrained('users')->cascadeOnDelete();//هيك صح؟
          //  $table->foreignId('from_id')->constrained('products')->cascadeOnDelete();//هيك صح؟
            $table->string('location');//set
            $table->double('total_price')->nullable();
            $table->boolean('isDelivered')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
