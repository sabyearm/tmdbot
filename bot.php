<?php
function get_api_tmd($lat , $lon){
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://data.tmd.go.th/nwpapi/v1/forecast/location/hourly/at?lat=".$lat."&lon=".$lon."&fields=tc,rain&hour=8&duration=2",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "accept: application/json",
    "authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6Ijc4NTFhYzBmZDhmMTk2YjU0YmMxYWM3Y2ZlODBhODk5OTU5MWZjNzA2OWUzYjBmMjRhZjZhYWI2NDA2NzNiYTk3NmFjZWU3ODBlODVjMjI0In0.eyJhdWQiOiIyIiwianRpIjoiNzg1MWFjMGZkOGYxOTZiNTRiYzFhYzdjZmU4MGE4OTk5NTkxZmM3MDY5ZTNiMGYyNGFmNmFhYjY0MDY3M2JhOTc2YWNlZTc4MGU4NWMyMjQiLCJpYXQiOjE1NjU4NzQzOTgsIm5iZiI6MTU2NTg3NDM5OCwiZXhwIjoxNTk3NDk2Nzk4LCJzdWIiOiIxNzYiLCJzY29wZXMiOltdfQ.j8K9jVu8SrvPTBYaaPYcG3Kf5UxStp7kx9IpLBgLZftIgCwwy8mU70x4pksk5SL17LJN04xGwmLBmXtKtFAfQ8O4V0n4nK-SDG2i5aaXiLPHxy9hR8KI5GPIwp9ayzI6Y-kLU4aa7BotN2UaCFnJwQmyw6lVja24UdvniNfUgB4xpFQP9mDSgVzG_0veefkqeocGnZiUtLvlLtS4eGjB4qzp5w8jMqh2WXRmg9Xia7b33Dh5OQCEWnD-uQYGX8-Ix7v30-B3smQjb-ulrd30tcAcmKxLofTfBwehZirakXMDt-oN0_HqigOBNla69CFm5OJfWdmkkAorWVtSjl4f72xlOEBS-DgXYodtgGQOrDrgFAiEnTqK0AipQwT-KahIpIT7Gv1G9pvFpDp5L-zlCoZaiy0P0IcQKFvsP09BPD020JJUKxHIrO8znOgD3QSR0Q39Y2okBx1WD2nY0yq6zgRRS6IDhmSI-Zbw2Tp9J0x_HjAxmo6RtMqW-Yk7ZosmE0KeY9GHMIcw_fPLSwBPWz2FNkUTgxlwrSP0Yf5Ngzo9gtnwwiOLTfSGLT4UdRq2Q92E_xplwEeAmuXlSkil8zo0fKe1mln2lLxFvCsVd7E2SY3VpaON0-5mYE68AyiEQ_1_fb0rH2E3lRx1gg3qdvYamJD1GL0oTD6aqVMEMDY",
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);
return json_decode($response);
}

$API_URL = 'https://api.line.me/v2/bot/message';
$ACCESS_TOKEN = 'MZo3EBaFykn2d1pjlXB2PngXM8InX+ws+tJjPNPPZZIJqkPoGBWbzLO7WViCPfCpkKL6sLZA1P5SAt76PKRfI5Nd/1w5QpEucb1L1l/GAl4HfvAMEUR5go+M7zhWt6ePPauC09nWU28qeX4Xw9V9vAdB04t89/1O/w1cDnyilFU=';// ใส่ Channel Secret
$channelSecret = 'b08ac2004ac851c096c0214d6b20111e';


$POST_HEADER = array('Content-Type: application/json', 'Authorization: Bearer ' . $ACCESS_TOKEN);

$request = file_get_contents('php://input');   // Get request content
$request_array = json_decode($request, true);   // Decode JSON to Array



if ( sizeof($request_array['events']) > 0 ) {

    foreach ($request_array['events'] as $event) {

        $reply_message = '';
        $reply_token = $event['replyToken'];
	
	$input_message = $event['message'];
        $text = $input_message['text'];
	if($input_message['type']=='location'){
		$latitude = $input_message['latitude'];
		$longitude = $input_message['longitude'];
		$forecast_data = ((get_api_tmd($latitude , $longitude))->WeatherForecasts)[0]->forecasts;
		$text = '';
		for($i=0 ; $i<sizeof($forecast_data) ; $i++){
			$ts = $forecast_data[$i]->time;
			$data = $forecast_data[$i]->data;
			$Temp = $data->tc;
			$Rain = $data->rain;
			$text .=  'เวลา'.$ts.'\nอุณหภูมิ = '.$ts.'องศาเซลเซียส\nปริมาณฝน = '.$Rain.'mm\n----------\n';
		}
		$data = [
		    'replyToken' => $reply_token,
		    // 'messages' => [['type' => 'text', 'text' => json_encode($request_array) ]]  Debug Detail message
		    'messages' => [['type' => 'text', 'text' => $text ]]
		];
		
	}
	else{
		$data = [
		    'replyToken' => $reply_token,
		    // 'messages' => [['type' => 'text', 'text' => json_encode($request_array) ]]  Debug Detail message
		    'messages' => [['type' => 'text', 'text' => 'ก็แชร์โลเกชั่นมาดิวะสัด' ]]
		];
	}
        $post_body = json_encode($data, JSON_UNESCAPED_UNICODE);

        $send_result = send_reply_message($API_URL.'/reply', $POST_HEADER, $post_body);

        echo "Result: ".$send_result."\r\n";
    }
}

echo "OK";




function send_reply_message($url, $post_header, $post_body)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $post_header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

?>
