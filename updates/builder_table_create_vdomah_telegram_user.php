<?php namespace Vdomah\Telegram\Updates;
/**
 * This file is part of the Telegram plugin for OctoberCMS.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * (c) Anton Romanov <iam+octobercms@theone74.ru>
 */
 
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateVdomahTelegramUser extends Migration
{
    public function up()
    {
        Schema::create('vdomah_telegram_user', function($table)
        {
            $table->engine = 'InnoDB';
            $table->bigInteger('id');
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('username', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->primary('id');
            $table->index(['username'], 'username');
        });
    }

    public function down()
    {
        Schema::drop('vdomah_telegram_user');
    }

}
