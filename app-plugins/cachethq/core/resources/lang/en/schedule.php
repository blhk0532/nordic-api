<?php

return [
    'resource_label' => 'Period|Perioder',
    'list' => [
        'headers' => [
            'name' => 'Name',
            'status' => 'Status',
            'scheduled_at' => 'Scheduled at',
            'completed_at' => 'Completed at',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'deleted_at' => 'Deleted at',
        ],
        'empty_state' => [
            'heading' => 'Perioder',
            'description' => 'Plan and schedule your maintenance.',
        ],
        'actions' => [
            'record_update' => 'Record Update',
            'complete' => 'Complete',
        ],
    ],
    'form' => [
        'name_label' => 'Name',
        'message_label' => 'Message',
        'scheduled_at_label' => 'Scheduled at',
        'completed_at_label' => 'Completed at',
    ],
    'add_update' => [
        'success_title' => 'Schedule :name Updated',
        'success_body' => 'A new schedule update has been recorded.',
        'form' => [
            'message_label' => 'Message',
            'completed_at_label' => 'Completed at',
        ],
    ],
    'status' => [
        'upcoming' => 'Upcoming',
        'in_progress' => 'In Progress',
        'complete' => 'Complete',
    ],
    'planned_maintenance_header' => '',
];
