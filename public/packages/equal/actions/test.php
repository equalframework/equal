<?php

use qinoa\http\HttpRequest;



$request = new HttpRequest("PUT http://localhost/test", ['Content-Type' => 'application/json'], '{"id":1, "content": "test"}');

$request->send();

