<?php

$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['listcheckin_settings'] = [
    'id' => [
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ],
    'app_id' => [
        'type' => 'int(11)',
        'is_null' => false
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
        'foreign_key' => [
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_LISTCHECKIN_SETTINGS_VID_AOV_VID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ],
        'index' => [
            'key_name' => 'value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ]
    ],
    'check_in_history_enable'  => [
        'type' => 'int(11) unsigned',
        'default' => '1'
    ],
    'required_note'  => [
        'type' => 'int(11) unsigned',
        'default' => '1'
    ],
    'required_phone_number'  => [
        'type' => 'int(11) unsigned',
        'default' => '1'
    ],
    'required_personal_id'  => [
        'type' => 'int(11) unsigned',
        'default' => '1'
    ],
    'admin_email' => [
        'type' => 'varchar(255)',
        'is_null' => true
    ],
    'qr_checkin_note' => [
        'type' => 'text',
        'is_null' => true,
    ],
    'is_checkout_enable'  => [
        'type' => 'int(11) unsigned',
        'default' => 1
    ],
    'location_tracking'  => [
        'type' => 'int(11) unsigned',
        'default' => 1
    ],   
    'created_at' => [
        'type' => 'datetime',
    ],
    'updated_at' => [
        'type' => 'datetime',
    ]
];