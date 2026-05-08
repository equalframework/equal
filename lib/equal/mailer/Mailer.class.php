<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\mailer;

use equal\email\Email;

class Mailer {

    /**
     * Sends given email
     *
     * @param Email $email
     * @param array{
     *     from?: string,
     *     username?: string
     * } $options
     * @return int
     * @throws \Exception
     */
    public function send($email, $options = []): int {
        throw new \Exception("mailer_send_function_not_implemented.");
    }
}
