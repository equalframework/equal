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
                'function'          => 'calcHistory'
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

            foreach($map_new_values as $field => $new) {
                $old = $map_old_values[$field] ?? '(empty)';
                if($old === $new) {
                    continue;
                }

                if(is_bool($new)) {
                    $new = $new ? 'true' : 'false';
                }
                elseif(is_null($new)) {
                    $new = 'null';
                }
                elseif($new === '') {
                    $new = '(empty string)';
                }

                $html .= "<tr><td><strong>$field</strong></td><td>&nbsp;</td><td><em>$old</em></td><td>→</td><td>$new</td></tr>";
            }

            $html .= "</tbody></table>";

            $result[$id] = $html;
        }

        return $result;
    }

}
