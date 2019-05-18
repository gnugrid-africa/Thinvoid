<?php date_default_timezone_set("Africa/Kampala"); ?>
<?php
require_once("SocketServer.class.php"); // Include the File

//Set Server IP
$serverIP = "";
//Server Port
$serverPort = "";

$server = new SocketServer($serverIP, $serverPort); // Create a Server binding to the given ip address and listen to port 31337 for connections
$server->max_clients = 100000; // Allow no more than 10 people to connect at a time
$server->max_read = 1024000;
$server->hook("CONNECT","handle_connect"); // Run handle_connect every time someone connects
$server->hook("INPUT","handle_input"); // Run handle_input whenever text is sent to the server
$dbC = "";
$server->infinite_loop(); // Run Server Code Until Process is terminated.


function handle_connect($server,&$client,$input)
{
   SocketServer::socket_write_smart($client->socket,time(),"");
}

function handle_input($server,&$client,$input)
{
	  try
	  {
	  		//Echo Output to log file
	  		print("input:".$input."\n");
	  		
	  		//Cache Input
	  		require_once("cache/phpfastcache.php"); 
		  	$cache = phpFastCache();
		  	$content = array("key"=>md5($input),"data"=>$input,"log_date"=>date("Y-m-d H:i:s"));
		  
		  	
		  	$queues = array("sensor_data_1","sensor_data_2", "sensor_data_3");
		  	
		  	foreach($queues as $queue)
		  	{		  
		  		//Check if not locked
				$lock_status = $cache->get($queue."_lock");
				if($lock_status == null)
				{
					$cache_name = $queue;
					break;
				}
				else
				{
					continue;
				}
		  	}
		  
		  	//Lock cache
		  	$cache->set($cache_name."_lock","locked",600);
		  
		 	print "Locking queue ".$cache_name.".\n";
		  	$cache_result = $cache->get($cache_name);
		  
		  	if($cache_result == null)
		  	{
			  $temp = array();
			  $temp[] = $content;
			  $cache->set($cache_name,$temp,600);
		  	}
		  	else
		  	{
			  $cache_result[] = $content;
			  $cache->delete($cache_name);
			  $cache->set($cache_name,$cache_result,600);
		  	}
		  
		  	$cache->delete($cache_name."_lock");
		  	print "Unlocked queue ".$cache_name."\n";
		  
		  	print(sizeof($cache->get($cache_name))." items present\n");
      	  
	  		print("Last Update: ".date("Y-m-d H:i:s")."\n");
	  
	  		SocketServer::socket_write_smart($client->socket,time(),"");
	  		
	  		} catch (Exception $e)
	  		{
		  		print ($e->getMessage()."\n");
	  		}
}
?>