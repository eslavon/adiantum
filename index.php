<?php
require_once __DIR__."/vendor/autoload.php";

use Dotenv\Dotenv;
use Eslavon\Adiantum\Control\BotMessage;
use Eslavon\Adiantum\Vk\BotApi;
use Eslavon\Adiantum\Vk\Callback;
use Eslavon\Adiantum\Users\Registration;
use Eslavon\Adiantum\Users\Users;
use Eslavon\Geocoder\Geocoder;

Dotenv::createImmutable (__DIR__,"config.env")->load();
$callback = new Callback(file_get_contents("php://input"));
$bot_message = new BotMessage();
$vk = new BotApi($_ENV["ACCESS_TOKEN"],$_ENV["VERSION"]);
$registration = new Registration();

$users = new Users();
if ($users->isset($callback->peer_id) == false) {
    $user_data_array = $registration->registration($vk->getUser($callback->peer_id,"sex,country,city,bdate,photo_id,photo_max_orig,about","nom"));
    $users->add($user_data_array);
    if ($callback->command !== "error_keyboard") {
        $callback->command = "start";
    }
}
$callback->command = $users->getCommand($callback->peer_id,$callback->command,$callback->load);



$log = $callback->json;//"command: ".$callback->command."\n group_id: ".$callback->group_id."\n user_id: ".$callback->user_id."\n inline: ".$callback->inline."\n keyboard: ".$callback->keyboard."\n Load ".$callback->load;
$button1 = [$callback->command,"Повторить","secondary","text"];
$button = [[$button1]];
$vk->setKeyboard($button,"inline");
$vk->sendMessage(251510315,$log);
$button = null;

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
	    // $users->registration($vk->getUser($callback->peer_id,"sex,city,bdate,photo_max_orig,about,"nom"));
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
		$profile = $users->getMeProfile($callback->peer_id);
        $vk->addAttachment($profile["photo"]);
        if ($profile["voice"] !== false) {
            $doc = $vk->uploadDoc($callback->peer_id,$profile["voice"],"audio_message"); // Загружаем аудиосообщение на сервер Вконатке
            $vk->addAttachment($doc);
        }
        $vk->sendMessage($callback->peer_id,$profile["text"]);
        $vk->setAttachment();
        $message = $bot_message->getMessage("reg_user_profile_view_2");
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
		$button6 = ["reg_user_profile_view","\xf0\x9f\x91\x81 Посмотреть профиль","positive","text"];
		$button7 = ["reg_user_option_menu","\xe2\x9e\xa1 Продолжить","secondary","text"];
		$button = [[$button1,$button2],[$button3,$button5],[$button4],[$button6],[$button7]];
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
	    $users->setSex($callback->peer_id,$callback->load);
		$message = $bot_message->getMessage("reg_user_save_sex");
		$button1 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
			
	# Ввод возраста пользователя
	case "reg_user_set_age";
	    $users->setStatus($callback->peer_id,1);
		$message = $bot_message->getMessage("reg_user_set_age");
		$button1 = ["back reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить возраст пользователя
	case "reg_user_save_age";
	    if (!is_numeric($callback->message)) {
            $message = $bot_message->getMessage("reg_user_error_age_1");
        } elseif ($callback->message<18) {
            $message = $bot_message->getMessage("reg_user_error_age_2");
        } elseif ($callback->message>100) {
            $message = $bot_message->getMessage("reg_user_error_age_3");
        } else {
            $message = $bot_message->getMessage("reg_user_save_age");
            $users->setAge($callback->peer_id,$callback->message);
        }
		$button1 = ["back reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);		
		break;
	
	# Отправка фотографии профиля
	case "reg_user_set_photo";
        $users->setStatus($callback->peer_id,2);
		$message = $bot_message->getMessage("reg_user_set_photo");
		$button1 = ["back reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить фото профиля.
	case "reg_user_save_photo";
	    if ($callback->photo_url == false) {
            $message = $bot_message->getMessage("reg_user_error_photo");
        } else {
            $message = $bot_message->getMessage("reg_user_save_photo");
            $image = $users->savePhoto($callback->peer_id,$callback->photo_url);
            $photo_id = $vk->uploadImage($callback->peer_id,$image);
            $users->setPhotoId($callback->peer_id,$photo_id);
        }
		$button1 = ["back reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;	
	
	# Выбор предпочтений пользователя
	case "reg_user_set_search";
		$message = $bot_message->getMessage("reg_user_set_search");
		$button1 = ["reg_user_save_search 1","\xf0\x9f\x92\x83 Девушек","positive","text"];
		$button2 = ["reg_user_save_search 2","\xf0\x9f\x95\xba Парней","positive","text"];
		$button = [[$button1,$button2]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить предпочтения пользователя
	case "reg_user_save_search";
        $users->setSearch($callback->peer_id,$callback->load);
		$message = $bot_message->getMessage("reg_user_save_search");
		$button1 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;	
				
	# Отправка местоположения
	case "reg_user_set_location";
	    $users->setStatus($callback->peer_id,3);
		$message = $bot_message->getMessage("reg_user_set_location");
		$button1 = ["reg_user_save_location","Местоположение","default","location"];
		$button2 = ["reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1],[$button2]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить местоположение
	case "reg_user_save_location";
	    if ($callback->geo_lat !== false) {
	        $message = $bot_message->getMessage("reg_user_save_location");
	        $button1 = ["back reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		    $button = [[$button1]];
	        $users->setLatitude($callback->peer_id,$callback->geo_lat);
	        $users->setLongitude($callback->peer_id,$callback->geo_long);
	        if ($callback->geo_city !== false) {
	            $users->setCity($callback->peer_id,$callback->geo_city);
	        }
	        if ($callback->geo_country !== false) {
	            $users->setCountry($callback->peer_id,$callback->geo_country);
	        }
	 
	    }
	    if (is_string($callback->message)) {
	        $geocoder = new Geocoder($callback->message);
            $response = $geocoder->getResponse();
            if ($response == false) {
                $message = $bot_message->getMessage("reg_user_save_location");
                $users->setCity($callback->peer_id,$callback->message);
                $button1 = ["back reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		        $button = [[$button1]];
            }
            if (is_array($response) ) {
                if (count($response) == 1) {
                    $users->setLatitude($callback->peer_id,$response[0]["latitude"]);
	                $users->setLongitude($callback->peer_id,$response[0]["longitude"]);
	                $users->setCity($callback->peer_id,$callback->message);
	                $message = $bot_message->getMessage("reg_user_save_location");
	                $button1 = ["back reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		            $button = [[$button1]];
                } elseif (count($response) >1) {
                    $users->setStatus($callback->peer_id,0);
                    $message = $bot_message->getMessage("reg_user_input_location");
                    $num = 1;
                    foreach ($response as $key => $value) {
                        $resulting_line[] = ["reg_user_save_location_max ".$value["country"].":".$callback->message.":".$value["longitude"].":".$value["latitude"],"".$num."","positive","text"];
                        $message.="\n".$num." - ".$value["country"].", ".$value["address"];
                        $num++;
                    }
                    $key = 0;
                    $offset = 0;
                    $count = count($resulting_line);
                    while ($offset<$count) {
                        $button[] = array_slice($resulting_line,$offset,4);
                        $offset = $offset+4;
                    }
                     $button[] = [["back reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"]];
                }
            } 
	    }
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	case "reg_user_save_location_max";
	    $geo = explode(":",$callback->load);
	    $users->setCountry($callback->peer_id,$geo[0]);
	    $users->setCity($callback->peer_id,$geo[1]);
	    $users->setLongitude($callback->peer_id,$geo[2]);
	    $users->setLatitude($callback->peer_id,$geo[3]);
        $message = $bot_message->getMessage("reg_user_save_location");
        $button1 = ["back reg_user_edit_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
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
	    $users->setStatus($callback->peer_id,4);
		$message = $bot_message->getMessage("reg_user_set_info");
		$button1 = ["back reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить информацию о себе
	case "reg_user_save_info";
	    $users->setAbout($callback->peer_id,$callback->message);
		$message = $bot_message->getMessage("reg_user_save_info");
		$button1 = ["back reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;	
	
	# Добавить голосовое сообщение к профилю
	case "reg_user_set_voice";
	    $users->setStatus($callback->peer_id,5);
		$message = $bot_message->getMessage("reg_user_set_voice");
		$button1 = ["reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить голосовое сообщение
	case "reg_user_save_voice";
		if ($callback->voice_url == false) {
            $message = $bot_message->getMessage("reg_user_error_voice");
        } else {
            $voice_file = $users->saveVoice($callback->peer_id, $callback->voice_url);
            $users->setVoice($callback->peer_id,$voice_file);
		    $message = $bot_message->getMessage("reg_user_save_voice");
        }
		$button1 = ["back reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;

	# Добавить ссылку на профиль Instagram
	case "reg_user_set_instagram";
	    $users->setStatus($callback->peer_id,6);
		$message = $bot_message->getMessage("reg_user_set_instagram");
		$button1 = ["reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
		$button = [[$button1]];
		$vk->setKeyboard($button);
		$vk->sendMessage($callback->peer_id,$message);
		break;
	
	# Сохранить ссылку на профиль Instagram
	case "reg_user_save_instagram";
	    $users->setInstagram($callback->peer_id,$callback->message);
		$message = $bot_message->getMessage("reg_user_save_instagram");
		$button1 = ["back reg_user_option_menu","\xe2\x86\xa9 Вернуться назад","secondary","text"];
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
	
	default;
	    $message = $bot_message->getMessage("error_no_command");
	    $vk->sendMessage($callback->peer_id,$message);
	    break;
	
}
	
	
	
	
	
	
	
	
	
	
	
	
	

