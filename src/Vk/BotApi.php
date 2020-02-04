<?php

namespace Eslavon\Adiantum\Vk;


class BotApi extends VkRequest
{
   /**
     * Ключ доступа сообщества
     * @var string
     */	
    private $token;

   /**
     * Версия VK API
     * @var string
     */	
    private $version = "5.103";

   /**
     * Вложения для отправки сообщения
     *
     * @var string|null
     */ 
    private $attachment = null;

   /**
     * Местоположение
     *
     * @var array
     */ 
    private $location = null;

   /**
     * Настройки (Создавить ли сниппет для ссылки, Уведомления, Интенты)
     *
     * @var array
     */ 
    private $setting = null;

   /**
     * Клавиатура для ботов
     *
     * @var array
     */ 
    private $keyboard = null;

   /**
     * Конструктор класса
     *
     * @param string $token - Ключ доступа сообщества
     * @param string $version - Версия VK API
     *
     */
    public function __construct($token, $version = "5.103")
    {
        $this->token = $token;
        $this->version = $version;
    }

    private function sendRequest($method,$params)
    {
        $params["access_token"] = $this->token;
        $params["v"] = $this->version;
        try {
            return $this->request($method, $params);
        } catch (VkException $e) {
        }
    }

    /** 
    * Добавить вложение 
    *
    * @param string $add_attachment
    */
    public function addAttachment($add_attachment)
    {

        $this->attachment = ($this->attachment !== null) ? $this->attachment.",".$add_attachment : $add_attachment;
    }

    /**
    * Получить строку вложений
    *
    * @return string
    */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
    * Задать строку вложений
    *
    * @param string $value
    */
    public function setAttachment($value = null)
    {
        $this->attachment = $value;
    }

    /**
     * Получить георкодинаты
     *
     * @return array $array
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
    * Задать координаты
    *
    * @param string $lat - Широта
    * @param string $long - Долгота
    */
    public function setLocation($lat,$long)
    {
        $this->location = ["lat"=>$lat,"long" => $long];
    }

    /**
    * Задать настройки
    *
    * @param array $setting
    */
    public function setSetting($setting = null)
    {
        if ($setting !== null) {
            foreach ($setting as $key => $value) {
                $this->setting[$key] = $value;
            }
        }
    }

    /**
     * Получить настройки
     *
     * @return array $array
     */
    public function getSetting()
    {
        return $this->setting;
    }

   /**
     * Формируем клавиатуру из массива
     *
     * @param array $array - массив клавиатуры
     * @param mixed $setting - Скрывать ли клавиатуру после нажатия, включить ли режим inline
     *
     * @return string
     */
    private function formationKeyboard($array,$setting = null)
    {
        $one_time = false;
        $inline = false;
        if ($setting == null) {
            $one_time = false;
            $inline = false;
        } elseif ($setting == "inline") {
            $one_time = false;
            $inline = true;
        }

        $keyboard = [];
        $x = 0;
        foreach ($array as $buttons) {
            $y = 0;
            foreach ($buttons as $button) {
                $keyboard[$x][$y]["action"]["type"] = $button[3];

                if ($button[3] == "text") {
                    $keyboard[$x][$y]["action"]["label"] = $button[1];
                    $keyboard[$x][$y]["color"] = $button[2];
                } elseif ($button[3] == "vkpay") {
                    $keyboard[$x][$y]["action"]["hash"] = $button[4];
                } elseif ($button[3] == "open_app") {
                    $keyboard[$x][$y]["action"]["label"] = $button[1];
                    $keyboard[$x][$y]["action"]["app_id"] = $button[4];
                    $keyboard[$x][$y]["action"]["owner_id"] = $button[5];
                    $keyboard[$x][$y]["action"]["hash"] = $button[6];
                }
                
                $keyboard[$x][$y]["action"]["payload"] = '{"command": "'.$button[0].'"}';

                $y++;
            }
            $x++;
        }
            $array_keyboard = ["one_time" => $one_time,"buttons" => $keyboard,"inline"=> $inline];
        return json_encode($array_keyboard, JSON_UNESCAPED_UNICODE);
    }

    /**
    * Задать клавиатуру
    *
    * @param array $array - массив клавиатуры
    * @param mixed $setting - Скрывать ли клавиатуру после нажатия, включить ли режим inline
    *
    */
    public function setKeyboard($array,$setting = null)
    {
        $this->keyboard = $this->formationKeyboard($array,$setting);
    }


    public function getKeyboard()
    {
        return $this->keyboard;
    }

    /**
     * Генерация случайного числа
     *
     * @return int
     */
    private function random()
    {
        return mt_rand(-99999999,99999999);
    }

