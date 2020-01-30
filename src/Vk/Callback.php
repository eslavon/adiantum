<?php

namespace Eslavon\Adiantum\Vk;

class Callback
{
    public $json;

    public $group_id;

    public $user_id;

    public $peer_id;

    public $message;

    public $command;

    public $load;

    public $client;
    
    public $inline;
    
    public $keyboard = false;


    public function __construct($json = false)
    {
        if ($json == false) {
            exit(200);
        }
        $this->json = $json;
        $data = json_decode($json);
        switch ($data->type) {
            case "confirmation";
                $this->command = "confirmation";
                $this->group_id = $data->group_id;
                break;
            case "message_new";
                $this->group_id = $data->group_id;
                $this->user_id = $data->object->message->from_id;
                $this->peer_id = $data->object->message->peer_id;
                $this->message = stripcslashes($data->object->message->text);

                if (isset($data->object->message->payload)) {
                    $payload = json_decode($data->object->message->payload, true);
                    $this->command = $this->clearCommand($payload["command"]);
                } else {
                    $this->command = $this->clearCommand($this->message);
                }
                $this->client = $data->object->client_info;
                if (isset($data->object->client_info->inline_keyboard)) {
                        $this->inline = true;
                }
                if (isset($data->object->client_info->keyboard)) {
                        $this->keyboard = true;
                } else {
                    $this->command = "error_keyboard";
                }
                break;
        }
    }

    private function clearCommand($command)
    {
        /**
        $regex_emoticons = "/[\x{1F600}-\x{1F64F}]/u";
        $command = preg_replace($regex_emoticons, "", $command);
        $regex_symbols = "/[\x{1F300}-\x{1F5FF}]/u";
        $command = preg_replace($regex_symbols, "", $command);
        $regex_transport = "/[\x{1F680}-\x{1F6FF}]/u";
        $command = preg_replace($regex_transport, "", $command);
        $regex_misc = "/[\x{2600}-\x{26FF}]/u";
        $command = preg_replace($regex_misc, "", $command);
        $regex_dingbats = "/[\x{2700}-\x{27BF}]/u";
        $command = preg_replace($regex_dingbats, "", $command);
        $command = preg_replace('/[^\p{L}0-9 \!]/iu', "", $command);
        */
        $command = trim($command);
        $command = mb_strtolower($command);
        $array_command = explode(" ", $command);
        $cmd = array_shift($array_command);
        $this->load = implode(" ", $array_command);
        return $cmd;
    }
}
	