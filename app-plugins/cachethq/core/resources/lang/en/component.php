<?php

return [
    'resource_label' => 'Kampanj|Kampanjer',
    'list' => [
        'headers' => [
            'name' => 'Name',
            'status' => 'Status',
            'order' => 'Order',
            'group' => 'Group',
            'enabled' => 'Enabled',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'deleted_at' => 'Deleted at',
        ],
        'empty_state' => [
            'heading' => 'Kampanjer',
            'description' => 'Kampanjer representerar de olika delarna av ditt system som kan påverka statusen på din status-sida.',
        ],
    ],
    'last_updated' => ':timestamp',
    'view_details' => 'Visa detaljer',
    'form' => [
        'name_label' => 'Kampanj',
        'status_label' => 'Status',
        'description_label' => 'Beskrivning',
        'campaign_group_label' => 'Kampanjgrupp',
        'link_label' => 'änk',
        'link_helper' => 'Länk till kampanj.',
    ],
    'status' => [
        'operational' => 'Operational',
        'performance_issues' => 'Performance Issues',
        'partial_outage' => 'Partial Outage',
        'major_outage' => 'Major Outage',
        'under_maintenance' => 'Under maintenance',
        'unknown' => 'Unknown',
    ],

];
