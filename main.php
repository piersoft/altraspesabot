n<?php
/**
* Telegram Bot example for AltraSpesa Lic. MIT per un'idea di Tommaso Conese
* @author Francesco Piero Paolicelli @piersoft
*/
include("settings_t.php");
include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");

	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

 function shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	//if (strpos($text,'@altraspesabot') !== false) $text=str_replace("@altraspesabot ","",$text);
	if ($text == "/start" || $text == "Informazioni") {
		$img = curl_file_create('altraspesa.png','image/png');
		$contentp = array('chat_id' => $chat_id, 'photo' => $img);
		$telegram->sendPhoto($contentp);
		$reply = "Il cibo... non si butta. Con questo servizio potrai contribuire a ridurre lo spreco alimentare e a trovare offerte da parte di esercenti e produttori di cibo. Puoi vedere l'elenco odierno di offerte, cercare per parola chiave anteponendo il carattere - (meno), puoi digitare il nome del Comune o infine cliccare sulla graffetta (📎) e poi 'posizione'. In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot è stato realizzato da @piersoft per AltraSpesa e potete migliorare il codice sorgente con licenza MIT che trovate su https://github.com/piersoft/altraspesabot. La propria posizione viene ricercata grazie al geocoder di openStreetMap con Lic. odbl.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ",info_newuser," .$chat_id. "\n";
		file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);
		$this->create_keyboard_temp($telegram,$chat_id);
		exit;
		} elseif ($text == "Comune") {
			$reply = "Digita direttamente il nome del Comune.";
			$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
		//	$log=$today. ";new chat started;" .$chat_id. "\n";
			$this->create_keyboard_temp($telegram,$chat_id);
			exit;
			} elseif ($text == "Ricerca") {
				$reply = "Clicca sulla graffetta (📎) e poi 'posizione'";
				$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
							$this->create_keyboard_temp($telegram,$chat_id);
			//	$log=$today. ";new chat started;" .$chat_id. "\n";
				exit;
			}elseif ($text == "offerte") {
				$reply = "Clicca sulla graffetta (📎) e poi 'posizione'";
				$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
							$this->create_keyboard_temp($telegram,$chat_id);
			//	$log=$today. ";new chat started;" .$chat_id. "\n";
				exit;
			}
			elseif($location != null){
			$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;
		}elseif(strpos($text,'/') === false){

			if(strpos($text,'?') !== false || strpos($text,'-') !== false){
							$text=str_replace("?","",$text);
							$text=str_replace("-","",$text);
							$location="Sto cercando le offerte contenenti: ".$text;
							$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
							$telegram->sendMessage($content);
							$text=str_replace(" ","%20",$text);
							$text=strtoupper($text);
					//		$urlgd  ="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20A%2CC%2CD%2CG%2CH%2CP%2CL%2CM%2CO%2CJ%2CK%20WHERE%20upper(C)%20like%20%27%25";
					$urlgd  ="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20upper(N)%20like%20%27%25";

							$urlgd .=$text;
							$urlgd .="%25%27%20AND%20O%20IS%20NOT%20NULL&key=1tvU2GOOix8YfmtPADXuwGPLrR9k3p1VVvbCMdO70A3A&gid=2109201553";
							$inizio=1;
							$homepage ="";

							$csv = array_map('str_getcsv',file($urlgd));
							$csv=str_replace(array("\r", "\n"),"",$csv);

							$count = 0;
							foreach($csv as $data=>$csv1){
								$count = $count+1;
							}
							if ($count ==0){
									$location="Nessun risultato trovato";
									$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
									$telegram->sendMessage($content);
								}
								if ($count >40){
										$location="Troppe risposte per il criterio scelto. Ti preghiamo di fare una ricerca più circoscritta";
										$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
										$telegram->sendMessage($content);
										exit;
									}
									$result=0;
									date_default_timezone_set('Europe/Rome');
									date_default_timezone_set("UTC");
									$today=time();
							for ($i=$inizio;$i<$count;$i++){
								$html =str_replace("/","-",$csv[$i][10]);
								$from = strtotime($html);
								$html1 =str_replace("/","-",$csv[$i][11]);
								$to = strtotime($html1);

								if ($today >= $from && $today <= $to) {

								//$homepage .="da: ".$from." a: ".$to." con oggi: ".$today."\n";
								$homepage .="\n";
								if($csv[$i][13] !=NULL) $homepage .="Descrizione: ".$csv[$i][13]."\n";
								if($csv[$i][1] !=NULL)$homepage .="Offerta inserita da: ".$csv[$i][1]."\n";
								if($csv[$i][2] !=NULL)$homepage .="Email: ".$csv[$i][2]."\n";
								if($csv[$i][3] !=NULL)$homepage .="Telefono: ".$csv[$i][3]."\n";
								if($csv[$i][4] !=NULL)$homepage .="Tipologia: ".$csv[$i][4]."\n";
								if($csv[$i][5] !=NULL)  $homepage .="Immagine: ".$csv[$i][5]."\n";
								if($csv[$i][6] !=NULL)$homepage .="Quantità: ".$csv[$i][6]."\n";
								if($csv[$i][7] !=NULL)$homepage .="Valore: ".$csv[$i][7]."\n";
								if($csv[$i][12] !=NULL)$homepage .="Comune: ".$csv[$i][12]."\n";
								if($csv[$i][10] !=NULL) $homepage .="Inizio validità: ".$csv[$i][10]."\n";
								if($csv[$i][11] !=NULL)  $homepage .="Fine validità: ".$csv[$i][11]."\n";
								$homepage .="____________\n";

								}


							}
							$chunks = str_split($homepage, self::MAX_LENGTH);
							foreach($chunks as $chunk) {
								$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
								$telegram->sendMessage($content);
									}
											$log=$today. ",ricerca,".$text."," .$chat_id. "\n";
											file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);
											$this->create_keyboard_temp($telegram,$chat_id);
											exit;

				}else{


			$string=0;

				$location="Sto cercando le offerte nella località: ".$text;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$string=0;

					$text=str_replace(" ","%20",$text);
					$text=strtoupper($text);
					$urlgd  ="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20upper(M)%20contains%20%27";
					$urlgd .=$text;
					$urlgd .="%27&key=1tvU2GOOix8YfmtPADXuwGPLrR9k3p1VVvbCMdO70A3A&gid=2109201553";

			  $inizio=1;
			  $homepage ="";
		  	$csv = array_map('str_getcsv',file($urlgd));
	  		$count = 0;
				foreach($csv as $data=>$csv1){
					$count = $count+1;
				}
			if ($count ==0)
			{
						$location="Nessuna offerta trovata";
						$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);
						$this->create_keyboard_temp($telegram,$chat_id);
						exit;
			}	elseif ($count >100)
			{
						$location="Troppi risultati, impossibile visualizzazione";
						$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);
						$this->create_keyboard_temp($telegram,$chat_id);
						exit;
			}
			function decode_entities($text)
			{

							$text=htmlentities($text, ENT_COMPAT,'ISO-8859-1', true);
							$text= preg_replace('/&#(\d+);/me',"chr(\\1)",$text); #decimal notation
							$text= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$text);  #hex notation
							$text= html_entity_decode($text,ENT_COMPAT,"UTF-8"); #NOTE: UTF-8 does not work!

							return $text;
			}

			$result=0;
			date_default_timezone_set('Europe/Rome');
			date_default_timezone_set("UTC");
			$today=time();
	//echo $count;
	//  $count=3;


	for ($i=$inizio;$i<$count;$i++){

	$html =str_replace("/","-",$csv[$i][10]);
	$from = strtotime($html);
	$html1 =str_replace("/","-",$csv[$i][11]);
	$to = strtotime($html1);


	if ($today >= $from && $today <= $to) {

	//$homepage .="da: ".$from." a: ".$to." con oggi: ".$today."\n";
	$homepage .="\n";
	if($csv[$i][13] !=NULL) $homepage .="Descrizione: ".$csv[$i][13]."\n";
	if($csv[$i][1] !=NULL)$homepage .="Offerta inserita da: ".$csv[$i][1]."\n";
	if($csv[$i][2] !=NULL)$homepage .="Email: ".$csv[$i][2]."\n";
	if($csv[$i][3] !=NULL)$homepage .="Telefono: ".$csv[$i][3]."\n";
	if($csv[$i][4] !=NULL)$homepage .="Tipologia: ".$csv[$i][4]."\n";
	if($csv[$i][5] !=NULL)  $homepage .="Immagine: ".$csv[$i][5]."\n";
	if($csv[$i][6] !=NULL)$homepage .="Quantità: ".$csv[$i][6]."\n";
	if($csv[$i][7] !=NULL)$homepage .="Valore: ".$csv[$i][7]."\n";
	if($csv[$i][12] !=NULL)$homepage .="Comune: ".$csv[$i][12]."\n";
	if($csv[$i][10] !=NULL) $homepage .="Inizio validità: ".$csv[$i][10]."\n";
	if($csv[$i][11] !=NULL)  $homepage .="Fine validità: ".$csv[$i][11]."\n";
	$homepage .="____________\n";

	}
	}


		$chunks = str_split($homepage, self::MAX_LENGTH);

		foreach($chunks as $chunk)
		  {
		$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);

		$countff++;
			}

		$log=$today. ",comune," .$chat_id. "\n";
		file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);
		$this->create_keyboard_temp($telegram,$chat_id);
		exit;

}
$this->create_keyboard_temp($telegram,$chat_id);
exit;
}

}

	function create_keyboard_temp($telegram, $chat_id)
	 {
			 $option = array(["Comune","Ricerca"],["Informazioni"]);
			 $keyb = $telegram->buildKeyBoard($option, $onetime=false);
			 $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Fai una ricerca, digita un Comune/Parola oppure invia la tua posizione tramite la graffetta (📎)]");
			 $telegram->sendMessage($content);
	 }



