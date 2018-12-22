<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsToStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->unsignedInteger('emails_received')->change();
            $table->unsignedInteger('inboxes_created')->change();
            $table->unsignedBigInteger('storage_used')->change();
            $table->unsignedInteger('emails_deleted')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->integer('emails_received')->change();
            $table->integer('inboxes_created')->change();
            $table->integer('storage_used')->change();
            $table->integer('emails_deleted')->change();
        });
    }
}
