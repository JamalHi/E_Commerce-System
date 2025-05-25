<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->references('user_id')->on('user_extras')->cascadeOnDelete();
            //$table->foreignId('owner_id')->constrained('user_extras')->cascadeOnDelete();
            //$table->string('size');
            //$table->string('color');
            $table->string('name');
            $table->text('description');
            $table->set('category',['clothes','shoes','fabrics' ]);//set
            $table->double('price');
            $table->set('gender',['male','female']);//set
            $table->integer('counter')->nullable();
            $table->double('rate')->nullable();//numeric
            //$table->integer('quant');
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
        Schema::dropIfExists('products');
    }
}
