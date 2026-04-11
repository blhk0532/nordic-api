<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Terminal UI
    |--------------------------------------------------------------------------
    */
    'terminal' => [
        'connect' => 'Conectar',
        'disconnect' => 'Desconectar',
        'connected' => 'Conectado',
        'disconnected' => 'Desconectado',
        'connection_info' => 'Informações da conexão',
        'please_wait' => 'Aguarde...',
        'connect_terminal' => 'Conectar terminal',
        'disconnect_terminal' => 'Desconectar terminal',
        'session_status' => 'Status da sessão',
        'connection_details' => 'Detalhes da Conexão',
        'type' => 'Tipo',
        'host' => 'Servidor',
        'port' => 'Porta',
        'username' => 'Usuário',
        'auth_method' => 'Método de Autenticação',
        'ssh_key' => 'Chave SSH',
        'password' => 'Senha',
        'working_directory' => 'Diretório de Trabalho',
        'command_timeout' => 'Timeout do Comando',
        'login_shell' => 'Shell de Login',
        'enabled' => 'Habilitado',
        'disabled' => 'Desabilitado',
        'command_access' => 'Acesso a Comandos',
        'all_commands' => 'Todos os Comandos',
        'allowed_count' => ':count permitidos',
        'no_restrictions' => 'Sem restrições',
        'session_info' => 'Informações da Sessão',
        'session_id' => 'ID da Sessão',
        'commands_run' => 'Comandos Executados',
        'session_duration' => 'Duração da Sessão',
        'errors' => 'Erros',
        'allowed_commands' => 'Comandos Permitidos',
        'close_info_panel' => 'Fechar Painel de Informações',
        'click_connect' => 'Clique em Conectar para iniciar...',
        'type_input' => 'Digite e pressione Enter (Ctrl+C para cancelar)...',
        'type_command' => 'Digite um comando...',
        'running' => 'Executando',
        'cancel' => 'Cancelar',
        'cancel_shortcut' => 'Cancelar (Ctrl+C)',
        'keys' => 'Teclas:',
        'arrow_up' => 'Seta para Cima',
        'arrow_down' => 'Seta para Baixo',
        'arrow_left' => 'Seta para Esquerda',
        'arrow_right' => 'Seta para Direita',
        'enter' => 'Enter',
        'space' => 'Espaço',
        'tab' => 'Tab',
        'escape' => 'Escape',
        'backspace' => 'Backspace',
        'f1_help' => 'F1 - Ajuda',
        'f10_quit' => 'F10 - Sair (htop)',
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
        'terminal_logs' => 'Logs do Terminal',
        'tools' => 'Ferramentas',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */
    'pages' => [
        'terminal' => [
            'title' => 'Terminal',
            'local_terminal' => 'Terminal Local',
            'local_terminal_description' => 'Execute comandos no sistema local.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource
    |--------------------------------------------------------------------------
    */
    'resource' => [
        'label' => 'Log do Terminal',
        'plural_label' => 'Logs do Terminal',
        'back' => 'Voltar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */
    'table' => [
        'time' => 'Horário',
        'event' => 'Evento',
        'terminal' => 'Terminal',
        'type' => 'Tipo',
        'user' => 'Usuário',
        'command' => 'Comando',
        'exit' => 'Saída',
        'host' => 'Servidor',
        'session_id' => 'ID da Sessão',
        'ip_address' => 'Endereço IP',
        'duration' => 'Duração',
        'system' => 'Sistema',
        'localhost' => 'localhost',
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Filters
    |--------------------------------------------------------------------------
    */
    'filters' => [
        'event_type' => 'Tipo de Evento',
        'connection_type' => 'Tipo de Conexão',
        'user' => 'Usuário',
        'terminal' => 'Terminal',
        'failed_commands_only' => 'Apenas Comandos com Falha',
        'from' => 'De',
        'until' => 'Até',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Types
    |--------------------------------------------------------------------------
    */
    'events' => [
        'connected' => 'Conectado',
        'disconnected' => 'Desconectado',
        'command' => 'Comando',
        'output' => 'Saída',
        'error' => 'Erro',
        'blocked' => 'Bloqueado',
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
        'event_information' => 'Informações do Evento',
        'event_type' => 'Tipo de Evento',
        'connection_type' => 'Tipo de Conexão',
        'timing' => 'Tempo',
        'timestamp' => 'Data/Hora',
        'execution_time' => 'Tempo de Execução',
        'seconds' => ':count segundos',
        'user_session' => 'Usuário e Sessão',
        'user' => 'Usuário',
        'terminal_identifier' => 'Identificador do Terminal',
        'session_id' => 'ID da Sessão',
        'session_id_copied' => 'ID da sessão copiado',
        'ssh_connection_details' => 'Detalhes da Conexão SSH',
        'host' => 'Servidor',
        'port' => 'Porta',
        'ssh_username' => 'Usuário SSH',
        'command' => 'Comando',
        'command_copied' => 'Comando copiado',
        'exit_code' => 'Código de Saída',
        'output' => 'Saída',
        'client_information' => 'Informações do Cliente',
        'ip_address' => 'Endereço IP',
        'user_agent' => 'User Agent',
        'metadata' => 'Metadados',
        'metadata_key' => 'Chave',
        'metadata_value' => 'Valor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    */
    'widgets' => [
        'total_logs' => 'Total de Logs',
        'all_terminal_log_entries' => 'Todos os registros de log do terminal',
        'today' => 'Hoje',
        'logs_created_today' => 'Logs criados hoje',
        'commands' => 'Comandos',
        'total_commands_executed' => 'Total de comandos executados',
        'errors' => 'Erros',
        'total_error_events' => 'Total de eventos de erro',
    ],
];
