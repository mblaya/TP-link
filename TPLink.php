<?php

/*
  Plugin Name: TPLink
  Plugin URI: http://blaya.club
  Description: TPLink
  Author: Mariano Blaya
  Author URI: http://blaya.club
  License: GPLv2+
  Text Domain: TPLink
*/

/*
*********************************
*/
function TPLink_authorize( $username, $password, $UUID )
{
	if( $username=="" || $password=="" )
		return "";
	
	$url = 'https://eu-wap.tplinkcloud.com';
	$curl = curl_init( $url );
	
	$content = array (
		'method' => 'login',
		'params' => array (
			'appType' => 'Kasa_Android',
			'cloudUserName' => $username,
			'cloudPassword' => $password,
			'terminalUUID' => ''
			)
        	);
	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($content) );
	curl_setopt($curl, CURLOPT_VERBOSE, true);	
	$verbose = fopen('smarthome.log', 'w+');
	curl_setopt( $curl, CURLOPT_STDERR, $verbose);
	curl_setopt( $curl, CURLOPT_HEADER, false);
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array("Content-type: application/json") );
//	curl_setopt( $curl, CURLOPT_POST, true );

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ( $status != 200 ) {
    		die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}

	curl_close($curl);
	$response = json_decode($json_response, true);
	fclose( $verbose );
	return $response['result']['token'];
}

/*
*********************************
*/
function TPLink_getDevices( $token )
{
	if( $token=="" )
		return "";
	$url = 'https://eu-wap.tplinkcloud.com?token=';
	$url .= $token;
	
	$curl = curl_init( $url );
	
	$content = array (
		'method' => 'getDeviceList'
		);

	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($content) );
	curl_setopt($curl, CURLOPT_VERBOSE, true);	
	$verbose = fopen('smarthome.log', 'w+');
	curl_setopt( $curl, CURLOPT_STDERR, $verbose);
	curl_setopt( $curl, CURLOPT_HEADER, false);
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array("Content-type: application/json") );
//	curl_setopt( $curl, CURLOPT_POST, true );

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ( $status != 200 ) {
    		die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}

	curl_close($curl);
	$response = json_decode($json_response, true);
	
	fclose( $verbose );
 
	return $response['result']['deviceList'];
}

/*
*********************************
*/
function TPLink_showDevices( $token, $devices )
{
	if( $devices == NULL )
		return ;
	$count = 0;
	print( '<table><th>ID</th><th>Name</th><th>Model</th><th>Model</th><th>Status</th>' );
	foreach( $devices as $device )
	{
		$status = TPLink_getDeviceStatus( $token, $device['deviceId'] );
		switch( $status ) {
			case NULL: $status = "OFFLINE"; break;
			case 1: $status = "ON"; break;
			case 0: $status = "OFF"; break;
		}
		
		print( '<tr>' .
			'<td>' . $count . '</td>' .
			'<td>' . $device['alias'] . '</td>' .
			'<td>' . $device['deviceModel'] . '</td>' .
			'<td>' . $device['deviceName']. '</td>' .
			'<td>' . $status . '</td>' .
			'</tr>'
			);
		$count ++;
	}
	print '</table>';
}


/*
*********************************
*/
function TPLink_getDeviceStatus( $token, $deviceID )
{
	if( $token=="" )
		return "";
	$url = 'https://eu-wap.tplinkcloud.com?token=';
	$url .= $token;
	
	$curl = curl_init( $url );
	
	$content = array ( 'method' => 'passthrough',
		"params" => array( "deviceId" => $deviceID,
			"requestData" => json_encode(array("system" => array("get_sysinfo" => null,),))));

	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($content) );
	curl_setopt($curl, CURLOPT_VERBOSE, true);	
	$verbose = fopen('smarthome.log', 'w+');
	curl_setopt( $curl, CURLOPT_STDERR, $verbose);
	curl_setopt( $curl, CURLOPT_HEADER, false);
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array("Content-type: application/json") );
//	curl_setopt( $curl, CURLOPT_POST, true );

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ( $status != 200 ) {
    		die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}

	curl_close($curl);
	$response = json_decode($json_response, true);	

//	print_r( $response['result']['responseData'] );
	$found = preg_match( '/"relay_state":([0-9])/i', $response['result']['responseData'], $matches );
	if( $found == 0 )
		$found = preg_match( '/"on_off":([0-9])/i', $response['result']['responseData'], $matches );

	fclose( $verbose );
 
	return $matches[1];
}

/*
*********************************
*/
function TPLink_getDeviceType( $token, $deviceID )
{
	if( $token=="" )
		return "";
	$url = 'https://eu-wap.tplinkcloud.com?token=';
	$url .= $token;

	$curl = curl_init( $url );
	
	$content = array ( 'method' => 'passthrough',
			"params" => array( "deviceId" => $deviceID,
			"requestData" => json_encode(array("system" => array("get_sysinfo" => null,),))));

	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($content) );
	curl_setopt($curl, CURLOPT_VERBOSE, true);	
	$verbose = fopen('smarthome.log', 'w+');
	curl_setopt( $curl, CURLOPT_STDERR, $verbose);
	curl_setopt( $curl, CURLOPT_HEADER, false);
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array("Content-type: application/json") );	

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ( $status != 200 ) {
    		die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}

	curl_close($curl);	
	$response = json_decode($json_response, true);
//	print_r( $response );
	$found = preg_match( '/"mic_type":"([^"]*)"/i', $response['result']['responseData'], $matches );
	if( $found == 0 )
		$found = preg_match( '/"type":"([^"]*)"/i', $response['result']['responseData'], $matches );	
	
//	print_r( $matches );
	return $matches[1];	
}


/*
*********************************
*/
function TPLink_setDeviceStatus( $token, $deviceID, $newStatus )
{
	if( $token=="" )
		return "";
	$url = 'https://eu-wap.tplinkcloud.com?token=';
	$url .= $token;
	
	$deviceType = TPLink_getDeviceType( $token, $deviceID );
//	print "Device type = " . $deviceType . "<br>";
	
	if( $deviceType == "IOT.SMARTPLUGSWITCH" )	
		$content = array ( 'method' => 'passthrough',
			"params" => array( "deviceId" => $deviceID,
				"requestData" => "{\"system\":{\"set_relay_state\":{\"state\": $newStatus }}}"
				)
			);
	else
		$content = array ( 'method' => 'passthrough',
			"params" => array( "deviceId" => $deviceID,
				"requestData" => "{\"smartlife.iot.smartbulb.lightingservice\":{ \"transition_light_state\" : {\"on_off\": $newStatus, \"transition_period\": 0} } }"
				)
			);
		
	$curl = curl_init( $url );
// print_r( json_encode($content) );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($content) );
	curl_setopt($curl, CURLOPT_VERBOSE, true);	
	$verbose = fopen('smarthome.log', 'w+');
	curl_setopt( $curl, CURLOPT_STDERR, $verbose);
	curl_setopt( $curl, CURLOPT_HEADER, false);
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array("Content-type: application/json") );
//	curl_setopt( $curl, CURLOPT_POST, true );

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ( $status != 200 ) {
    		die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}

	curl_close($curl);
	$response = json_decode($json_response, true);
// print_r( $response );	
	fclose( $verbose );
 
	return $response;
}

?>