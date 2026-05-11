<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\mailer;

use equal\email\Email;
use equal\http\HttpRequest;

/*
* Examples 


SendGrid

$config = [
    'api_url' => 'https://api.sendgrid.com/v3/mail/send',

    'auth' => [
        'type' => 'bearer'
    ],

    'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ],

    'success' => [
        'status_codes' => [202]
    ],

    'body' => [
        'personalizations' => [
            [
                'to' => [
                    [
                        'email' => '@to'
                    ]
                ],
                '?cc' => '@cc:email_list',
                '?bcc' => '@bcc:email_list'
            ]
        ],
        'from.email' => '@from',
        'subject' => '@subject',
        'content' => [
            [
                'type' => 'text/html',
                'value' => '@body'
            ]
        ],
        '?reply_to.email' => '@reply_to'
    ]
];

Brevo

([
    'api_url' => 'https://api.brevo.com/v3/smtp/email',
    'api_key' => $api_key,
    'from' => 'noreply@example.com',

    'auth' => [
        'type' => 'header',
        'name' => 'api-key'
    ],

    'success' => [
        'status_codes' => [201],
        'count_path' => 'messageIds'
    ],

    'body' => [
        'sender.email' => '@from',
        'subject' => '@subject',
        'to' => [
            [
                'email' => '@to'
            ]
        ],
        'htmlContent' => '@body',
        '?replyTo.email' => '@reply_to',
        '?cc' => '@cc:email_list',
        '?bcc' => '@bcc:email_list'
    ]
]

*/

/**
 * Config structure
 *
 * ```
 * [
 *   'api_url' => '',
 *    'api_key' => '',
 *    'from' => '',
 *
 *    'auth' => [
 *        'type' => 'header|bearer|basic|none',
 *        'name' => null,
 *        'username' => null
 *    ],
 *
 *    'headers' => [],
 *
 *   'success' => [
 *       'status_codes' => [200, 201, 202],
 *       'count_path' => null
 *    ],
 *
 *    'body' => [
 *       'sender.email' => '@from',
 *       'subject' => '@subject',
 *       'to' => [
 *                [
 *                    'email' => '@to'
 *                ]
 *       ],
 *       'htmlContent' => '@body',
 *       '?replyTo.email' => '@reply_to',
 *       '?cc' => '@cc:email_list',
 *       '?bcc' => '@bcc:email_list'
 *    ]
 * ]
 * ```
 */
class MailerApi extends Mailer {

    private array $config = [];

    private array $defaults = [
        'method' => 'POST',

        'api_url' => null,

        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ],

        'auth' => [
            'type' => 'header',
            'name' => 'api-key'
        ],

        'success' => [
            'status_codes' => [200, 201, 202],
            'count_path' => null
        ],

