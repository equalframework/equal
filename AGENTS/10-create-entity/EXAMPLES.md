- Minimal entity class skeleton:
```php
<?php
namespace sale\booking;

use equal\orm\Model;

class BookingExample extends Model {
    public static function getColumns() {
        return [
            'name' => ['type' => 'string', 'required' => true]
        ];
    }
}
```

- Advanced entity class example:
```php
<?php
namespace realestate\governance;

class AssemblyExample extends \equal\orm\Model {

    public static function getColumns() {
        return [
            'condo_id' => [
                'type'           => 'many2one',
                'foreign_object' => 'realestate\property\Condominium',
                'description'    => 'Condominium concerned by the assembly.',
                'required'       => true,
                'dependents'     => ['count_shares', 'count_owners'],
                'onupdate'       => 'onupdateCondoId'
            ],

            'name' => [
                'type'        => 'string',
                'description' => 'Assembly title.',
                'required'    => true,
                'multilang'   => true
            ],

            'assembly_type' => [
                'type'        => 'string',
                'description' => 'Type of assembly.',
                'selection'   => ['statutory', 'extraordinary'],
                'default'     => 'statutory'
            ],

            'status' => [
                'type'        => 'string',
                'description' => 'Current lifecycle status.',
                'selection'   => ['draft', 'published', 'in_progress', 'closed'],
                'default'     => 'draft'
            ],

            'assembly_date' => [
                'type'        => 'date',
                'description' => 'Scheduled date of the assembly.'
            ],

            'assembly_location' => [
                'type'        => 'string',
                'description' => 'Planned location of the meeting.'
            ],

            'assembly_template_id' => [
                'type'           => 'many2one',
                'foreign_object' => 'realestate\governance\AssemblyTemplate',
                'description'    => 'Template used to initialize agenda items.',
                'onupdate'       => 'onupdateAssemblyTemplateId',
                'visible'        => ['is_second_session', '=', false]
            ],

            'is_second_session' => [
                'type'        => 'boolean',
                'description' => 'Marks a follow-up session.',
                'default'     => false
            ],

            'related_assembly_id' => [
                'type'           => 'many2one',
                'foreign_object' => 'realestate\governance\Assembly',
                'description'    => 'Original assembly when this is a second session.',
                'visible'        => ['is_second_session', '=', true],
                'readonly'       => true
            ],

            'ownerships_ids' => [
                'type'            => 'many2many',
                'foreign_object'  => 'realestate\ownership\Ownership',
                'foreign_field'   => 'assemblies_ids',
                'rel_table'       => 'realestate_governance_assembly_rel_ownership',
                'rel_local_key'   => 'assembly_id',
                'rel_foreign_key' => 'ownership_id',
                'description'     => 'Ownerships concerned by the assembly.'
            ],

            'assembly_items_ids' => [
                'type'           => 'one2many',
                'foreign_object' => 'realestate\governance\AssemblyItem',
                'foreign_field'  => 'assembly_id',
                'description'    => 'Agenda items.',
                'domain'         => [['condo_id', '=', 'object.condo_id']],
                'order'          => 'order',
                'onupdate'       => 'onupdateAssemblyItemsIds'
            ],

            'assembly_attendees_ids' => [
                'type'           => 'one2many',
                'foreign_object' => 'realestate\governance\AssemblyAttendee',
                'foreign_field'  => 'assembly_id',
                'description'    => 'Registered attendees.',
                'domain'         => [['condo_id', '=', 'object.condo_id']]
            ],

            'count_shares' => [
                'type'        => 'computed',
                'result_type' => 'integer',
                'description' => 'Total statutory shares of the condominium.',
                'relation'    => ['condo_id' => 'total_shares'],
                'store'       => true,
                'readonly'    => true
            ],

            'count_owners' => [
                'type'        => 'computed',
                'result_type' => 'integer',
                'description' => 'Number of ownerships concerned by the assembly.',
                'function'    => 'calcCountOwners',
                'store'       => true,
                'readonly'    => true
            ],

            'count_attendees' => [
                'type'        => 'computed',
                'result_type' => 'integer',
                'description' => 'Number of registered attendees.',
                'function'    => 'calcCountAttendees',
                'store'       => true,
                'readonly'    => true
            ],

            'quorum_rate' => [
                'type'        => 'computed',
                'result_type' => 'float',
                'description' => 'Attendance ratio in percent.',
                'function'    => 'calcQuorumRate',
                'readonly'    => true
            ],

            'is_quorum_reached' => [
                'type'        => 'computed',
                'result_type' => 'boolean',
                'description' => 'True when at least half of the ownerships are represented.',
                'function'    => 'calcIsQuorumReached',
                'store'       => true,
                'readonly'    => true
            ]
        ];
    }

    public static function getActions() {
        return [
            'publish' => ['description' => 'Publish the assembly.', 'policies' => [], 'function' => 'doPublish'],
            'refresh_items_order' => ['description' => 'Normalize agenda ordering.', 'policies' => [], 'function' => 'doRefreshItemsOrder']
        ];
    }

    protected static function onupdateCondoId($self) {
        $self->read(['condo_id' => ['name'], 'assembly_location']);
        foreach($self as $id => $assembly) {
            if(empty($assembly['assembly_location']) && !empty($assembly['condo_id']['name'])) {
                self::id($id)->update(['assembly_location' => 'Common room - ' . $assembly['condo_id']['name']]);
            }
        }
    }

    protected static function onupdateAssemblyTemplateId($self) {
        $self->read(['condo_id', 'assembly_template_id']);
        foreach($self as $id => $assembly) {
            AssemblyItem::search([['assembly_id', '=', $id]])->delete(true);

            $templates = AssemblyItemTemplate::search([
                    ['assembly_template_id', '=', $assembly['assembly_template_id']],
                    ['is_group', '=', false]
                ])
                ->read(['name', 'code', 'order', 'description_call', 'has_vote_required', 'majority']);

            foreach($templates as $template) {
                AssemblyItem::create([
                    'condo_id'         => $assembly['condo_id'],
                    'assembly_id'      => $id,
                    'name'             => $template['name'],
                    'code'             => $template['code'],
                    'order'            => $template['order'],
                    'description_call' => $template['description_call'],
                    'has_vote_required'=> $template['has_vote_required'],
                    'majority'         => $template['majority']
                ]);
            }
        }
    }

    protected static function onupdateAssemblyItemsIds($self) {
        $self->do('refresh_items_order');
    }

    protected static function doPublish($self) {
        $self->read(['status', 'assembly_date', 'assembly_items_ids']);
        foreach($self as $id => $assembly) {
            if($assembly['status'] !== 'draft') {
                continue;
            }
            if(!$assembly['assembly_date'] || !count($assembly['assembly_items_ids'])) {
                throw new \Exception('missing_required_data', EQ_ERROR_INVALID_PARAM);
            }
            self::id($id)->update(['status' => 'published']);
        }
    }

    protected static function doRefreshItemsOrder($self) {
        foreach($self as $id => $assembly) {
            $items = AssemblyItem::search([['assembly_id', '=', $id]])->read(['order']);
            $index = 1;
            foreach($items as $item_id => $item) {
                AssemblyItem::id($item_id)->update(['order' => $index++]);
            }
        }
    }

    protected static function calcCountOwners($self) {
        $result = [];
        $self->read(['ownerships_ids']);
        foreach($self as $id => $assembly) {
            $result[$id] = count($assembly['ownerships_ids']);
        }
        return $result;
    }

    protected static function calcCountAttendees($self) {
        $result = [];
        $self->read(['assembly_attendees_ids']);
        foreach($self as $id => $assembly) {
            $result[$id] = count($assembly['assembly_attendees_ids']);
        }
        return $result;
    }

    protected static function calcQuorumRate($self) {
        $result = [];
        $self->read(['count_owners', 'count_attendees']);
        foreach($self as $id => $assembly) {
            $result[$id] = $assembly['count_owners'] ? round(($assembly['count_attendees'] / $assembly['count_owners']) * 100, 2) : 0;
        }
        return $result;
    }

    protected static function calcIsQuorumReached($self) {
        $result = [];
        $self->read(['count_owners', 'count_attendees']);
        foreach($self as $id => $assembly) {
            $result[$id] = ($assembly['count_owners'] > 0 && $assembly['count_attendees'] >= ceil($assembly['count_owners'] / 2));
        }
        return $result;
    }
}
```