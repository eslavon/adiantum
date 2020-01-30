<?php

namespace Eslavon\Adiantum\Control;


class BotMessage
{
    private const PATH = __DIR__."/../../res/message.json";

    private function getJson()
    {
        return file_get_contents(self::PATH);
    }

    public function getMessage($menu)
    {
        $data = json_decode($this->getJson());
		return $data->$menu;

    }
    public function getFormatMessage($menu,$search,$replace)
    {
        $message_src = $this->getMessage($menu);
        return str_replace($search,$replace,$message_src);
        
    }
}