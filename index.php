<?php
require_once __DIR__."/vendor/autoload.php";

use Dotenv\Dotenv;
use Eslavon\Adiantum\Control\BotMessage;
use Eslavon\Adiantum\Vk\BotApi;
use Eslavon\Adiantum\Vk\Callback;

$dotenv = Dotenv::createImmutable (__DIR__,"config.env")->load();
$callback = new Callback(file_get_contents("php://input"));
$bot_message = new BotMessage();
$vk = new BotApi($_ENV["ACCESS_TOKEN"],$_ENV["VERSION"]);

$log = "command: ".$callback->command."\n group_id: ".$callback->group_id."\n user_id: ".$callback->user_id."\n inline: ".$callback->inline."\n keyboard: ".$callback->keyboard;
$button1 = [$callback->command,"Повторить","secondary","text"];
$button = [[$button1]];
$vk->setKeyboard($button,"inline");
$vk->sendMessage(251510315,$log);

echo "ok";

switch ($callback->command) {
	# Клиент пользователя не поддерживает клавиатуру
	case "error_keyboard";
		$message = $bot_message->getMessage("error_keyboard");
		$button1 = ["start","\xf0\x9f\x94\x84 Обновить","positive","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$setting = array("dont_parse_links" => 1); //Параметры
        $vk->setSetting($setting);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Пользователь впервые в боте
	case "start";
	    $name = $vk->getUserFirstName($callback->peer_id);
		$message = $bot_message->getFormatMessage("start",["NAME_USER","NAME_BOT"],[$name,$_ENV["NAME_BOT"]]);
		$button1 = ["reg_user_profile_view","\xe2\x9c\x94 Продолжить","positive","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Предпросмотр анкеты
	case "reg_user_profile_view";
		$message = $bot_message->getMessage("reg_user_profile_view_1");
		$button1 = ["reg_user_edit_menu","\xf0\x9f\x93\x9d Редактировать анкету","positive","text"];
		$button2 = ["reg_user_option_menu","\xe2\x9e\xa1 Пропустить","secondary","text"];
		$button = [[$button1],[$button2]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
		
	# Изменить основные параметры анкеты	
	case "reg_user_edit_menu";
		$message = $bot_message->getMessage("reg_user_edit_menu");
		$button1 = ["reg_user_set_sex","\xf0\x9f\x91\xab Изменить пол","primary","text"];
		$button2 = ["reg_user_set_age","\xf0\x9f\x97\x93 Изменить возраст","primary","text"];
		$button3 = ["reg_user_set_photo","\xf0\x9f\x93\xb8 Изменить фото","primary","text"];
		$button4 = ["reg_user_set_search","\xf0\x9f\x94\x8e Изменить предпочтения","primary","text"];
		$button5 = ["reg_user_set_location","\xf0\x9f\x8f\x99 Изменить город","primary","text"];
		$button6 = ["reg_user_option_menu","\xe2\x9e\xa1 Продолжить","secondary","text"];
		$button = [[$button1,$button2],[$button3],[$button4],[$button5],[$button6]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Выбор пола пользователя
	case "reg_user_set_sex";
		$message = $bot_message->getMessage("reg_user_set_sex");
		$button1 = ["reg_user_save_sex 1","\xf0\x9f\x92\x83 Я девушка","positive","text"];
		$button2 = ["reg_user_save_sex 2","\xf0\x9f\x95\xba Я парень","positive","text"];
		$button = [[$button1,$button2]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить пол пользователя
	case "reg_user_save_sex";
		$message = $bot_message->getMessage("reg_user_save_sex");
		$button1 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
			
	# Ввод возраста пользователя
	case "reg_user_set_age";
		$message = $bot_message->getMessage("reg_user_set_age");
		$button1 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить возраст пользователя
	case "reg_user_save_age";
		$message = $bot_message->getMessage("reg_user_save_age");
		$button1 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);		
		break;
	
	# Отправка фотографии профиля
	case "reg_user_set_photo";
		$message = $bot_message->getMessage("reg_user_set_photo");
		$button1 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить фото профиля.
	case "reg_user_save_photo";
		$message = $bot_message->getMessage("reg_user_save_photo");
		$button1 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;	
	
	# Выбор предпочтений пользователя
	case "reg_user_set_search";
		$message = $bot_message->getMessage("reg_user_set_search");
		$button1 = ["reg_user_save_search 1","\xf0\x9f\x92\x83 Девушек","positive","text"];
		$button2 = ["reg_user_save search 2","\xf0\x9f\x95\xba Парней","positive","text"];
		$button = [[$button1,$button2]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить предпочтения пользователя
	case "reg_user_save_search";
		$message = $bot_message->getMessage("reg_user_save_search");
		$button1 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;	
				
	# Отправка местоположения
	case "reg_user_set_location";
		$message = $bot_message->getMessage("reg_user_set_location");
		$button1 = ["reg_user_save_location","Местоположение","default","location"];
		$button2 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1],[$button2]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить местоположение
	case "reg_user_save_location";
		$message = $bot_message->getMessage("reg_user_save_location");
		$button1 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;	
	
	# Дополнительные пункты
	case "reg_user_option_menu";
		$message = $bot_message->getMessage("reg_user_option_menu");
		$button1 = ["reg_user_set_info","\xf0\x9f\x93\x8b Добавить информацию","primary","text"];
		$button2 = ["reg_user_set_voice","\xf0\x9f\x8e\x99 Добавить голосовое сообщение","primary","text"];
		$button3 = ["reg_user_set_instagram","\xf0\x9f\x8e\x87 Добавить Instagram","primary","text"];
		$button4 = ["achievement_1","\xe2\x9c\x96 Пропустить","secondary","text"];
		$button = [[$button1],[$button2],[$button3],[$button4]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Ввод информации о себе
	case "reg_user_set_info";
		$message = $bot_message->getMessage("reg_user_set_info");
		$button1 = ["reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить информацию о себе
	case "reg_user_save_info";
		$message = $bot_message->getMessage("reg_user_save_info");
		$button1 = ["reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;	
	
	# Добавить голосовое сообщение к профилю
	case "reg_user_set_voice";
		$message = $bot_message->getMessage("reg_user_set_voice");
		$button1 = ["reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить голосовое сообщение
	case "reg_user_save_voice";
		$message = $bot_message->getMessage("reg_user_save_voice");
		$button1 = ["reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;

	# Добавить ссылку на профиль Instagram
	case "reg_user_set_instagram";
		$message = $bot_message->getMessage("reg_user_set_instagram");
		$button1 = ["reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить ссылку на профиль Instagram
	case "reg_user_save_instagram";
		$message = $bot_message->getMessage("reg_user_save_instagram");
		$button1 = ["reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;	
	
	# Достижение "Первые шаги"
	case "achievement_1";
		$message = $bot_message->getMessage("achievement_1");
		$button1 = ["main_menu","\xe2\x9c\x94 Продолжить","positive","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Главное меню
	case "main_menu";
		$message = $bot_message->getMessage("main_menu");
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
}
	
	
	
	
	
	
	
	
	
	
	
	
	

