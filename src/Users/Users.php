<?php


namespace Eslavon\Adiantum\Users;
use Eslavon\Adiantum\Db\Db;

class Users
{
    public function add($array)
    {
        Db::insert("users",$array);
    }

    public function isset($user_id)
    {
        return Db::isset("users","user_id",$user_id);
    }

    public function getProfile($user_id)
    {
        $result = Db::select("users","user_id",$user_id);
        $profile["text"] = $this->getProfileText($result);
        $profile["photo"] = $result["photo_id"];
        $profile["voice"] = $this->getVoice($result["voice_message"]);
        return $profile;
    }

    public function getMeProfile($user_id)
    {
        $result = Db::select("users","user_id",$user_id);
        $profile["text"] = $this->getMeProfileText($result);
        $profile["photo"] = $result["photo_id"];
        $profile["voice"] = $this->getVoice($result["voice_message"]);
        return $profile;
    }

    public function setStatus($user_id,$status)
    {
        Db::update("users","user_id",$user_id,"status",$status);
    }

    public function setSex($user_id,$sex)
    {
        Db::update("users","user_id",$user_id,"sex",$sex);
    }
    public function setSearch($user_id,$search)
    {
        Db::update("users","user_id",$user_id,"search",$search);
    }
    public function setAge($user_id,$age)
    {
        Db::update("users","user_id",$user_id,"age",$age);
    }

    public function setPhotoId($user_id,$photo_id)
    {
        Db::update("users","user_id",$user_id,"photo_id",$photo_id);
    }
    public function setLatitude($user_id,$lat)
    {
         Db::update("users","user_id",$user_id,"latitude",$lat);
    }
    public function setLongitude($user_id,$long)
    {
         Db::update("users","user_id",$user_id,"longitude",$long);
    }
    public function setCountry($user_id,$country)
    {
        Db::update("users","user_id",$user_id,"country",$country);
    }
    public function setCity($user_id,$city)
    {
        Db::update("users","user_id",$user_id,"city",$city);
    }
    public function setAbout($user_id,$about)
    {
        Db::update("users","user_id",$user_id,"about",$about);
    }    
    public function setInstagram($user_id,$instagram)
    {
        Db::update("users","user_id",$user_id,"instagram",$instagram);
    }        
    public function setVoice($user_id,$voice)
    {
        Db::update("users","user_id",$user_id,"voice_message",$voice);
    }          
    
    private function getProfileText($result)
    {
        $text = "\xf0\x9f\x91\xa4 ";
        $text.= $result["first_name"].", ";
        if ($result["age"] !== "false") {
            $text.= $result["age"];
        }
        if ($result["city"] !== "false") {
            $text.=", ".$result["city"];
        }
        $text.=$this->formateSearch($result["search"]);
        if ($result["about"] !== "false") {
            $text.="\n"."\xf0\x9f\x93\x8b О себе:  ".$result["about"];
        }
        if ($result["instagram"] !== "" ) {
            $text.="\n"."\xf0\x9f\x8e\x87 Instagram:  ".$result["instagram"];
        }
        return $text;
    }

    private function getMeProfileText($result)
    {
        $text = "\xf0\x9f\x91\xa4 Имя: ".$result["first_name"]."\n";
        $text.= ($result["age"] !== "false") ? "\xf0\x9f\x97\x93 Возраст: ".$result["age"]."\n" : "\xf0\x9f\x97\x93 Возраст: Не указан \n";
        $text.= ($result["city"] !== "false") ? "\xf0\x9f\x8f\x99 Город: ".$result["city"]."\n" : "\xf0\x9f\x8f\x99 Город: Не указан \n";
        $text.="\xf0\x9f\x91\xab Пол: ".$this->formateSex($result["sex"]);
        $text.=$this->formateSearch($result["search"])."\n";
        $text.= ($result["about"] !== "false") ? "\xf0\x9f\x93\x8b О себе: ".$result["about"]."\n" : "\xf0\x9f\x93\x8b О себе: Не заполнено \n";
        $text.= ($result["instagram"] !== "") ? "\xf0\x9f\x8e\x87 Instagram: ".$result["instagram"]."\n" : "\xf0\x9f\x8e\x87 Instagram: Не указан \n";
        return $text;
    }

    private function getVoice($voice)
    {
        if ($voice !=="") {
           return $voice;
        } else {
            return false;
        }
    }

    private function formateSearch($search)
    {
        switch ($search) {
            case 1;
                return "\n"."\xf0\x9f\x94\x8e Ищу: Девушку";
                break;
            case 2;
                return "\n"."\xf0\x9f\x94\x8e Ищу: Парней";
                break;
            default;
                return "\n"."\xf0\x9f\x94\x8e Ищу: Девушек и Парней";
                break;
        }
    }
    private function formateSex($sex)
    {
        switch ($sex) {
            case 1;
                return "Женский";
                break;
            case 2;
                return "Мужской";
                break;
            default;
                return "Не указан";
                break;
        }
    }

    public function getCommand($user_id, $command,$load)
    {
        if ($command == "back") {
            $this->setStatus($user_id,0);
            return $load;
        }
        $status_as_command = array (
            "1"=>"reg_user_save_age",
            "2" =>"reg_user_save_photo",
            "3" => "reg_user_save_location",
            "4" => "reg_user_save_info",
            "5" => "reg_user_save_voice",
            "6" => "reg_user_save_instagram"
        );
        $status = $this->getStatus($user_id);
        if (array_key_exists($status,$status_as_command))
        {
            return $status_as_command[$status];
        } else {
            return $command;
        }
    }

    public function getStatus($user_id)
    {
        $row = Db::select("users","user_id",$user_id,"status");
        return $row["status"];
    }

    public function savePhoto($peer_id, $photo_url)
    {
        $path = "res/photo_user/".$peer_id.".jpg";
        $fp = fopen($path, "w");
        $ch = curl_init(stripcslashes($photo_url));
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $data = curl_exec($ch);
        $curl_error_code = curl_errno($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        if ($curl_error || $curl_error_code) {
            //"CURL error:  " . $curl_error_code;
            if ($curl_error) {
                return ": {$curl_error}";
            }
        }
        return $path;
    }
    public function saveVoice($peer_id, $voice_url)
    {
        $path = "res/voice_user/".$peer_id.".ogg";
        $fp = fopen($path, "w");
        $ch = curl_init(stripcslashes($voice_url));
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $data = curl_exec($ch);
        $curl_error_code = curl_errno($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        if ($curl_error || $curl_error_code) {
            //"CURL error:  " . $curl_error_code;
            if ($curl_error) {
                return ": {$curl_error}";
            }
        }
        return $path;
    }

}