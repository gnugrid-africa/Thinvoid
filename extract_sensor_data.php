<?php date_default_timezone_set("Africa/Kampala"); ?>
<?php
  class extract_sensor_data
  {
	  //General Info
	  public $db_resource = "";
	  public $serial_no = "";
	  public $device_time = ""; 
	  public $lat = ""; 
	  public $lng = "";
	  public $altitude = "";
	  public $charging_voltage = ""; 
	  public $battery_voltage = ""; 
	  public $load_voltage ="";
	  public $light_intensity ="";
	  
	  public $east=""; 
	  public $south=""; 
	  public $west="";
	  public $north="";

	  public $signal_strength="";
	  public $internal_battery = "";
	  
	  public $comments="";
	  
	  
	  //Construct function will require the SQL credentials.
	  function __construct()
	  {
		 $host = 'localhost';
		 $user = 'root';
		 $pass = '';
		 $db = 'gnugrid';
		 $this->db_resource = mysqli_connect($host,$user,$pass,$db);
	  }

	  //Return Record from Database
	  function get_record ($table, $col, $conditions) 
	  {
		$data = '';
		$query_to_run = "select ".$col." from ".$table." ".$conditions;
		
		$start = mysqli_query($this->db_resource, $query_to_run);
		if($start) {
			if(mysqli_num_rows($start) > 0) {
				$end = mysqli_fetch_assoc($start); //or die(mysql_error());
				$property = mysqli_fetch_field($start);
				$data = $end[$property->name];
				mysqli_free_result($start);
				//error_log("Cached ".$data." for query: ".$query_to_run);
				return $data;
		} else return '';
		} else return mysqli_error($this->db_resource);
	}

	  function unset_all()
	  {
		  foreach ($this as &$value) {
			$value = null;
		  }
	  }
	  
	  function print_all()
	  {
		 foreach ($this as &$value) {
			 print($value."\n");
		  } 
	  }

	  function format_number($number)
	  {
		$number = preg_replace("/[^0-9]/","",$number);
		$number = round($number);
		$country_code = "256";
		$number_len = 12;
		
		if(substr($number,0,strlen($country_code)) == $country_code && strlen($number) == $number_len) return $number;
		else if((strlen($number)+strlen($country_code)) == $number_len) return $country_code.$number;
		
		else return $number;
	  }
	  
	  function DMS2Decimal($degrees = 0, $minutes = 0, $seconds = 0, $direction = 'n') 
	  {
	     //converts DMS coordinates to decimal
	     //returns false on bad inputs, decimal on success
	      
	     //direction must be n, s, e or w, case-insensitive
	     $d = strtolower($direction);
	     $ok = array('n', 's', 'e', 'w');
	      
	     //degrees must be integer between 0 and 180
	     if(!is_numeric($degrees) || $degrees < 0 || $degrees > 180) {
	          throw new Exception("Invalid Degrees");
	     }
	     //minutes must be integer or float between 0 and 59
	     elseif(!is_numeric($minutes) || $minutes < 0 || $minutes > 59) {
	          throw new Exception("Invalid Minutes");
	     }
	     //seconds must be integer or float between 0 and 59
	     elseif(!is_numeric($seconds) || $seconds < 0 || $seconds > 59) {
	          throw new Exception("Invalid Seconds");
	     }
	     elseif(!in_array($d, $ok)) {
	          throw new Exception("Invalid Direction");
	     }
	     else {
	          //inputs clean, calculate
	          $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
	           
	          //reverse for south or west coordinates; north is assumed
	          if($d == 's' || $d == 'w') {
	               $decimal *= -1;
	          }
      	}
      
     	return $decimal;
 	  }
	  
	  function get_suitable_val($number,$max,$min,$to_scan)
	  {
		//$number = substr($number,0,strpos($number,"."));
		$x = "";
		for($i=1;$i<=$to_scan;$i++)
		{
			if(substr($number,0,$i) >= $min && substr($number,0,$i) <= $max)
			{
				$x = substr($number,0,$i);
			} else
			{
				continue;
			}
		}
		return $x;
	  }
	  
	  function data_decode($sensor_data)
	  {
		 $this->unset_all();
		 /*
		   Data Structure: IMEI|Charging_Voltage|Battery_Voltage|Load_Voltage|Light_Intensity|Degrees|N/S|Degrees|E\W|Longitude|Altitude|Timestamp|Device_Signal_Strength|Internal_Battery_Level|Custom_Value
		 */

		$valid_gps = false; 
		$data = explode("|",trim($sensor_data));

		$this->serial_no = $data[0];
		$this->charging_voltage = $data[1];
		$this->battery_voltage = $data[2];
		$this->load_voltage = $data[3];
		$this->light_intensity = $data[4];

		if($data[6] == "N")
		{
			$this->north = $data[5];
			//North is used to calculate the Latitude. Range is 90 to -90
			$degree = $this->get_suitable_val($this->north,90,-90,2);
			$minutes = substr($this->north, $degree);
			if(substr($minutes,0,1) == ".")
			{
				  $minutes = "0".$minutes;
			}

			$this->lat = $this->coords($degree,$minutes,"north");
		}


		if($data[6] == "S")
		{
			$this->south = $data[5];
			//North is used to calculate the Latitude. Range is 90 to -90
			$degree = $this->get_suitable_val($this->south,90,-90,2);
			$minutes = substr($this->south,$degree);
			if(substr($minutes,0,1) == ".")
			{
				  $minutes = "0".$minutes;
			}

			$this->lat = $this->coords($degree,$minutes,"south");
		}

		if($data[8] == "W")
		{
			$this->west = $data[7];
			//East is used to calculate the Longitude. Range is 180 to -180
			$degree = $this->get_suitable_val($this->west,180,-180,3);
			$minutes = substr($this->west,strlen($degree));
			if(substr($minutes,0,1) == ".")
			{
			  $minutes = "0".$minutes;
			}
			
			$this->lng = $this->coords($degree,$minutes,"west");
		}

		if($data[8] == "E")
		{
			$this->east = $data[7];
			//East is used to calculate the Longitude. Range is 180 to -180
			$degree = $this->get_suitable_val($this->east,180,-180,3);
			$minutes = substr($this->east,strlen($degree));
			if(substr($minutes,0,1) == ".")
			{
			  $minutes = "0".$minutes;
			}
			
			$this->lng = $this->coords($degree,$minutes,"east");
		}

		$this->altitude = $data[9];
		$this->device_time = $data[10];
		$this->signal_strength = $data[11];
		$this->internal_battery = $data[12];

	  }
	  
	  function sensor_table($db_link, $imei)
	  {
		 $go = mysqli_query($db_link, "CREATE TABLE `sensor_".$imei."` (
									  `lat` double DEFAULT NULL,
									  `lng` double DEFAULT NULL,
									  `altitude` double DEFAULT NULL,
									  `speed` double DEFAULT NULL,
									  `direction` double DEFAULT NULL,
									  `odometer` double DEFAULT NULL,
									  `engine_state` varchar(50) NOT NULL DEFAULT 'unknown',
									  `sos` enum('1','0') NOT NULL DEFAULT '0',
									  `low_battery` enum('1','0','-1') NOT NULL DEFAULT '-1',
									  `fuel` double DEFAULT NULL,
									  `gsm_signal` double DEFAULT NULL,
									  `power_supply` double DEFAULT NULL,
									  `notification` text,
									  `date` date DEFAULT NULL,
									  `time` time DEFAULT NULL,
									  `unrecognized` text,
									  PRIMARY KEY (`date`,`time`)
									) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
		if($go) return true;
		else 
		{
			$error = mysqli_error($db_link);
			if(substr_count($error,"Table 'sensor_".$imei."' already exists") > 0) return true;
			else return false;
		}
	  }
	  
	  function coords($degrees, $minutes, $direction)
	  {
		$total_seconds = $minutes*60;
		$fraction = $total_seconds/3600;
		if($direction == "west" || $direction == "south")
		{
			return "-".($degrees+$fraction);
		} else
		{
			return ($degrees+$fraction);
		}	
	  }
	  
	  function populate_values($db_link, $table)
	  {
		  $go = mysqli_query($db_link, "insert ignore into `".$table."` ()");
	  }

	  function trigger_device($deviceIMEI, $triggerAction,$expireDate="")
	  {
	  	  switch ($triggerAction) {
	  	  	case 'turn_on':
	  	  		$this->send_sms($this->get_record("device","phone_no","where _status = '1' and imei = '".$deviceIMEI."'"),"START 123456");
	  	  		return true;
	  	  	case 'turn_off':
	  	  		$this->send_sms($this->get_record("device","phone_no","where _status = '1' and imei = '".$deviceIMEI."'"),"STOP 123456");
	  	  		return true;
	  	  		break;
	  	  	case 'timer':
	  	  		break;	
	  	  	default:
	  	  		return false;
	  	  		break;
	  	  }
	  }

	  //Generate stay on token for device. expiry date should be passed as unix timestamp
	  function generateToken($deviceIMEI, $expireDate)
	  {
	  	 $chars = $deviceIMEI.$expireDate;
	    srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;
		while ($i <= 7) {
			$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	  }
	  
	  function send_sms($recipients, $message)
	  {
		require_once('AfricasTalkingGateway.php');
		
		// Specify your login credentials
		// Specify your login credentials
		$username   = "thinvoid";
		$apikey     = "c65c0a6a8b2c75808ed4b0062c709e3b488796a1912cfcf65a3ed6d9632aa1cd";

		// Specify your Africa's Talking phone number in international format
		$from = "+256312319555";
		$to = "";
		// Create a new instance of our awesome gateway class
		$gateway = new AfricasTalkingGateway($username, $apikey);

		$to = "";
		$bits = preg_split("/[^0-9]/", $recipients);
		foreach($bits as $item)
		{
			if(trim($item) != "")
			{
				$to   .= "+".$this->format_number($item).","; 
			}
		}
		$to = substr($to, 0, -1);
		//echo $to; exit;
		try 
		{ 
		  error_log("Sending SMS to ".$to);
		  // Thats it, hit send and we'll take care of the rest. 
		  $results = $gateway->sendMessage($to, $message);
		  //print_r($results); exit;
		  return true;
		}
		catch ( AfricasTalkingGatewayException $e )
		{
		  error_log($e->getMessage());
		  return false;
		}
	
	}
	  
  }
?>