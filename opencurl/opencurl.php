<?php
/*
openCURL
@version v0.1
@author Mike Smotherman (@codeinfused)
@github https://github.com/codeinfused/opencurl

TO USE PASSWORD ENCRYPTION
edit line 9 of crypt.php and create your own salt key

----------------------------
EXAMPLE:

$curl = new Curl(array(
	"cookieFile" => "codeinfused",				// optional, defaults to md5 hash
	"defaultRefer" => "http://www.google.com"	// optional
));

$curl->post(array(
	"url" => "...",
	"data" => array(),  	// optional
	"hasFile" => false,  	// optional
	"showHeaders" => true, 	// optional
	"fresh" => false,  		// optional
	"autofollow" => true,	// optional
	"headers" => array()	// optional
));
	
echo $curl->newurl;
echo $curl->html;
*/

class Curl{

	public $html;
	public $cookie;
	public $lasturl;
	public $newurl;
	public $headers = array();
	
	public function __construct($init)
	{
		$this->lasturl = $this->thisor( $init['defaultRefer'], "http://www.google.com" );
		$this->createCookie($init['cookieFile']);
	}
	
	public function createCookie($title)
	{
		$cfile = empty($title) ? md5(time()) : $title;
		$this->cookie = dirname(__FILE__)."/cookies/".$cfile.".txt";
		$this->checkLocalCookie();
	}
	
	public function checkLocalCookie()
	{
		if(!file_exists($this->cookie)){
			$this->createCookieFile();
		}
	}
	
	public function createCookieFile()
	{
		$handle = fopen($this->cookie, 'w');
		fclose($handle);
	}
	
	public function endCleanup()
	{
		$this->html = "";
		$this->headers = "";
	}
	
	public function thisor($val, $def){
		if(!isset($val)){
			return $def;
		}else{
			return $val;
		}
	}


	/*
		turn post data into CURL strings
	*/
	public function createPostData($arr)
	{
		$post_string = "";
		foreach($arr as $key=>$value) { 
			if(is_array($value)){
				for($i=0; $i<count($value); $i++){
					$post_string .= $key.'[]='.urlencode($value[$i]).'&';
				}
			}else{
				$post_string .= $key.'='.urlencode($value).'&'; 
			}
		}
		rtrim($post_string, '&');
		return $post_string;
	}
	
	
	/*
		read curl headers into array
	*/
	function readHead($ch, $str){
		$len = strlen($str);
		$this->headers[] = $str;  // not in obj context
		return $len;
	}
	
	
	public function echoHeaders($title)
	{
		$msg = "<h3>$title HEADERS</h3>";
		$msg .= "<pre>";
		$msg .= print_r($this->headers, true);
		$msg .= "</pre>";
		return $msg;
	}
	
	
	/*
		curl post
	*/
	public function post($settings)
	{
		$url = $settings['url'];
		$data = $this->thisor( $settings['data'], array() );
		$refer = $this->thisor( $settings['referer'], $this->lasturl );
		$hasfile = $this->thisor( $settings['hasFile'], false );
		$usehead = $this->thisor( $settings['useHeaders'], true );
		$fresh = $this->thisor( $settings['fresh'], false );
		$headers = $this->thisor( $settings['headers'], false );
		$autofollow = $this->thisor( $settings['autofollow'], true );
		
		if(empty($url)){
			return "missing url";
		}
		
		$post_string = $this->createPostData($data);
		$this->lasturl = $url;
		$this->headers = array();
		
		$curl = curl_init();
		$agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.149 Safari/537.36";

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, $fresh);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $autofollow);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
		curl_setopt($curl, CURLOPT_HEADER, $usehead);
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this, 'readHead'));
		if($headers!==false){
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($curl, CURLOPT_POST, count($data));
		curl_setopt($curl, CURLOPT_REFERER, $refer);
		if($hasfile===true){
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data;"));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}else{
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_string);
		}
		curl_setopt($curl, CURLOPT_TIMEOUT, 500 );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($curl, CURLOPT_USERAGENT, $agent);
		
		$resp = curl_exec($curl);
		
		$err = curl_error($curl);
		if(!empty($err)){
			echo "CURL ERROR: ".$err;
		}
		
		$this->newurl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);		
		$this->html = $resp;
	}
	
	
	public function get($settings)
	{
		$url = $settings['url'];
		$data = $this->thisor( $settings['data'], array() );
		$refer = $this->thisor( $settings['referer'], $this->lasturl );
		$usehead = $this->thisor( $settings['showHeaders'], true );
		$fresh = $this->thisor( $settings['fresh'], false );
		$heads = $this->thisor( $settings['headers'], false );
		$autofollow = $this->thisor( $settings['autofollow'], true );
		
		$get_string = $this->createPostData($data);
		$this->lasturl = $url;
		$this->headers = array();
		if($get_string){
			$url = $url."?".$get_string;
		}
				
		$curl = curl_init();
		$agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.149 Safari/537.36";
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, $fresh);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $autofollow);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_HEADER, $usehead);
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this, 'readHead'));
		if($heads!==false){
			curl_setopt($curl, CURLOPT_HTTPHEADER, $heads);
		}
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($curl, CURLOPT_REFERER, $refer);
		curl_setopt($curl, CURLOPT_TIMEOUT, 500 );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($curl, CURLOPT_USERAGENT, $agent);
		$resp = curl_exec($curl);
		
		$err = curl_error($curl);
		if(!empty($err)){
			echo "CURL ERROR: ".$err;
		}
		
		$this->newurl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);		
		$this->html = $resp;
	}
	
}

?>