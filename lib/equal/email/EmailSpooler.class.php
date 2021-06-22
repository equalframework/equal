<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\email;

if(! @include_once(QN_BASEDIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php')) {
    throw new \Exception("missing_dependency", QN_ERROR_INVALID_CONFIG);
}
use \Swift_SmtpTransport as Swift_SmtpTransport;
use \Swift_Message as Swift_Message;
use \Swift_Mailer as Swift_Mailer;

use equal\organic\Service;
use equal\services\Container;

class EmailSpooler extends Service {

    const MESSAGE_FOLDER = QN_BASEDIR.'/spool';

    private $spool;

    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     */
    protected function __construct(Container $container) {
        $this->spool = [];
    }

    public static function constants() {
        return ['QN_BASEDIR', 'EMAIL_SMTP_HOST', 'EMAIL_SMTP_PORT', 'EMAIL_SMTP_ACCOUNT_USERNAME', 'EMAIL_SMTP_ACCOUNT_PASSWORD', 'EMAIL_SMTP_ACCOUNT_EMAIL', 'EMAIL_SMTP_ACCOUNT_DISPLAYNAME'];
    }

    /** 
     * Load the spool by reading all files currently in `$messages_folder` directory
    */
    private function load() {
        $files = scandir(self::MESSAGE_FOLDER);
        foreach($files as $file) {
            if(in_array($file, ['.', '..', '.gitkeep'])) continue;
            
            // extract message details
            $filename = self::MESSAGE_FOLDER.'/'.$file;
            $data = file_get_contents($filename);
            $message = unserialize($data);
        
            // spool is mapped by email address 
            $email = $message['email'];
            if(!isset($this->spool[$email])) $this->spool[$email] = [];            
            $this->spool[$email][] = $message;            
        }
    }

    /**
     * Clean the spool (delete all pending messages without sending them)
     */
    public function flush() {
        $files = scandir(self::MESSAGE_FOLDER);
        foreach($files as $file) {
            // retrieve file full path
            $filename = self::MESSAGE_FOLDER.'/'.$file;
            unlink($filename);
        }
    }

    /**
     * Add a message to the spool
     */
    public function queue($subject, $message, $email) {
        $message = [
            'subject'   => $subject,
            'message'   => $message,
            'email'     => $email
        ];
        $data = serialize($message);
        $file = md5($data);
        $filename = self::MESSAGE_FOLDER.'/'.$file;
        file_put_contents($filename, $data);
    }
    

    /**
     * Send all messages currently in the spool
     */
    public function run() {
        $this->load();
        
        $transport = new Swift_SmtpTransport(EMAIL_SMTP_HOST, EMAIL_SMTP_PORT);
        $transport->setUsername(EMAIL_SMTP_ACCOUNT_USERNAME)
                  ->setPassword(EMAIL_SMTP_ACCOUNT_PASSWORD);          
 
        $mailer = new Swift_Mailer($transport);                  

        foreach($this->spool as $email => $messages) {
            $envelope = new Swift_Message();

            $body = '';
            $subject = '';

            foreach($messages as $message) {
                if(!strlen($subject) && strlen($message['subject'])) $subject = $message['subject'];
                $body .= $message['message'];
                // remove file from storage
                $data = serialize($message);
                $file = md5($data);
                $filename = self::MESSAGE_FOLDER.'/'.$file;
                unlink($filename);
            }
        
            $envelope->setTo($email)
                     ->setSubject($subject)
                     ->setContentType("text/html")
                     ->setBody($body)
                     ->setFrom([EMAIL_SMTP_ACCOUNT_EMAIL => EMAIL_SMTP_ACCOUNT_DISPLAYNAME]);
                 
            $result = $mailer->send($envelope);     
        }
    }

}