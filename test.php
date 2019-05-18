<?php require_once("extract_sensor_data.php"); ?>
<?php
  $api = new extract_sensor_data();

  //$api->send_sms("256782157074","Testing","6115");
  //$api->trigger_device("00211676","turn_on");

  $api->trigger_device("00211676","turn_off");
?>