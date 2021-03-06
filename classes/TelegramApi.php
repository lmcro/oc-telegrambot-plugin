<?php namespace Vdomah\Telegram\Classes;
/**
 * This file is part of the Telegram plugin for OctoberCMS.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * (c) Anton Romanov <iam+octobercms@theone74.ru>
 */
 
use \Vdomah\Telegram\Models\TelegramInfoSettings;
use \Longman\TelegramBot\Exception\TelegramException;
use \Longman\TelegramBot\Request;

class TelegramApi extends \Longman\TelegramBot\Telegram
{

    public static $_instance;
	public static $_encoding = 'utf8';
	protected $commands_namespaces = [];

	public function __construct($api_key, $bot_name)
	{
		parent::__construct($api_key, $bot_name);
		$this->addCommandsPathMy(plugins_path('vdomah/telegram/commands'), 'Vdomah\\Telegram\\Commands');
		if ((TelegramInfoSettings::instance()->get('db_encoding'))) {
			self::$_encoding = TelegramInfoSettings::instance()->get('db_encoding');
		}
	}

	public function addCommandsPath($path, $before = true) {/* dummy */}

	public function addCommandsPathMy($path, $namespace, $before = true)
	{
		if (!is_dir($path)) {
			throw new TelegramException('Commands path "' . $path . '" does not exist!');
		}
		if (!in_array($path, $this->commands_paths)) {
			if ($before) {
				array_unshift($this->commands_paths, $path);
			} else {
				array_push($this->commands_paths, $path);
			}
		}
		if (!in_array($namespace, $this->commands_namespaces)) {
			if ($before) {
				array_unshift($this->commands_namespaces, $namespace);
			} else {
				array_push($this->commands_namespaces, $namespace);
			}
		}
		return $this;
	}

	public function getCommandObject($command)
	{
		$which = ['System'];
		($this->isAdmin()) && $which[] = 'Admin';
		$which[] = 'User';
		
		$command = explode('_', $command);
		$command = array_map(array($this, 'ucfirstUnicode'), $command);
		$command = implode('', $command);

		foreach ($this->commands_namespaces as $namespace) {
			foreach ($which as $auth) {
				$command_namespace = $namespace . '\\' . $auth . 'Commands\\' . $command . 'Command';
				if (class_exists($command_namespace)) {
					return new $command_namespace($this, $this->update);
				}
			}
		}

		return null;
	}

    public static function instance(){

        if ( ! self::$_instance) {
            if ( ! TelegramInfoSettings::instance()->get('token')) {
                throw new \Exception('Token not set');
            }

            if ( ! TelegramInfoSettings::instance()->get('name')) {
                throw new \Exception('Bot name not set');
            }

            self::$_instance = new TelegramApi(
                TelegramInfoSettings::instance()->get('token'),
                TelegramInfoSettings::instance()->get('name')
            );

            $mysql_credentials = [
                'host'      => \Config::get('database.connections.mysql.host'),
                'database'  => \Config::get('database.connections.mysql.database'),
                'user'  	=> \Config::get('database.connections.mysql.username'),
                'password'  => \Config::get('database.connections.mysql.password'),
            ];
            // TODO
            self::$_instance->enableMySQL($mysql_credentials, 'vdomah_telegram_', self::$_encoding);

			// batan.io
			if ($token = TelegramInfoSettings::instance()->get('botan_token')){
            	self::$_instance->enableBotan($token);
			}

			// enable admins
			$admins = [];
			foreach(TelegramInfoSettings::instance()->get('admins') as $i) {
				$admins[] = $i['admin'];
			}
			self::$_instance->enableAdmins($admins);
        }

        return self::$_instance;

    }

	public static function setDbEncoding($encoding) {
		self::$_encoding = $encoding;
	}

    public function sendMessage(array $data) {
        return Request::sendMessage($data);
    }

}
