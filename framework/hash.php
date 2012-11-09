<?php
	//
	// Password hashing functions

	class Hash
	{
		static function create($password) 
		{
		    $salt = substr(str_replace('+', '.', base64_encode(sha1(microtime(true), true))), 0, 22);
		    return crypt($password, '$2a$12$' . $salt);
		}

		static function verify($password, $hash)
		{
			return(crypt($password, $hash) == $hash);
		}	
	}

?>