        'body' => [
            'sender.email' => '@from',
            'subject' => '@subject',
            'to' => [
                [
                    'email' => '@to'
                ]
            ],
            'htmlContent' => '@body',
        ]
    ];

    public function __construct(array $config = []) {
        $this->config = $this->mergeConfig($this->defaults, $config);
    }

    /**
     * @param Email $email
     * @param array{
     *     api_key?: string,
     *     from?: string,
     *     from_name?: string
     * } $options
     * @return int
     * @throws \Exception
     */
    public function send(Email $email, $options = []): int {
        $this->validateConfig();

        $method = $this->config['method'];
        $api_url = $this->config['api_url'];

        $request = new HttpRequest("{$method} {$api_url}");

        foreach($this->config['headers'] as $name => $value) {
            $request->header($name, $value);
        }

        $this->applyAuthentication($request, $options);

        $context = $this->buildContext($email, $options);
        $body = $this->resolveBody($this->config['body'], $context);

        $response = $request
            ->setBody($body)
            ->send();

        $status = $response->getStatusCode();
        $data = $response->body();

        if(!in_array($status, $this->config['success']['status_codes'], true)) {
            return 0;
        }

        if(!is_array($data)) {
            return 1;
        }

        return $this->countAcceptedMessages($data);
    }

    private function validateConfig(): void {
        if(empty($this->config['api_url'])) {
            throw new \Exception('missing_api_url');
        }

        if(empty($this->config['body'])) {
            throw new \Exception('missing_body_mapping');
        }
    }

    private function applyAuthentication(HttpRequest $request, array $options): void {
        $api_key = $options['api_key'] ?? $this->config['api_key'] ?? null;

        if(empty($api_key)) {
            throw new \Exception('missing_api_key');
        }

        $auth = $this->config['auth'];

        switch($auth['type']) {
            case 'header':
                if(empty($auth['name'])) {
                    throw new \Exception('missing_auth_header');
                }
                $request->header($auth['name'], $api_key);
                break;

            case 'bearer':
                $request->header('Authorization', "Bearer {$api_key}");
                break;

            case 'basic':
                $username = $auth['username'] ?? 'api';
                $token = base64_encode("{$username}:{$api_key}");
                $request->header('Authorization', "Basic {$token}");
                break;

            default:
                throw new \Exception('unsupported_auth_type');
        }
    }

    private function buildContext(Email $email, array $options): array {
        $from = $options['from'] ?? $this->config['from'] ?? null;

        if(empty($from)) {
            throw new \Exception('missing_from');
        }

        return [
            'from' => $from,
            'from_name' => $options['from_name'] ?? $this->config['from_name'] ?? null,
            'to' => $email->to,
            'subject' => $email->subject,
            'body' => $email->body,
            'reply_to' => $email->reply_to ?? null,
            'cc' => $email->cc ?? [],
            'bcc' => $email->bcc ?? []
        ];
    }

    private function resolveBody(array $mapping, array $context): array {
        $body = [];

        foreach($mapping as $key => $value) {
            $optional = false;
            $target_path = $key;

            if(is_string($key) && substr($key, 0, 1) === '?') {
                $optional = true;
                $target_path = substr($key, 1);
            }

            $resolved = $this->resolveValue($value, $context);

            if($optional && $this->isEmptyValue($resolved)) {
                continue;
            }

            if(is_string($target_path) && strpos($target_path, '.') !== false) {
                $this->setPath($body, $target_path, $resolved);
            }
            else {
                $body[$target_path] = $resolved;
            }
        }

        return $body;
    }

    private function resolveValue(mixed $value, array $context): mixed {
        if(is_array($value)) {
            $resolved = [];

            foreach($value as $key => $item) {
                if(is_string($key) && substr($key, 0, 1) === '?') {
                    $resolved_key = substr($key, 1);
                    $resolved_value = $this->resolveValue($item, $context);

                    if($this->isEmptyValue($resolved_value)) {
                        continue;
                    }

                    $resolved[$resolved_key] = $resolved_value;
                    continue;
                }

                $resolved[$key] = $this->resolveValue($item, $context);
            }

            return $resolved;
        }

        if(!is_string($value) || substr($value, 0, 1) !== '@') {
            return $value;
        }

        $expression = substr($value, 1);
        [$field, $filter] = array_pad(explode(':', $expression, 2), 2, null);

        $resolved = $context[$field] ?? null;

        if($filter) {
            $resolved = $this->applyFilter($filter, $resolved);
        }

        return $resolved;
    }

    private function applyFilter(string $filter, mixed $value): mixed {
        switch($filter) {
            case 'email_list':
                if(empty($value)) {
                    return [];
                }

                if(is_string($value)) {
                    $value = [$value];
                }

                return array_map(
                    fn($email_address) => ['email' => $email_address],
                    $value
                );

            case 'email_csv':
                if(empty($value)) {
                    return '';
                }

                if(is_string($value)) {
                    return $value;
                }

                return implode(',', $value);

            default:
                throw new \Exception("unsupported_filter: {$filter}");
        }
    }

    private function setPath(array &$target, string $path, mixed $value): void {
        $parts = explode('.', $path);
        $cursor = &$target;

        foreach($parts as $part) {
            if(!isset($cursor[$part]) || !is_array($cursor[$part])) {
                $cursor[$part] = [];
            }

            $cursor = &$cursor[$part];
        }

        $cursor = $value;
    }

    private function countAcceptedMessages(array $data): int {
        $path = $this->config['success']['count_path'] ?? null;

        if(!$path) {
            return 1;
        }

        $value = $this->getPath($data, $path);

        if(is_array($value)) {
            return count($value);
        }

        if(!empty($value)) {
            return 1;
        }

        return 1;
    }

    private function getPath(array $data, string $path): mixed {
        $cursor = $data;

        foreach(explode('.', $path) as $part) {
            if(!is_array($cursor) || !array_key_exists($part, $cursor)) {
                return null;
            }

            $cursor = $cursor[$part];
        }

        return $cursor;
    }

    private function isEmptyValue(mixed $value): bool {
        return $value === null || $value === '' || $value === [];
    }

    private function mergeConfig(array $base, array $override): array {
        foreach($override as $key => $value) {
            if(
                is_array($value)
                && isset($base[$key])
                && is_array($base[$key])
            ) {
                $base[$key] = $this->mergeConfig($base[$key], $value);
            }
            else {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}
