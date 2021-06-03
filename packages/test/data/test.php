<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\http\HttpRequest;

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