    /**
     * Отправка сообщения (Универсальный метод)
     *
     * @param array $array_params - массив параметров
     *
     * @return void
     * @throws VkException
     */
    public function messagesSend($array_params)
    {
        foreach ($array_params as $key => $value) {
            $params[$key] = $value;
        }
        $params["random_id"] = $this->random();
        $attachment = $this->getAttachment();
        if ($attachment !== null) {
            $params["attachment"] = $attachment;
            $this->attachment = null;
        }
        $location = $this->getLocation();
        if ($location !== null) {
            $params["lat"] = $location["lat"];
            $params["long"] = $location["long"];
            $this->location = null;
            $this->setAttachment();
        }

        if ($this->setting !== null) {
            foreach ($this->setting as $key => $value) {
                $params[$key] = $value;
            }
            $this->setting = null;
        }

        if ($this->keyboard !== null) {
            $params["keyboard"] = $this->keyboard;
            $this->keyboard = null;
        }

        $method = "messages.send";
        return $this->sendRequest($method,$params);
    }

    /**
     * Отправка текстового соообщения пользователю/в беседу
     *
     * @param int $peer_id - ID назначения
     * @param string $message - Текст сообщения
     *
     * @return array
     * @throws VkException
     */
    public function sendMessage($peer_id,$message)
    {
        $params = array(
            "peer_id" => $peer_id,
            "message" => $message
        );
        return $this->messagesSend($params);
    }

    /**
     * Получить адрес для загрузки изображений
     *
     * @param int $peer_id - ID назначения
     *
     * @return string
     * @throws VkException
     */
    public function imageUploadServer($peer_id)
    {
        $params = array(
            "peer_id" => $peer_id,
            "access_token" => $this->token,
            "v" => $this->version           
        );
        $method = "photos.getMessagesUploadServer";
        $result = $this->request($method,$params);
        return $result["response"]["upload_url"];
    }

    /**
     * Получить адрес для загрузки документов
     *
     * @param int $peer_id - ID назначения
     * @param string $type - тип документа. Возможные значения:doc — обычный документ; audio_message — голосовое сообщение.
     *
     * @return string
     * @throws VkException
     */
    public function docUploadServer($peer_id,$type = "doc")
    {
        $params = array(
            "type" => $type,
            "peer_id" => $peer_id,
            "access_token" => $this->token,
            "v" => $this->version             
        );
        $method = "docs.getMessagesUploadServer";
        $result = $this->request($method,$params);
        return $result["response"]["upload_url"];
    }

    /**
     * Сохранить загруженную фотографию
     *
     * @param int $peer_id - ID назначения
     *
     * @return string
     * @throws VkException
     */
    public function savePhotoServer($photo,$server,$hash)
    {
        $params = array(
            "photo" => $photo,
            "server" => $server,             
            "hash" => $hash,             
            "access_token" => $this->token,
            "v" => $this->version
        );

        $method = "photos.saveMessagesPhoto";
        $result = $this->request($method,$params);
        $id = $result["response"][0]["id"];
        $owner_id = $result["response"][0]["owner_id"];
        $access_key = $result["response"][0]["access_key"];
        $image = "photo".$owner_id."_".$id."_".$access_key;
        return $image;    
    }

    /**
     * Сохранить загруженную документ
     *
     * @param string $file - ID документа
     *
     * @return string
     * @throws VkException
     */
    public function saveDocServer($file,$type = "doc")
    {
        $params = array(
            "file" => $file,
            "access_token" => $this->token,
            "v" => $this->version
        );
        $method = "docs.save";
        $result = $this->request($method,$params);
        $key = ($type == "doc") ? "doc" : "audio_message";
        $id = $result["response"][$key]["id"];
        $owner_id = $result["response"][$key]["owner_id"];
        $doc = "doc".$owner_id."_".$id;
        return $doc;         
    }

    /**
     * Загрузить изображение на сервер и получить индетификатор изображения
     *
     * @param int $peer_id - ID назначения
     * @param string $file - Локальный путь до файла
     *
     * @return string
     * @throws VkException
     */
    public function uploadImage($peer_id,$file)
    {
        $upload_url = $this->imageUploadServer($peer_id);
        $image_array = $this->upload($peer_id,$file,$upload_url);
        $server = $image_array["server"];
        $photo = $image_array["photo"];
        $hash = $image_array["hash"];
        return $this->savePhotoServer($photo,$server,$hash);
    }

    /**
     * Загрузить документ на сервер и получить индетификатор документы для отправки в сообщении
     *
     * @param int $peer_id - ID назначения
     * @param string $file - Локальный путь до файла
     *
     * @return string
     * @throws VkException
     */
    public function uploadDoc($peer_id,$file,$type = "doc")
    {
        $upload_url = $this->docUploadServer($peer_id,$type);
        $doc_array = $this->upload($peer_id,$file,$upload_url);
        $file = $doc_array["file"];
        return $this->saveDocServer($file,$type);
    }
    
    public function getUserFirstName($peer_id)
    {
        $result = $this->getUser($peer_id);
        return $result[0]["first_name"];
    }

    public function getUser($peer_id,$fields = "sex",$name_case = "nom")
    {
        $params = array(
            "user_ids" => $peer_id,
            "fields" => $fields,
            "name_case" => $name_case,
            "access_token" => $this->token,
            "v" => $this->version
        );
        $method = "users.get";
        $result = $this->sendRequest($method,$params);
        return $result["response"];
    }


}

