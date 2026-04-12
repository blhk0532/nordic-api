<?php

return [
    'host' => env('AMI_HOST', '127.0.0.1'),
    'port' => (int) env('AMI_PORT', 5038),
    'username' => env('AMI_USERNAME'),
    'secret' => env('AMI_SECRET'),
    'dongle' => [
        'sms' => [
            'device' => env('AMI_SMS_DEVICE'),
        ],
    ],
    'events' => [
        'AGIExec' => [
        ],
        'AgentConnect' => [
        ],
        'AgentComplete' => [
        ],
        'Agentlogin' => [
        ],
        'Agentlogoff' => [
        ],
        'Agents' => [
        ],
        'AsyncAGI' => [
        ],
        'Bridge' => [
        ],
        'CDR' => [
        ],
        'CEL' => [
        ],
        'ChannelUpdate' => [
        ],
        'CoreShowChannel' => [
        ],
        'CoreShowChannelsComplete' => [
        ],
        'DAHDIShowChannelsComplete' => [
        ],
        'DAHDIShowChannels' => [
        ],
        'DBGetResponse' => [
        ],
        'DTMF' => [
        ],
        'Dial' => [
        ],
        'DongleDeviceEntry' => [
        ],
        'DongleNewCUSD' => [
        ],
        'DongleNewUSSDBase64' => [
        ],
        'DongleNewUSSD' => [
        ],
        'DongleSMSStatus' => [
        ],
        'DongleShowDevicesComplete' => [
        ],
        'DongleStatus' => [
        ],
        'DongleUSSDStatus' => [
        ],
        'DonglePortFail' => [
        ],
        'ExtensionStatus' => [
        ],
        'FullyBooted' => [
        ],
        'Hangup' => [
        ],
        'Hold' => [
        ],
        'JabberEvent' => [
        ],
        'Join' => [
        ],
        'Leave' => [
        ],
        'Link' => [
        ],
        'ListDialPlan' => [
        ],
        'Masquerade' => [
        ],
        'MessageWaiting' => [
        ],
        'MusicOnHold' => [
        ],
        'NewAccountCode' => [
        ],
        'NewCallerid' => [
        ],
        'Newchannel' => [
        ],
        'Newexten' => [
        ],
        'Newstate' => [
        ],
        'OriginateResponse' => [
        ],
        'ParkedCall' => [
        ],
        'ParkedCallsComplete' => [
        ],
        'PeerEntry' => [
        ],
        'PeerStatus' => [
        ],
        'PeerlistComplete' => [
        ],
        'QueueMemberAdded' => [
        ],
        'QueueMember' => [
        ],
        'QueueMemberPaused' => [
        ],
        'QueueMemberRemoved' => [
        ],
        'QueueMemberStatus' => [
        ],
        'QueueParams' => [
        ],
        'QueueStatusComplete' => [
        ],
        'QueueSummaryComplete' => [
        ],
        'QueueSummary' => [
        ],
        'RTCPReceived' => [
        ],
        'RTCPReceiverStat' => [
        ],
        'RTCPSent' => [
        ],
        'RTPReceiverStat' => [
        ],
        'RTPSenderStat' => [
        ],
        'RegistrationsComplete' => [
        ],
        'Registry' => [
        ],
        'Rename' => [
        ],
        'ShowDialPlanComplete' => [
        ],
        'StatusComplete' => [
        ],
        'Status' => [
        ],
        'Transfer' => [
        ],
        'UnParkedCall' => [
        ],
        'Unlink' => [
        ],
        'UserEvent' => [
        ],
        'VarSet' => [
        ],
    ],
];
