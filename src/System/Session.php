<?php 
namespace Src\System;

class Session{

    // Check if session is set
	public static function exists($name){
		return (isset($_SESSION[$name])) ? true : false;
    }
    
    // Function set sission
	public static function put($name,$value){
		return $_SESSION[$name] = $value;
    }
    
    // Function used to get session value
	public static function get($name){
		return isset($_SESSION[$name]) ? $_SESSION[$name] : null; 
    }
    
    // Unset session 
	public static function delete($name){
		if(self::exists($name)){
			unset($_SESSION[$name]);
		}
	}
	// Destroy all session
    public static function guturitsa(){
			return session_destroy();
	}
	
    // Change session value
	public static function flash($name, $string = ''){
		if(self::exists($name)){
			$session = self::get($name);
			self::delete($name);
			return $session;
		}else{
			self::put($name, $string);
		}
		return '';
    }
    
}
?>