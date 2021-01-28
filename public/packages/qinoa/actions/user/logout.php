<?php
/**
*    This file is part of the easyObject project.
*    http://www.cedricfrancoys.be/easyobject
*
*    Copyright (C) 2012  Cedric Francoys
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Logs a user out.",
    'response'      => [
        'content-type'  => 'application/json'
    ],    
    'providers'     => ['context', 'auth', 'orm']
]);

list($context) = [ $providers['context'] ];

// client-side: delete session identification cookie and all cookies from current domain
$response = $context->httpResponse();
$response->getHeaders()->set('Cookie', '');
$response->body('')->status(204)->send();
// server-side: generate a new session
session_regenerate_id(true);