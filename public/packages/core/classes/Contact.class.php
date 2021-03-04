<?php
namespace core;


class Contact extends \qinoa\orm\Model {
    public static function getColumns() {
        return [
			'firstname' => [
				'type' => 'string',
			],
			'lastname' => [
				'type' => 'string',
			],
			'email' => [
				'type' => 'string',
			],
			'mobile' => [
				'type' => 'string',
			],
			'landline' => [
				'type' => 'string',
			]
		];
	}
	
    public static function getConstraints() {
        return [
            'email'			=>  [
                                'error_id'          => 'invalid_email',
                                'error_message'     => 'Email must be a valid email address.',
                                'function'          => function ($login) {
                                        // valid email address
                                        if($login == 'admin') return true;
                                        return (bool) (preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,13})$/', $login, $matches));
                                    }
                                ],
            'mobile'		=>  [
                                'error_id'          => 'invalid_mobile',
                                'error_message'     => 'Mobile must be a phone number.',
                                'function'          => function ($phone) {
										return (bool) (preg_match('/^\+?[0-9]{4,14}$/', $phone, $matches));
									}
                                ],
            'landline'		=>  [
                                'error_id'          => 'invalid_landline',
                                'error_message'     => 'Landline must be a phone number.',
                                'function'          => function ($phone) {
                                        return (bool) (preg_match('/^\+?[0-9]{4,14}$/', $phone, $matches));
                                    }
                                ]
								
        ];
    }	
}
