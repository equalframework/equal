<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Log extends Model {

    public static function getColumns() {
        return [
            'date' => [
                'type'              => 'datetime',
                'description'       => 'Date and time of the log entry creation.'
            ],

            'action' => [
                'type'              => 'string',
                'required'          => true,
                'description'       => 'The name of the action performed on targeted object (\'create\', \'update\', \'delete\', or any custom value).'
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'required'          => true,
                'description'       => 'User that performed the action.'
            ],

            'object_class' => [
                'type'              => 'string',
                'description'       => "Class of entity this entry is related to."
            ],

            'object_id' => [
                'type'              => 'integer',
                'description'       => "Identifier of the targeted object (of given class)."
            ],

            'history' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'usage'             => 'text/html',
                'description'       => "Textual history of changes made to the object at the time of the Log.",
                'function'          => 'calcHistory',
                'store'             => false
            ]

        ];
    }

    /**
     * Returns the unique fields of the model (for indexing).
     * #memo - unity is checked through Collection
     *
     * @return string
     */
    public function getUnique() {
        return [
            ['created', 'user_id', 'object_class', 'object_id'],
        ];
    }

    /**
     * This method is used to calculate the history of a given object
     *
     */
    protected static function calcHistory($self) {
        $result = [];

        $self->read(['user_id', 'object_class', 'object_id', 'created']);

        foreach($self as $id => $log) {
            $change = Change::search(['log_id', '=', $id])->read(['description', 'diff'])->first();
            if(!$change) {
                continue;
            }

            $map_old_values = [];
            $map_new_values = json_decode($change['diff'], true);
            if ($map_new_values === null) {
                // ignore invalid JSON
                continue;
            }
            $fields = array_keys($map_new_values);

            $changes_ids = Change::search([
                        ['object_class', '=', $log['object_class']],
                        ['object_id', '=', $log['object_id']],
                        ['created', '<', $log['created']],
                        ['log_id', '<>', $id]
                    ],
                    ['limit' => 25, 'sort' => ['created' => 'desc']]
                )
                ->ids();

            foreach($changes_ids as $change_id) {
                $change = Change::id($change_id)->read(['diff'])->first();
                $json = json_decode($change['diff'], true);
                if ($json === null) {
                    // ignore invalid JSON
                    continue;
                }
                foreach($fields as $index => $field) {
                    if(isset($json[$field])) {
                        $map_old_values[$field] = $json[$field];
                        unset($fields[$index]);
                    }
                }
                if(empty($fields)) {
                    break;
                }
            }

            $html = "<table>";
            $max_len = 120;

            // #todo - quick workaround to improve dates display
            $isTimestamp = function($val, $field) {
                if(in_array($field, ['created', 'modified'], true)) {
                    return true;
                }
                elseif(strpos($field, 'date') !== false) {
                    return true;
                }
                elseif(is_numeric($val)) {
                    // plausible si entre 2000 et 2100
                    return ($val > 946684800 && $val < 4102444800);
                }
                return false;
            };

            foreach($map_new_values as $field => $new) {
                $old = $map_old_values[$field];

                if($old === $new) {
                    continue;
                }

                if(is_array($old)) {
                    $old = implode(', ', $old);
                }
                elseif(is_object($old)) {
                    $old = json_encode($old, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                elseif(is_bool($old)) {
                    $old = $old ? 'true' : 'false';
                }
                elseif(is_null($old)) {
                    $old = '(null)';
                }
                elseif($old === '') {
                    $old = '(empty string)';
                }
                elseif($isTimestamp($old, $field)) {
                    $old = date('c', $old);
                }

                if(is_array($new)) {
                    $new = implode(', ', $new);
                }
                elseif(is_object($new)) {
                    $new = json_encode($new, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                elseif(is_bool($new)) {
                    $new = $new ? 'true' : 'false';
                }
                elseif(is_null($new)) {
                    $new = '(null)';
                }
                elseif($new === '') {
                    $new = '(empty string)';
                }
                elseif($isTimestamp($new, $field)) {
                    $new = date('c', $new);
                }

                $old = mb_strlen($old) > $max_len ? mb_substr($old, 0, $max_len - 1) . '…' : $old;
                $new = mb_strlen($new) > $max_len ? mb_substr($new, 0, $max_len - 1) . '…' : $new;

                $html .= "<tr><td><strong>$field</strong></td><td>&nbsp;</td><td><em>$old</em></td><td>→</td><td>$new</td></tr>";
            }

            $html .= "</tbody></table>";

            $result[$id] = $html;
        }

        return $result;
    }

}
