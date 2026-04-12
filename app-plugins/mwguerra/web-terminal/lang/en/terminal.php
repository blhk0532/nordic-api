<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Terminal UI
    |--------------------------------------------------------------------------
    */
    'terminal' => [
        'connect' => 'Connect',
        'disconnect' => 'Disconnect',
        'connected' => 'Connected',
        'disconnected' => 'Disconnected',
        'connection_info' => 'Connection info',
        'please_wait' => 'Please wait...',
        'connect_terminal' => 'Connect terminal',
        'disconnect_terminal' => 'Disconnect terminal',
        'session_status' => 'Session status',
        'connection_details' => 'Connection Details',
        'type' => 'Type',
        'host' => 'Host',
        'port' => 'Port',
        'username' => 'Username',
        'auth_method' => 'Auth Method',
        'ssh_key' => 'SSH Key',
        'password' => 'Password',
        'working_directory' => 'Working Directory',
        'command_timeout' => 'Command Timeout',
        'login_shell' => 'Login Shell',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'command_access' => 'Command Access',
        'all_commands' => 'All Commands',
        'allowed_count' => ':count allowed',
        'no_restrictions' => 'No restrictions',
        'session_info' => 'Session Info',
        'session_id' => 'Session ID',
        'commands_run' => 'Commands Run',
        'session_duration' => 'Session Duration',
        'errors' => 'Errors',
        'allowed_commands' => 'Allowed Commands',
        'close_info_panel' => 'Close Info Panel',
        'click_connect' => 'Click Connect to start...',
        'type_input' => 'Type input and press Enter (Ctrl+C to cancel)...',
        'type_command' => 'Type a command...',
        'running' => 'Running',
        'cancel' => 'Cancel',
        'cancel_shortcut' => 'Cancel (Ctrl+C)',
        'keys' => 'Keys:',
        'arrow_up' => 'Arrow Up',
        'arrow_down' => 'Arrow Down',
        'arrow_left' => 'Arrow Left',
        'arrow_right' => 'Arrow Right',
        'enter' => 'Enter',
        'space' => 'Space',
        'tab' => 'Tab',
        'escape' => 'Escape',
        'backspace' => 'Backspace',
        'f1_help' => 'F1 - Help',
        'f10_quit' => 'F10 - Quit (htop)',
        'local' => 'Local',
        'ssh' => 'SSH',
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        'terminal' => 'Terminal',
        'terminal_logs' => 'Shell Logs',
        'tools' => 'System LOGS',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */
    'pages' => [
        'terminal' => [
            'title' => 'Terminal',
            'local_terminal' => 'Local Terminal',
            'local_terminal_description' => 'Execute commands on the local system.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource
    |--------------------------------------------------------------------------
    */
    'resource' => [
        'label' => 'Terminal Log',
        'plural_label' => 'Shell Logs',
        'back' => 'Back',
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */
    'table' => [
        'time' => 'Time',
        'event' => 'Event',
        'terminal' => 'Terminal',
        'type' => 'Type',
        'user' => 'User',
        'command' => 'Command',
        'exit' => 'Exit',
        'host' => 'Host',
        'session_id' => 'Session ID',
        'ip_address' => 'IP Address',
        'duration' => 'Duration',
        'system' => 'System',
        'localhost' => 'localhost',
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Filters
    |--------------------------------------------------------------------------
    */
    'filters' => [
        'event_type' => 'Event Type',
        'connection_type' => 'Connection Type',
        'user' => 'User',
        'terminal' => 'Terminal',
        'failed_commands_only' => 'Failed Commands Only',
        'from' => 'From',
        'until' => 'Until',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Types
    |--------------------------------------------------------------------------
    */
    'events' => [
        'connected' => 'Connected',
        'disconnected' => 'Disconnected',
        'command' => 'Command',
        'output' => 'Output',
        'error' => 'Error',
        'blocked' => 'Blocked',
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Types
    |--------------------------------------------------------------------------
    */
    'connection_types' => [
        'local' => 'Local',
        'ssh' => 'SSH',
    ],

    /*
    |--------------------------------------------------------------------------
    | Infolist (View Page)
    |--------------------------------------------------------------------------
    */
    'infolist' => [
        'event_information' => 'Event Information',
        'event_type' => 'Event Type',
        'connection_type' => 'Connection Type',
        'timing' => 'Timing',
        'timestamp' => 'Timestamp',
        'execution_time' => 'Execution Time',
        'seconds' => ':count seconds',
        'user_session' => 'User & Session',
        'user' => 'User',
        'terminal_identifier' => 'Terminal Identifier',
        'session_id' => 'Session ID',
        'session_id_copied' => 'Session ID copied',
        'ssh_connection_details' => 'SSH Connection Details',
        'host' => 'Host',
        'port' => 'Port',
        'ssh_username' => 'SSH Username',
        'command' => 'Command',
        'command_copied' => 'Command copied',
        'exit_code' => 'Exit Code',
        'output' => 'Output',
        'client_information' => 'Client Information',
        'ip_address' => 'IP Address',
        'user_agent' => 'User Agent',
        'metadata' => 'Metadata',
        'metadata_key' => 'Key',
        'metadata_value' => 'Value',
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    */
    'widgets' => [
        'total_logs' => 'Total Logs',
        'all_terminal_log_entries' => 'All terminal log entries',
        'today' => 'Today',
        'logs_created_today' => 'Logs created today',
        'commands' => 'Commands',
        'total_commands_executed' => 'Total commands executed',
        'errors' => 'Errors',
        'total_error_events' => 'Total error events',
    ],
];
