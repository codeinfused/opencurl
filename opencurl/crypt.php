<?php
class Crypt{
	
	public $key;
	
	public function __construct()
	{
		// CHANGE TO A RANDOMIZED SALT OF YOUR OWN
		$this->key = "a_cVmmKlORYpwDFqZA";
	}
	
	public function encrypt($string)
	{
		$iv = mcrypt_create_iv(
		    mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC),
		    MCRYPT_DEV_URANDOM
		);
				
		$encrypted = base64_encode(
		    $iv .
		    mcrypt_encrypt(
		        MCRYPT_RIJNDAEL_256,
		        hash('sha256', $this->key, true),
		        $string,
		        MCRYPT_MODE_CBC,
		        $iv
		    )
		);
		
		return $encrypted;
	}
	
	public function decrypt($encrypted)
	{		
		$data = base64_decode($encrypted);
		$iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));
				
		$decrypted = rtrim(
		    mcrypt_decrypt(
		        MCRYPT_RIJNDAEL_256,
		        hash('sha256', $this->key, true),
		        substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)),
		        MCRYPT_MODE_CBC,
		        $iv
		    ),
		    "\0"
		);
		
		return $decrypted;
	}
}
?>