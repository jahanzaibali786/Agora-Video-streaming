<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('message_seens', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_message_id');
            $table->bigInteger('group_id');
            $table->bigInteger('group_user_id');
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_seens');
    }
};
