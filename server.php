<?php
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Websocket\Connection;

$ws_worker = new Worker('websocket://0.0.0.0:9000');

$ws_worker->count = 4;

$clients = [];

$ws_worker->onConnect = function($connection) use (&$clients) {
    $connection->id = uniqid();
    $clients[$connection->id] = $connection;
};

$ws_worker->onMessage = function($connection, $data) use (&$clients) {
    $messageData = json_decode($data, true);

    switch ($messageData['type']) {
        case 'move':
    
    
            break;
    }
};

$ws_worker->onClose = function($connection) use (&$clients) {
    unset($clients[$connection->id]);
};

Worker::runAll();

$gameState = [
    'ball' => [
        'x' => 0,
        'y' => 0,
        'dx' => 0,
        'dy' => 0
    ],
    'paddles' => [
        [
            'x' => 0,
            'y' => 0
        ],
        [
            'x' => 0,
            'y' => 0
        ],
        [
            'x' => 0,
            'y' => 0
        ],
        [
            'x' => 0,
            'y' => 0
        ]
    ],
    'player1Score' => 0,
    'player2Score' => 0,
    'player3Score' => 0,
    'player4Score' => 0
];

function sendGameStateToClient($client, $gameState) {
    $message = [
        'type' => 'updateGameState',
        'data' => $gameState
    ];
    $client->send(json_encode($message));
}

$ws_worker->onMessage = function($connection, $data) use (&$clients, &$gameState) {
    $message = json_decode($data, true);
    switch ($message['type']) {
        case 'userAction':
            // Update game state based on the user action
            break;
    }

    // Send updated game state to all clients
    foreach ($clients as $client) {
        sendGameStateToClient($client, $gameState);
    }
};
