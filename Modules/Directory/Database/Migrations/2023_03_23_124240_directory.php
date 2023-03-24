<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Directory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filemanager_directories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->uniuqe();
            $table->foreignID('parent_id')->nullable();
            $table->foreignID('user_id')->nullable();
            $table->string('disk');
            $table->string('name');
            $table->string('path');
            $table->string('color_hex')->nullable();
            $table->string('description')->nullable();
            $table->string('permission')->nullable();
            $table->char('status')->default('a');
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
        Schema::dropIfExists('filemanager_directories');
    }
}
