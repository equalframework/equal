<?php 
use qinoa\http\HttpRequest;

list($params, $providers) = announce([
	"description"	=> "",
	"params"		=> [],
	"providers"		=> ['context']
]);





$data = file_get_contents('resID_links.txt');
$lines = preg_split('/\r\n|\r|\n/', $data);


foreach($lines as $line) {
	try {
		$url = trim($line);
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		 

		// $request = new HttpRequest("HEAD $url");	
		// $response = $request->send();	
		// $status = $response->statusCode();
	}
	catch(Exception $e) {
		$status = 400;
	}
	echo $status.';'.$url.PHP_EOL;
}
