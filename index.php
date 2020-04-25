<?php 
class rest_client{
    private $allow_methods=['GET','POST','PUT','DELETE'];
    private $uri=null;

    public function __construct(string $api_uri){
        if (!preg_match('/^https?:\/\/((www\.)|())[\w\d][\w\d-.]{0,61}[\w\d]\/.*?$/',$api_uri)) throw new Error("Не верно указан ресурс");
        if ($api_uri[strlen($api_uri)-1]=='/') $api_uri=substr($api_uri,0,-1);
        $this->uri=$api_uri;
        echo $this->uri;
    }

    private function curl_request(string $http_method, string $rest_path, array $post_fields=null){
        $http_method=strtoupper($http_method);
        if (!in_array($http_method,$this->allow_methods)) throw new Error("Недопустимый HTTP метод");
        if ($rest_path[0]!='/') $rest_path='/'.$rest_path;
        $curl=curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->uri.$rest_path);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, "anystring:".$this->api_key);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_UNRESTRICTED_AUTH, true);
        if ($http_method!='GET') curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
        if ($post_fields) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_fields));
        $return = curl_exec($curl);
        curl_close($curl);
        return json_decode($return,true);
    }
}

$rest=new rest_client('http://disk.yandex.ru/test');