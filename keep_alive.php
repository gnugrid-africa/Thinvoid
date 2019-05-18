<?php date_default_timezone_set("Africa/Kampala"); ?>
<?php
$status_files = array('data_logger.php', 'decode_sensor_data.php');

exec("ps aux | grep php", $content);

for($i=0;$i<sizeof($status_files);$i++)
{
         foreach($content as $item)
         {
             if(trim($item) != "" && trim($status_files[$i]) != "")
			 {
			 	if(substr_count($item,$status_files[$i]) > 0)
             	{
                	$status_files[$i] = '';
             	}
			 }
         }
}

foreach($status_files as $n)
{
	
        if(trim($n) != "")
        {
                unlink("/root/".$n.".out");
                print("Starting ".$n." . . .\n");
                popen("nohup php /root/".$n." > /root/".$n.".out &","r");
        }
        else
        {
                //Clean File
                $fp = fopen("/root/".$n.".out", "w");
                fwrite($fp, "");
                fclose($fp);
        }
}
?>
