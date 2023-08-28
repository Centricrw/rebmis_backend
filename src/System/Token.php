<?php
namespace Src\System;
use Src\System\Session;

class Token {

    public static function generate($token_name,$user_id){
		return Session::put($token_name,self::randomString(32)."$".$user_id);
	}
	public static function checkToken($token,$token_name){
		if(Session::exists($token_name) && $token == Session::get($token_name)){
			if(self::tokenStartLifeTime() > self::getTokenExpire()) {
				self::destroyToken($token_name);
				return false;
			}
			return true;
		}
	}
	public static function destroyToken($token_name){
		return Session::delete($token_name);
	}
	public static function getToken($token_name){
		return Session::get($token_name);
	}
	public static function getTokenExpire(){
		return Session::get("token_expire");
	}
	public static function setTokenExpire() {
		$token_expire = time() + (48 * 60);
		return Session::put("token_expire",$token_expire);
	}

	public static function tokenStartLifeTime() {
		return $start_now = time();;
	}

	public static function randomString($length){
		$keys = array_merge(range(0,9), range('a', 'z'));
		$key = "";
		for ($i=0; $i < $length; $i++) { 
			$key .= $keys[mt_rand(0, count($keys) -1)];
		}
		return $key;
	}
}
?>