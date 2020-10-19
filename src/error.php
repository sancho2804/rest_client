<?php
namespace sancho2804\rest_client;

use Exception;

class error extends Exception{
    private $errors=null;
    private $lang='ru-ru';
    private $lang_path=null;

    public function __construct(string $code, string $message=null, string $lang='ru-ru'){
        if ($message){
            parent::__construct($message,(int)$code);
            return;
        }
        $this->lang=$lang;
        $this->lang_path=__DIR__."/lang/$this->lang/error.json";
        $this->get_texts();
        if (isset($this->errors[$code])) parent::__construct($this->errors[$code],(int)$code);
    }

    private function local_error(string $error, int $code, int $line){
        parent::__construct($error,$code);
        $this->file=__FILE__;
        $this->line=$line;
    }

    private function get_texts(){
        if (!file_exists($this->lang_path)){
            $this->local_error('Error lang file is not exist',1001,__LINE__);
            return;
        }
		$errors=file_get_contents($this->lang_path);
		if ($errors===false){
            $this->local_error('Cant read error lang file',1002,__LINE__);
            return;
        }
		if (!$errors){
            $this->local_error('Error lang file is empty',1003,__LINE__);
            return;
        }
		$errors=json_decode($errors,true);
		if (json_last_error()!=JSON_ERROR_NONE){
            $this->local_error('Error lang file have invalid JSON',1004,__LINE__);
            return;
        }
		$this->errors=$errors;
    }
}
