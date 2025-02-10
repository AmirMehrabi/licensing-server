<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

 class CreateLicenseTokensTable extends Migration {
    public function up()
    {
        if (!Schema::hasTable('license_tokens')) {
        Schema::create('license_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('license_id');
            $table->string('token', 100)->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
    }

    public function down()
    {
        Schema::dropIfExists('license_tokens');
    }
}