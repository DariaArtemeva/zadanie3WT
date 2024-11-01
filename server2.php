<?php
//php server2.php start
    use Workerman\Worker;
    use Workerman\Lib\Timer;
    require_once __DIR__ . '/vendor/workerman/workerman/Autoloader.php';

 
    function generateRandomNumberJsonMessage($maxRandNum) {
		$time = date('h:i:s');
		$num=rand(0, intval($maxRandNum));  
		 
		$obj = new stdClass();
		$obj->msg = "The server time is: {$time}";
		$obj->num = "$num";
		return json_encode($obj);
	}

    // SSL context.
    $context = [
        'ssl' => [
            'local_cert'  => '/home/xartemeva/webte_fei_stuba_sk.pem',
            'local_pk'    => '/home/xartemeva/webte.fei.stuba.sk.key',
            'verify_peer' => false,
        ]
    ];
    
    // Create A Worker and Listens 9000 port, use Websocket protocol
    $ws_worker = new Worker("websocket://0.0.0.0:9000", $context);
    
    // Enable SSL. WebSocket+SSL means that Secure WebSocket (wss://). 
    // The similar approaches for Https etc.
    $ws_worker->transport = 'ssl';
 
    // 4 processes
    $ws_worker->count = 4;
    
    // Add a Timer to Every worker process when the worker process start
    $ws_worker->onWorkerStart = function($ws_worker)
    {   $GLOBALS['userdata']=0;    
        // Timer every 5 seconds
        Timer::add(5, function()use($ws_worker)
        {          
          // Iterate over connections and send the time          
          foreach($ws_worker->connections as $connection)
            {
                $connection->send(generateRandomNumberJsonMessage($GLOBALS['userdata']));
            }            
        });
    
 
    // Emitted when new connection come
    $ws_worker->onConnect = function($connection)
    {
        // Emitted when websocket handshake done
        $connection->onWebSocketConnect = function($connection)
        {
            echo "New connection\n";
        };
    };
 
    $ws_worker->onMessage = function($connection, $data)
    {
        $GLOBALS['userdata']=$data;
        // Send hello $data
        $connection->send(generateRandomNumberJsonMessage($data));
    };
 
    // Emitted when connection closed
    $ws_worker->onClose = function($connection)
    {
        echo "Connection closed";
    };
}; 
    // Run worker
    Worker::runAll();
    
    
?>
    