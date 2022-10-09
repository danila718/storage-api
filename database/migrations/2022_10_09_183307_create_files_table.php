<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->foreignId('dir_id')->nullable()->constrained('folders');
            $table->foreignId('created_by')->constrained('users');
            $table->uuid('share_id')->unique()->nullable()->index();
            $table->timestamps();
            $table->unique(['name', 'dir_id', 'created_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
};
