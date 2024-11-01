<?php
    use Workerman\Worker;
    require_once 'vendor/workerman/workerman/Autoloader.php';
 
    function generateRandomNumberJsonMessage($maxRandNum) {
		$time = date('h:i:s');
		$num=rand(0, intval($maxRandNum));  
		 
		$obj = new stdClass();
		$obj->msg = "The server time is: {$time}";
		$obj->num = "$num";
		return json_encode($obj);
	}
    $clients = [];
    $context = [
        'ssl' => [
            'local_cert'  => '/home/xartemeva/webte_fei_stuba_sk.pem',
            'local_pk'    => '/home/xartemeva/webte.fei.stuba.sk.key',
            'verify_peer' => false,
        ]
    ];

    $ws_worker = new Worker("websocket://0.0.0.0:9000", $context);
    $ws_worker->transport = 'ssl';
 
    $ws_worker->count = 4;          
 
    $ws_worker->onConnect = function($connection) use (&$clients)
    {
        $clients[] = $connection;
        $connection->onWebSocketConnect = function($connection)
        {
            echo "New connection\n";
        };
    };
    
 
    $ws_worker->onMessage = function($connection, $data) use (&$clients)
    {
        $response = generateRandomNumberJsonMessage($data);
        foreach ($clients as $client) {
            $client->send($response);
        }
    };
    
 

    $ws_worker->onClose = function($connection) use (&$clients)
    {
        echo "Connection closed\n";
        $index = array_search($connection, $clients);
        if ($index !== false) {
            unset($clients[$index]);
        }
    };
    
 
    Worker::runAll();
    
 
?>
    