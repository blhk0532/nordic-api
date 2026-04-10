<?php

return [
    'resource_label' => 'Projekt|Projekt',
    'incident_count' => ':count Incident|:count Incidents',
    'visibility' => [
        'expanded' => 'Always Expanded',
        'collapsed' => 'Always Collapsed',
        'collapsed_unless_incident' => 'Collapsed Unless Ongoing',
    ],
    'list' => [
        'headers' => [
            'name' => 'Name',
            'visible' => 'Visible',
            'collapsed' => 'Collapsed',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
        'empty_state' => [
            'heading' => 'Projects are used to group components together.',
            'description' => 'Projects related components together.',
        ],
    ],
    'form' => [
        'name_label' => 'Name',
        'visible_label' => 'Visible',
        'collapsed_label' => 'Collapsed',
    ],
];
