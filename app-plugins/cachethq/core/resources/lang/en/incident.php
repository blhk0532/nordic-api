<?php

return [
    'title' => 'Schema',
    'resource_label' => 'Schema|Schema',
    'status' => [
        'investigating' => 'Investigating',
        'identified' => 'Identified',
        'watching' => 'Watching',
        'fixed' => 'Fixed',
        'reported' => 'Reported',
    ],
    'edit_button' => 'Edit Schema',
    'new_button' => 'Update',
    'no_incidents_reported' => 'No Schema',
    'timeline' => [
        'past_incidents_header' => '',
        'recent_incidents_header' => 'Recent Schema',
        'no_incidents_reported_between' => 'No Schema reported between :from and :to',
        'navigate' => [
            'previous' => 'Previous',
            'today' => 'Today',
            'next' => 'Next',
        ],
    ],
    'list' => [
        'headers' => [
            'name' => 'Name',
            'status' => 'Status',
            'visible' => 'Visible',
            'stickied' => 'Stickied',
            'occurred_at' => 'Arbetsdag',
            'notified_subscribers' => 'Notified subscribers',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'deleted_at' => 'Deleted at',
        ],
        'actions' => [
            'record_update' => 'Record Update',
            'view_incident' => 'View information',
        ],
        'empty_state' => [
            'heading' => 'Schema',
            'description' => 'Schema is used to communicate and track the status of your services.',
        ],
    ],
    'form' => [
        'name_label' => 'Name',
        'status_label' => 'Status',
        'message_label' => 'Message',
        'occurred_at_label' => 'Arbetsdag',
        'occurred_at_helper' => 'The information\'s created timestamp will be used if left empty.',
        'visible_label' => 'Visible',
        'user_label' => 'User',
        'user_helper' => 'The user who reported the information.',
        'notifications_label' => 'Notify Subscribers?',
        'stickied_label' => 'Sticky Information?',
        'guid_label' => 'Information UUID',
        'add_component' => [
            'action_label' => 'Add Campaign',
            'header' => 'Kampanj',
            'component_label' => 'Kampanj',
            'status_label' => 'Status',
        ],
    ],
    'record_update' => [
        'success_title' => 'Information :name Updated',
        'success_body' => 'A new information update has been recorded.',
        'form' => [
            'message_label' => 'Message',
            'status_label' => 'Status',
            'user_label' => 'User',
            'user_helper' => 'Who reported this information.',
        ],
    ],
    'overview' => [
        'total_incidents_label' => 'Total Information',
        'total_incidents_description' => 'Uppdateringar.',
    ],
];
