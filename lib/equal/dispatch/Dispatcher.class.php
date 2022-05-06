<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\dispatch;

use equal\organic\Service;
use equal\services\Container;


class Dispatcher extends Service {


    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     */
    protected function __construct(Container $container) {
    }

    public static function constants() {
        return ['ROOT_USER_ID'];
    }

    /**
     * Run a batch of scheduled tasks.
     * #memo - Scheduler always operates as root user.
     *
     * @param   string    $message_model    Name of the model the message relates to.
     * @param   string    $object_class     Class name of the entity the message relates to.
     * @param   string    $object_id        Identifier of the entity the message relates to.
     * @param   string    $severity         Selection: 'notice', 'warning', 'important', 'urgent'.
     * @param   string    $controller       Controller to invoker, with package notation.
     * @param   array     $params           Array holding the payload to relay to the controller.
     * @param   array     $links            Array holding a list of links (md format) to objects somehow related to the message.
     *
     */
    public function dispatch($message_model, $object_class, $object_id, $severity='notice', $controller=null, $params=[], $links=[]) {
        /** @var \equal\orm\ObjectManager */
        $orm = $this->container->get('orm');
        trigger_error("QN_DEBUG_PHP::dispatching message", QN_REPORT_DEBUG);

        $message_models_ids = $orm->search('core\alert\MessageModel', ['name', '=', $message_model]);

        if($message_models_ids > 0 ){
            $message_model_id = reset($message_models_ids);

            $values = [
                'message_model_id'  => $message_model_id,
                'object_class'      => $object_class,
                'object_id'         => $object_id,
                'severity'          => $severity,
                'controller'        => $controller,
                'params'            => json_encode($params),
                'links'             => json_encode($links)
            ];

            if(!count($orm->validate('core\alert\Message', [], $values, true, true))) {
                $orm->create('core\alert\Message', $values);
            }

        }
        else {
            trigger_error("QN_DEBUG_PHP::unknown message model", E_USER_WARNING);
        }
    }

    /**
     * Try to dismiss a message.
     * This should be invoked following a user request for removing the message.
     * The message is deleted and, if it relates to a controller, a call is made (which, in turn, can lead to the message being re-created).
     *
     * @param   string    $id         Identifier of the message to cancel.
     */
    public function dismiss($id) {
        /** @var \equal\orm\ObjectManager */
        $orm = $this->container->get('orm');
        $messages_ids = $orm->search('core\alert\Message', ['id', '=', $id]);
        if($messages_ids > 0) {
            $message_id = reset($messages_ids);
            $messages = $orm->read('core\alert\Message', $message_id, ['controller', 'params']);
            if($messages > 0) {
                $message = reset($messages);
                $orm->remove('core\alert\Message', $message_id, true);                
                if($message['controller']) {
                    try {
                        $body = json_decode($message['params'], true);
                        \eQual::run('do', $message['controller'], $body, true);
                    }
                    catch(\Exception $e) {
                        // error occured during execution
                    }
                }
            }
        }
    }

    /**
     * Cancel (delete) a message without running related controller.
     * This should be invoked when a message no longer relates to an actual situation.
     *
     * @param   string    $message_model    Name of the model the message relates to.
     * @param   string    $object_class     Class name of the entity the message relates to.
     * @param   string    $object_id        Identifier of the entity the message relates to.
     */
    public function cancel($message_model, $object_class, $object_id) {
        /** @var \equal\orm\ObjectManager */
        $orm = $this->container->get('orm');

        $message_models_ids = $orm->search('core\alert\MessageModel', ['name', '=', $message_model]);

        if($message_models_ids > 0 ){
            $message_model_id = reset($message_models_ids);
            $messages_ids = $orm->search('core\alert\Message', [['message_model_id', '=', $message_model_id], ['object_class', '=', $object_class], ['object_id', '=', $object_id]] );
            if($messages_ids > 0) {
                $orm->remove('core\alert\Message', $messages_ids, true);
            }
        }
    }

}