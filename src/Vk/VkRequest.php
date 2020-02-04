<?php

namespace Eslavon\Adiantum\Vk;

use CURLFile;

/**
 * VkRequest - отправка запросов к VK_API
 */
class VkRequest extends VkException
{
    public function request($method,$params)
    {
		$url = "https://api.vk.com/method/".$method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $json = curl_exec($ch);
 		if ($json === false) {
			$e = curl_error($ch);
        	throw new VkException($e);
        }
        curl_close($ch);
 		return $this->jsonValidate($json);

	}
	
	private function jsonValidate($json)
	{
        $data = json_decode($json,true);
        if (json_last_error() !== JSON_ERROR_NONE) {
        	$e = json_last_error_msg();
        	throw new VkException($e);
        }
        if (isset($data["error"])) {
        	$e = $data["error"]["error_msg"];
        	throw new VkException($e);
        }
		return $data;
	}


    public function upload($peer_id,$file,$upload_url)
    {
        $post = [ "file" => new CURLFile($file) ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $upload_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $json = curl_exec($ch);
        if ($json === false) {
            $e = curl_error($ch);
            throw new VkException($e);
        }       
        curl_close($ch); 
		return $this->jsonValidate($json);
    }
}

