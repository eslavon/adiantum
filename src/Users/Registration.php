<?php

namespace Eslavon\Adiantum\Users;

use DateTime;
use Eslavon\Geocoder\Geocoder;

class Registration
{

    public function registration($data)
    {
        return $this->getUserData($data);
    }

    private function getUserData($data)
    {
        return array(
            "user_id" => $data[0]["id"], //int default
            "first_name" => $data[0]["first_name"], //string default
            "last_name" => $data[0]["last_name"], // string default;
            "photo_id" => $data[0]["photo_id"],// string default;
            "sex" => $data[0]["sex"], // int default;
            "search" => $this->getSearch($data), // int option
            "bdate" => $this->getBirthDate($data),//string option
            "age" => $this->getAge($data),//int option
            "city" => $this->getCity($data),//string option
            "country" => $this->getCountry($data),
            "about" => $this->getAbout($data),//string option,
            "latitude" => $this->getLatitude($data),
            "longitude" => $this->getLongitude($data)
        );
	}

    private function getSearch($data)
    {
        switch ($data[0]["sex"]) {
            case 1;
                $search = 2;
                break;
            case 2;
                $search = 1;
                break;
            default;
                $search = 0;
                break;
        }
        return $search;
    }

    private function getBirthDate($data)
    {
        if (isset($data[0]["bdate"])) {
            return $data[0]["bdate"];
        } else {
            return "false";
		}
    }

    private function getAge($data)
    {
        if ($this->getBirthDate($data) == "false") {
            return "false";
        }

        if (mb_strlen($this->getBirthDate($data)) >8) {
            $datetime = new DateTime($this->getBirthDate($data));
            $interval = $datetime->diff(new DateTime(date("Y-m-d")));
            return $interval->format("%Y");
        } else {
            return "false";
        }
    }

    private function getAbout($data)
    {
        if ($data[0]["about"] == "") {
            return "false";
        } else {
            return $data[0]["about"];
        }
    }

    private function getCity($data)
    {
        if (isset($data[0]["city"])) {
            return $data[0]["city"]["title"];
        } else {
            return "false";
        }
    }

    private function getCountry($data)
    {
        if (isset($data[0]["country"])) {
            return $data[0]["country"]["title"];
        } else {
            return "false";
        }
    }

    private function getGeo($data)
    {
        if ($this->getCountry($data) == "false" or $this->getCity($data) == "false") {
            return false;
        } else {
            $address = $this->getCountry($data).",".$this->getCity($data);
            $geocoder = new Geocoder($address);
            $response = $geocoder->getResponse();
            if ($response !== false and count($response) == 1) {
                return array("longitude" => $response[0]["longitude"],"latitude" => $response[0]["latitude"]);
            } else {
                return false;
            }
        }

    }

    private function getLongitude($data)
    {
        if ($this->getGeo($data) == false) {
            return "false";
        } else {
            $array = $this->getGeo($data);
            return $array["longitude"];
        }
    }
    private function getLatitude($data)
    {
        if ($this->getGeo($data) == false) {
            return "false";
        } else {
            $array = $this->getGeo($data);
            return $array["latitude"];
        }
    }
}


