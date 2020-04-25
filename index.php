<?php 
class rest_client{
	private $allow_http_methods=['GET','POST','PUT','DELETE','PATCH','OPTIONS'];
	private $uri=null;
	private $auth=null;
	public $last_request_info=null;

	public function __construct(string $api_uri, string $login=null, string $pass=null){
		if (!preg_match('/^https?:\/\/((www\.)|())[\w\d][\w\d-.]{0,61}[\w\d]\/.*?$/',$api_uri)) throw new Error("Не верно указан ресурс");
		if ($api_uri[strlen($api_uri)-1]=='/') $api_uri=substr($api_uri,0,-1);
		$this->uri=$api_uri;
		echo $this->uri;

		if ($login!==null && $pass!==null) $this->auth=[
			'login'=>$login,
			'password'=>$pass,
		];
	}

	private function curl_request(string $http_method, string $rest_path, array $post_fields=null){
		$http_method=strtoupper($http_method);
		if (!in_array($http_method,$this->allow_http_methods)) throw new Error("Недопустимый HTTP метод");
		if (strlen($rest_path)>1 && $rest_path[0]!='/') $rest_path='/'.$rest_path;
		$curl=curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->uri.$rest_path);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		if ($this->auth) curl_setopt($curl, CURLOPT_USERPWD, $this->auth['login'].":".$this->auth['password']);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_UNRESTRICTED_AUTH, true);
		if ($http_method!='GET') curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
		if ($post_fields) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_fields));
		$return = curl_exec($curl);
		$this->last_request_info=curl_getinfo($curl);
		curl_close($curl);
		return $return;
	}

	public function get(string $rest_path, bool $parse_json=true){
		$result=$this->curl_request('GET',$rest_path);
		if ($parse_json) $result=json_decode($result,true);
		return $result;
	}

	public function save(string $rest_path, array $object, bool $parse_json=true){
		$result=$this->curl_request('POST',$rest_path,$object);
		if ($parse_json) $result=json_decode($result,true);
		return $result;
	}

	public function delete(string $rest_path, bool $parse_json=true){
		$result=$this->curl_request('DELETE',$rest_path);
		if ($parse_json) $result=json_decode($result,true);
		return $result;
	}

	public function update(string $rest_path, array $object, bool $parse_json=true){
		$result=$this->curl_request('PATCH',$rest_path,$object);
		if ($parse_json) $result=json_decode($result,true);
		return $result;
	}

	public function full_update(string $rest_path, array $object, bool $parse_json=true){
		$result=$this->curl_request('PUT',$rest_path,$object);
		if ($parse_json) $result=json_decode($result,true);
		return $result;
	}

	public function options(string $rest_path, bool $parse_json=true){
		$result=$this->curl_request('OPTIONS',$rest_path);
		if ($parse_json) $result=json_decode($result,true);
		return $result;
	}
	
}
