<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'	=>	"Validate a user subscription. This controller is meant to be requested through a link sent by email.",
    'params' 		=>	[
        'code' => [
            'description'   => 'Code for authenticating user.',
            'type'          => 'string',
            'required'      => true
        ],
        'redirect' => [
            'description'   => 'Relative URL to redirect user to, after successful authentication. A reset token is appended to it.',
            'type'          => 'string',
            'usage'         => 'url',
            'default'       => 'auth/#/reset'
        ]
    ],
    'constants'     => ['BACKEND_URL', 'AUTH_SECRET_KEY'],
    'access'        => [
        'visibility'        => 'public'
    ],
    'response'      => [
        'content-type'      => 'text/html',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);

// initialize local vars with inputs
['orm' => $orm, 'context' => $context, 'auth' => $auth] = $providers;

$send_invalid_link_response = static function() use ($context) {
    $html = <<<'HTML'
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lien invalide</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            display: grid;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            place-items: center;
            color: #263238;
            background: #f5f7f8;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        main {
            width: min(100%, 560px);
            padding: 40px;
            border: 1px solid #e3e7e9;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 28px rgba(38, 50, 56, .08);
        }
        h1 {
            margin: 0 0 16px;
            font-size: clamp(1.4rem, 4vw, 1.8rem);
            line-height: 1.25;
        }
        p {
            margin: 0;
            margin-bottom: 10px;
            color: #59656b;
            font-size: 1rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <main>
        <h1>Ce lien est invalide ou a déjà été utilisé</h1>
        <p>
            Si vous avez déjà défini un mot de passe, vous pouvez l'utiliser pour <a href="/auth/#/signin">vous identifier</a>.
        </p>
        <p>Dans le cas contraire, supprimez le message email correspondant et demandez à votre syndic de vous en envoyer un nouveau.</p>
    </main>
</body>
</html>
HTML;

    $context->httpResponse()
            ->status(400)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->body($html)
            ->send();
    exit();
};

$credentials = base64_decode($params['code'], true);

if($credentials === false || strpos($credentials, ':') === false) {
    $send_invalid_link_response();
}

[$login, $password] = explode(':', $credentials, 2);

if(!strlen($login) || !strlen($password)) {
    $send_invalid_link_response();
}

$auth->su();

// received password is expected to be encrypted the same way it is stored
$ids = $orm->search('core\User', [['login', '=', $login]]);

if(!count($ids)) {
    $send_invalid_link_response();
}

$list = $orm->read(User::getType(), $ids, ['id', 'login', 'password']);
$user = reset($list);

if(!is_array($user) || !isset($user['password']) || !password_verify($password, $user['password'])) {
    $send_invalid_link_response();
}

// mark user as validated (will update status according to USER_ACCOUNT_VALIDATION)
$orm->update(User::getType(), $user['id'], ['validated' => true]);

$response = $context->httpResponse();

if(strlen($params['redirect'])) {
    // Generate a reset token valid for 15 minutes, same as password recovery links.
    $token = $auth->token($user['id'], 60 * 15);
    $url = rtrim(constant('BACKEND_URL'), '/') . '/' . trim($params['redirect'], '/') . '/' . $token;
    header('Location: ' . $url);
    exit();
}

$context->httpResponse()
        ->status(204)
        ->send();
