<?php 
class rest_client{
	private $allow_http_methods=['GET','POST','PUT','DELETE','PATCH','OPTIONS'];
	private $uri=null;
	private $auth=null;
	public $last_request_info=null;
	private $rules=null;

	public function __construct(string $api_uri, string $login=null, string $pass=null){
		if (!preg_match('/^https?:\/\/((www\.)|())[\w\d][\w\d-.]{0,61}[\w\d](:[0-9]{1,5}|())\/.*?$/',$api_uri)) throw new Error("Не верно указан ресурс");
		if ($api_uri[strlen($api_uri)-1]=='/') $api_uri=substr($api_uri,0,-1);
		$this->uri=$api_uri;

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
		if ($this->auth) {
			curl_setopt($curl, CURLOPT_USERPWD, $this->auth['login'].":".$this->auth['password']);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: '.$this->auth['login'].' '.$this->auth['password']));
		}
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_UNRESTRICTED_AUTH, true);
		if ($http_method!='GET') curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
		if ($post_fields) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_fields));
		$return = curl_exec($curl);
		$this->last_request_info=curl_getinfo($curl);
		curl_close($curl);
		return $return;
	}

	private function client_execute(string $http_method, string $rest_path, array $object=null, bool $parse_json=true){
		if (!in_array($http_method,$this->allow_http_methods)) throw new Error("Недопустимый HTTP метод");
		$result=$this->curl_request($http_method,$rest_path,$object);
		if ($parse_json) $result=json_decode($result,true);
		return $result;
	}

	public function get(string $rest_path, array $object=null, bool $parse_json=true){
		return $this->client_execute('GET',$rest_path,$object,$parse_json);
	}

	public function save(string $rest_path, array $object=null, bool $parse_json=true){
		return $this->client_execute('POST',$rest_path,$object,$parse_json);
	}

	public function delete(string $rest_path, array $object=null, bool $parse_json=true){
		return $this->client_execute('DELETE',$rest_path,$object,$parse_json);
	}

	public function update(string $rest_path, array $object=null, bool $parse_json=true){
		return $this->client_execute('PATCH',$rest_path,$object,$parse_json);
	}

	public function create(string $rest_path, array $object=null, bool $parse_json=true){
		return $this->client_execute('PUT',$rest_path,$object,$parse_json);
	}

	public function options(string $rest_path, array $object=null, bool $parse_json=true){
		return $this->client_execute('OPTIONS',$rest_path,$object,$parse_json);
	}

	public function eat_json(string $path){
		if (!$path || !file_exists($path)) throw new Error("Файл не найден");
		$rules=file_get_contents($path);
		if ($rules===false) throw new Error("Не удается считать файл");
		if (!$rules) throw new Error("Файл пуст");
		$rules=json_decode($rules,true);
		if (json_last_error()!=JSON_ERROR_NONE) throw new Error("Файл имеет невалидный JSON");
		$this->rules=$rules;
	}

	public function exec(string $alias, array $object=null, bool $parse_json=true){
		$item=$this->rules[$alias];
		if (!$item) throw new Error("Не найден запрашиваемый алиас");
		$args=func_get_args();
		for ($i=3; $i < count($args); $i++) {
			$args[$i]=urlencode($args[$i]);
			$item['uri']=substr_replace($item['uri'],$args[$i],strpos($item['uri'],'{::}'),4);
			if (!empty($item['require']) && in_array($i-3,$item['require'])) $item['require']=array_diff($item['require'],[$i-3]);
		}
		if (!empty($item['require'])) throw new Error("Необходимых параметров не задано: ".count($item['require']));
		$item['uri']=str_replace('{::}','',$item['uri']);
		return $this->client_execute($item['method'],$item['uri'],$object,$parse_json);
	}
	
}
