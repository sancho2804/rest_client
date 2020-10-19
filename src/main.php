<?php 
namespace sancho2804\rest_client;

use sancho2804\rest_client\error;

class main{
	private $allow_http_methods=['GET','POST','PUT','DELETE','PATCH','OPTIONS'];
	private $uri=null;
	private $auth=null;
	protected $last_request_info=null;
	private $rules=null;
	private $service=null;

	public function __construct(string $api_uri, string $login=null, string $token=null){
		if (!preg_match('/^https?:\/\/((www\.)|())[\w\d][\w\d\-.]{0,61}[\w\d](:[0-9]{1,5}|())\/.*?$/',$api_uri)) throw new Error(1);
		if ($api_uri[strlen($api_uri)-1]=='/') $api_uri=substr($api_uri,0,-1);
		$this->uri=$api_uri;

		if ($login!==null && $token!==null) $this->auth=[
			'login'=>$login,
			'token'=>$token,
		];
	}

	public function __get(string $name){
		switch ($name){
			case 'last_request_info': return $this->last_request_info;
			case 'service': return $this->service;
		}
	}

	public function __call(string $name, array $args){
		if (!$this->rules) throw new Error(7);
		if (!$args || count($args)<2) $args=array_merge_recursive($args,[null, true]);
		$parse_json=array_pop($args);
		$post_fields=array_pop($args);
		return $this->exec_map_method($name, $post_fields, $args, $parse_json);
	}

	public function set_service(string $service){
		$map_path=__DIR__."/maps/$service.json";
		if (!$service || !file_exists($map_path)) throw new Error(3);
		$rules=file_get_contents($map_path);
		if ($rules===false) throw new Error(4);
		if (!$rules) throw new Error(5);
		$rules=json_decode($rules,true);
		if (json_last_error()!=JSON_ERROR_NONE) throw new Error(6);
		$this->rules=$rules;
	}

	private function exec_map_method(string $alias, array $post_fields=null, array $args, bool $parse_json=true){
		if (!$this->rules) throw new Error(7);
		if (!isset($this->rules[$alias])) throw new Error(8);
		$item=$this->rules[$alias];
		for ($i=0; $i < count($args); $i++) {
			$args[$i]=urlencode($args[$i]);
			$item['uri']=substr_replace($item['uri'],$args[$i],strpos($item['uri'],'{::}'),4);
			if (!empty($item['require']) && in_array($i,$item['require'])) $item['require']=array_diff($item['require'],[$i]);
		}
		if (!empty($item['require'])) throw new Error(9);
		$item['uri']=str_replace('{::}','',$item['uri']);
		return $this->client_execute($item['method'],$item['uri'],$post_fields,$parse_json);
	}

	public function exec(string $alias, array $post_fields=null, $parse_json=true){
		if (!$this->rules) throw new Error(7);
		$args=func_get_args();
		array_splice($args,0,3);
		return $this->exec_map_method($alias,$post_fields,$args,$parse_json);
	}

	private function curl_request(string $http_method, string $rest_path, array $post_fields=null){
		$http_method=strtoupper($http_method);
		if (!in_array($http_method,$this->allow_http_methods) && false) throw new Error(2);
		if (strlen($rest_path)>1 && $rest_path[0]!='/') $rest_path='/'.$rest_path;
		$curl=curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->uri.$rest_path);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		if ($this->auth) {
			curl_setopt($curl, CURLOPT_USERPWD, $this->auth['login'].":".$this->auth['token']);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: '.$this->auth['login'].' '.$this->auth['token']));
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

	private function client_execute(string $http_method, string $rest_path, array $post_fields=null, bool $parse_json=true){
		$result=$this->curl_request($http_method,$rest_path,$post_fields);
		if ($parse_json) $result=json_decode($result,true);
		return $result;
	}

	public function get(string $rest_path, array $post_fields=null, bool $parse_json=true){
		return $this->client_execute('GET',$rest_path,$post_fields,$parse_json);
	}

	public function save(string $rest_path, array $post_fields=null, bool $parse_json=true){
		return $this->client_execute('POST',$rest_path,$post_fields,$parse_json);
	}

	public function delete(string $rest_path, array $post_fields=null, bool $parse_json=true){
		return $this->client_execute('DELETE',$rest_path,$post_fields,$parse_json);
	}

	public function update(string $rest_path, array $post_fields=null, bool $parse_json=true){
		return $this->client_execute('PATCH',$rest_path,$post_fields,$parse_json);
	}

	public function create(string $rest_path, array $post_fields=null, bool $parse_json=true){
		return $this->client_execute('PUT',$rest_path,$post_fields,$parse_json);
	}

	public function options(string $rest_path, array $post_fields=null, bool $parse_json=true){
		return $this->client_execute('OPTIONS',$rest_path,$post_fields,$parse_json);
	}
}
