<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\email;


class EmailAttachment {

    /**
     * Full filename of the attachment (with extension).
     * @var string
     */
    public $name;

    /**
     * Raw binary data of the attachment.
     * @var string
     */
    public $data;

    /**
     * Content Type of data (MIME type).
     * @var string
     */
    public $content_type;


    public function __construct(string $name, string $data, string $content_type) {
        $this->name = $name;
        $this->data = $data;
        $this->content_type = $content_type;
    }
}