function location_manager($telegram,$user_id,$chat_id,$location)
	{

			$lon=$location["longitude"];
			$lat=$location["latitude"];
			$r=1;
			$response=$telegram->getData();
			$response=str_replace(" ","%20",$response);

				$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
				$json_string = file_get_contents($reply);
				$parsed_json = json_decode($json_string);
				//var_dump($parsed_json); debug
				$comune="";
				$temp_c1 =$parsed_json->{'display_name'};

				if ($parsed_json->{'address'}->{'town'}) {
					$temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'town'};
					$comune .=$parsed_json->{'address'}->{'town'};
				}else 	$comune .=$parsed_json->{'address'}->{'city'};

				if ($parsed_json->{'address'}->{'village'}) $comune .=$parsed_json->{'address'}->{'village'};
				$location="Sto cercando le offerte (fino a 20) a \"".$comune."\" tramite le coordinate che hai inviato: ".$lat.",".$lon;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

			  $alert="";

			//	echo $comune; debug
			$comune=str_replace(" ","%20",$comune);
			$comune=strtoupper($comune);
			$urlgd  ="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20upper(M)%20contains%20%27";
			$urlgd .=$comune;
			$urlgd .="%27&key=1tvU2GOOix8YfmtPADXuwGPLrR9k3p1VVvbCMdO70A3A&gid=2109201553";

			$csv = array_map('str_getcsv',file($urlgd));
			$count = 0;
			foreach($csv as $data=>$csv1){
				$count = $count+1;
			}
		if ($count ==0 )
		{
					$location="Nessuna offerta trovata";
					$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					$this->create_keyboard_temp($telegram,$chat_id);
					exit;
		}	elseif ($count >100)
		{
					$location="Troppi risultati, impossibile visualizzazione";
					$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					$this->create_keyboard_temp($telegram,$chat_id);
					exit;
		}

			$inizio=1;
			$homepage ="";

			$latidudine="";
			$longitudine="";
			$data=0.0;
			$data1=0.0;
			//$count = 0; debug
			$dist=0.0;
				$paline=[];
				$distanza=[];
				$countf = 0 ;
				date_default_timezone_set('Europe/Rome');
				date_default_timezone_set("UTC");
				$today=time();

			for ($i=$inizio;$i<$count;$i++){
				$html =str_replace("/","-",$csv[$i][10]);
				$from = strtotime($html);
				$html1 =str_replace("/","-",$csv[$i][11]);
				$to = strtotime($html1);


				if ($today >= $from && $today <= $to) {
				$homepage .="\n";

				$lat10=floatval($csv[$i][8]);
				$long10=floatval($csv[$i][9]);
				$theta = floatval($lon)-floatval($long10);
				$dist =floatval( sin(deg2rad($lat)) * sin(deg2rad($lat10)) +  cos(deg2rad($lat)) * cos(deg2rad($lat10)) * cos(deg2rad($theta)));
				$dist = floatval(acos($dist));
				$dist = floatval(rad2deg($dist));
				$miles = floatval($dist * 60 * 1.1515 * 1.609344);

				if ($miles >=1){
					$data1 =number_format($miles, 2, '.', '');
					$data =number_format($miles, 2, '.', '')." Km";
				} else {
					$data =number_format(($miles*1000), 0, '.', '')." mt";
					$data1 =number_format(($miles*1000), 0, '.', '');
				}
				$csv[$i][100]= array("distance" => "value");

				$csv[$i][100]= $data1;
				$csv[$i][101]= array("distancemt" => "value");

				$csv[$i][101]= $data;
				$t=floatval($r*5000);


						if ($data < $t)
						{

							$distanza[$i]['distanza'] =$csv[$i][100];
							$distanza[$i]['distanzamt'] =$csv[$i][101];
							$distanza[$i]['lat'] =$csv[$i][8];
							$distanza[$i]['lon'] =$csv[$i][9];
							$distanza[$i]['descrizione'] =$csv[$i][13];
							$distanza[$i]['tipologia'] =$csv[$i][4];
							$distanza[$i]['nome'] =$csv[$i][1];
							$distanza[$i]['email'] =$csv[$i][2];
							$distanza[$i]['tel'] =$csv[$i][3];
							$distanza[$i]['foto'] =$csv[$i][5];
							$distanza[$i]['valore'] =$csv[$i][7];
							$distanza[$i]['city'] =$csv[$i][12];
							$distanza[$i]['inizio'] =$csv[$i][10];
							$distanza[$i]['fine'] =$csv[$i][11];
							$distanza[$i]['qt'] =$csv[$i][6];
				$countf++;
						}
			}

			$temp_c1="";
			sort($distanza);
			for ($f=0;$f<20;$f++){

					if($distanza[$f]['descrizione'] !=NULL)				$temp_c1 .="\nDescrizione: ".$distanza[$f]['descrizione'];
					if($distanza[$f]['nome'] !=NULL)						$temp_c1 .="\nOfferta inserita da: ".$distanza[$f]['nome'];
					if($distanza[$f]['email'] !=NULL)					$temp_c1 .="\nEmail: ".$distanza[$f]['email'];
					if($distanza[$f]['tel'] !=NULL)					$temp_c1 .="\nTelefono: ".$distanza[$f]['tel'];
					if($distanza[$f]['tipologia'] !=NULL)			$temp_c1 .="\nTipologia: ".$distanza[$f]['tipologia'];
					if($distanza[$f]['foto'] !=NULL)					$temp_c1 .="\nFoto: ".$distanza[$f]['foto'];
					if($distanza[$f]['qt'] !=NULL)				$temp_c1 .="\nQuantità: ".$distanza[$f]['qt'];
					if($distanza[$f]['valore'] !=NULL)				$temp_c1 .="\nValore: ".$distanza[$f]['valore'];
					if($distanza[$f]['city'] !=NULL)				$temp_c1 .="\nComune: ".$distanza[$f]['city'];
					if($distanza[$f]['inizio'] !=NULL)					$temp_c1 .="\nInizio validità: ".$distanza[$f]['inizio'];
					if($distanza[$f]['fine'] !=NULL)					$temp_c1 .="\nFine validità: ".$distanza[$f]['fine'];
					if($distanza[$f]['lat'] !=NULL){
						$temp_c1 .="\nVisualizza su Openstreetmap:\n";
						$temp_c1 .= "http://www.openstreetmap.org/?mlat=".$distanza[$f]['lat']."&mlon=".$distanza[$f]['lon']."#map=19/".$distanza[$f]['lat']."/".$distanza[$f]['lon']."\n";
						$temp_c1 .="Visualizza su Google App:\nhttp://maps.google.com/maps?q=".$distanza[$f]['lat'].",".$distanza[$f]['lon'];

				}

				if($distanza[$f]['distanzamt'] !=NULL)			$temp_c1 .="\nDista: ".$distanza[$f]['distanzamt'];

				if($distanza[$f]['descrizione	'] !=NULL)						$temp_c1 .="\n_____________\n";


			}
		}
			$chunks = str_split($temp_c1, self::MAX_LENGTH);
		  foreach($chunks as $chunk) {

		 		 $content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_to_message_id' =>$bot_request_message_id,'disable_web_page_preview'=>true);
		 		 $telegram->sendMessage($content);

		  }

			$longUrl="http://www.piersoft.it/altraspesabot/locator.php?lat=".$lat."&lon=".$lon."&r=1";
			$apiKey = API;

			$postData = array('longUrl' => $longUrl, 'key' => $apiKey);
			$jsonData = json_encode($postData);

			$curlObj = curl_init();

			curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
			curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curlObj, CURLOPT_HEADER, 0);
			curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
			curl_setopt($curlObj, CURLOPT_POST, 1);
			curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

			$response = curl_exec($curlObj);

			// Change the response json string to object
			$json = json_decode($response);

			curl_close($curlObj);
			//  $reply="Puoi visualizzarlo su :\n".$json->id;
			$shortLink = get_object_vars($json);
			//return $json->id;
if ($count !=0){
			$mappa ="\nVisualizza tutte le offerte odierne nella tua su mappa :\n".$shortLink['id'];
			$content = array('chat_id' => $chat_id, 'text' => $mappa,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
}
		 	$today = date("Y-m-d H:i:s");

		 	$log=$today. ",location sent," .$chat_id. "\n";
		 	$this->create_keyboard_temp($telegram,$chat_id);
		 	exit;

	}


}

?>
