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
        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->string('recurring_type')->default('weekly')->comment('weekly, monthly');
            $table->string('recurring_day')->nullable()->comment('monday, tuesday, wednesday, thursday, friday, saturday, sunday');
            $table->dateTime('recurring_date')->nullable();
            $table->dateTime('recurring_hour')->nullable();
            $table->dateTime('recurring_minute')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_run_at')->nullable();
            $table->dateTime('next_run_at')->nullable();
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
        Schema::dropIfExists('recurring_tasks');
    }
};
