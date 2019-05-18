<?php date_default_timezone_set("Africa/Kampala"); ?>
<?php require_once("extract_sensor_data.php"); ?>
<?php $api = new extract_sensor_data(); ?>
<?php require_once("cache/phpfastcache.php"); $cache = phpFastCache(); ?>
<?php
	$x=0;
	while(true)
	{
		if(++$x >= 200) { $api->unset_all(); exit;}
		print("Querying unprocessed sensor_data . . . \n");
		//$a = $dbC->query("select * from tambula_log2 where `status` = 'pending' order by id asc");
		$queues = array("sensor_data","sensor_data2", "sensor_data3");
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
		
		
		print "Processing queue ".$cache_name."\n";
		$cache_result = $cache->get($cache_name);
		
		if($cache_result != null && sizeof($cache_result) > 0)
		{
			//Lock Cache
			print "Locking ".$cache_name."\n";
			$cache->set($cache_name."_lock",$cache_name,600);
			
			$processed_ids = array();
			$unprocessed_ids = array();
						
			$sql_data = array();
			
			print(sizeof($cache_result)." rows to be processed - ".date("Y-m-d H:i:s")."\n");
			//$b = $a->fetch_assoc();
			$temp_query = "";
			for($i=0;$i<sizeof($cache_result);$i++)
			{
				if(isset($cache_result[$i]['data']) && trim($cache_result[$i]['data']) == "" && is_array($cache_result[$i]))
				{
					continue;
				}
				
				print_r($cache_result[$i]);
				$processed = false;
				
				$processed = $api->data_decode(trim($cache_result[$i]['data']));
				
				//Log to Database
								
				$api->unset_all();				
				print("Last Update: ".date("Y-m-d H:i:s")."\n");
			} 
			
			//Unlock
			$cache->delete($cache_name);
			print "Clearing ".$cache_name."\n";
			$cache->delete($cache_name."_lock");
			print "Unlocking ".$cache_name." . . .\n";
		}
		
		print("Sleeping for 1 seconds - ".date("Y-m-d H:i:s")."\n");
		sleep(1);
		//exit;
		
	}

?>