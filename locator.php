  <?php


//file di test
$lat=$_GET["lat"];
$lon=$_GET["lon"];
$r=$_GET["r"];

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

$alert="";

//	echo $comune; debug
$comune=str_replace(" ","%20",$comune);
$comune=strtoupper($comune);
$urlgd  ="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20upper(M)%20contains%20%27";
$urlgd .=$comune;
$urlgd .="%27%20AND%20O%20IS%20NOT%20NULL&key=1tvU2GOOix8YfmtPADXuwGPLrR9k3p1VVvbCMdO70A3A&gid=2109201553";


//$url ="https://docs.google.com/spreadsheets/d/1x84pu3KF1_II7R8jFGwfzQfY33fOujidBqu_9lGUU_4/pub?gid=0&single=true&output=csv";
$inizio=1;
$homepage ="";
//  echo $url;
$csv = array_map('str_getcsv', file($urlgd));
$latidudine="";
$longitudine="";
$data=0.0;
$data1=0.0;
$count = 0;
$dist=0.0;
  $paline=[];
  $distanza=[];
  date_default_timezone_set('Europe/Rome');
  date_default_timezone_set("UTC");
  $today=time();

foreach($csv as $data=>$csv1){
  $count = $count+1;
}

//$count=5;

//  echo $count;
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
//echo $miles;

  if ($miles >=1){
$data1 =number_format($miles, 2, '.', '');
    $data =number_format($miles, 2, '.', '')." Km";
  } else {
    $data =number_format(($miles*1000), 0, '.', '')." mt";
$data1 =number_format(($miles*1000), 0, '.', '');
  }
  $csv[$i][100]= array("distance" => "value");

  $csv[$i][100]= $dat1;
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

      }
}

}
//echo $homepage;

sort($distanza);

$file1 = "db/mappaf.json";
$original_data="";


$dest1 = fopen($file1, 'w');

//$geostring=geoJson($original_json_string);

$original_data = json_decode($distanza[$tt], true);
if(empty($distanza))
{

  echo "<script type='text/javascript'>alert('Non ci sono fermate vicino alla tua posizione');</script>";

}
$features = array();

foreach($distanza as $key => $value) {
//  var_dump($value);
    $features[] = array(
            'type' => 'Feature',
            'geometry' => array('type' => 'Point', 'coordinates' => array((float)$value['lon'],(float)$value['lat'])),
            'properties' => array('nome' => $value['nome'], 'city' => $value['city'],'tel' => $value['tel'],'email' => $value['email'],'telefono' => $value['tel'],'distanza' => $value['distanzamt'],'foto' => $value['foto'],'descrizione' => $value['descrizione'],'inizio' => $value['inizio'],'fine' => $value['fine'],'qt' => $value['qt'],'tipologia' => $value['tipologia']),
            );
    };

  $allfeatures = array('type' => 'FeatureCollection', 'features' => $features);

$geostring =json_encode($allfeatures, JSON_PRETTY_PRINT);

//echo $geostring;
fputs($dest1, $geostring);


?>

<!DOCTYPE html>
<html lang="it">
  <head>
  <title>AltraSpesa</title>
  <link rel="stylesheet" href="http://necolas.github.io/normalize.css/2.1.3/normalize.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.5/leaflet.css" />
  <link rel="stylesheet" href="MarkerCluster.css" />
  <link rel="stylesheet" href="MarkerCluster.Default.css" />
  <meta property="og:image" content="http://www.piersoft.it/altraspesabot/altraspesa.png"/>
  <script src="http://cdn.leafletjs.com/leaflet-0.7.5/leaflet.js"></script>
  <script src="leaflet.markercluster.js"></script>
  <script type="text/javascript" src="csvjson.js" ></script>
<script type="text/javascript">

function microAjax(B,A){this.bindFunction=function(E,D){return function(){return E.apply(D,[D])}};this.stateChange=function(D){if(this.request.readyState==4 ){this.callbackFunction(this.request.responseText)}};this.getRequest=function(){if(window.ActiveXObject){return new ActiveXObject("Microsoft.XMLHTTP")}else { if(window.XMLHttpRequest){return new XMLHttpRequest()}}return false};this.postBody=(arguments[2]||"");this.callbackFunction=A;this.url=B;this.request=this.getRequest();if(this.request){var C=this.request;C.onreadystatechange=this.bindFunction(this.stateChange,this);if(this.postBody!==""){C.open("POST",B,true);C.setRequestHeader("X-Requested-With","XMLHttpRequest");C.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");C.setRequestHeader("Connection","close")}else{C.open("GET",B,true)}C.send(this.postBody)}};

</script>
  <style>
  #mapdiv{
        position:fixed;
        top:0;
        right:0;
        left:0;
        bottom:0;
}
div.circlered {
	/* IE10 */
background-image: -ms-linear-gradient(top right, red 0%, black 100%);

/* Mozilla Firefox */
background-image: -moz-linear-gradient(top right, red 0%, black 100%);

/* Opera */
background-image: -o-linear-gradient(top right, red 0%, black 100%);

/* Webkit (Safari/Chrome 10) */
background-image: -webkit-gradient(linear, right top, left bottom, color-stop(0, red), color-stop(1,black));

/* Webkit (Chrome 11+) */
background-image: -webkit-linear-gradient(top right, red 0%, black 100%);

/* Regola standard */
background-image: linear-gradient(top right, red 0%, black 100%);
    background-color: red;
    border-color: black;
    border-radius: 50px;
    border-style: solid;
    border-width: 1px;
	  font-color: white;
    width:15px;
    height:15px;
}
#infodiv{
background-color: rgba(255, 255, 255, 0.95);

font-family: Helvetica, Arial, Sans-Serif;
padding: 2px;


font-size: 10px;
bottom: 13px;
left:0px;


max-height: 50px;

position: fixed;

overflow-y: auto;
overflow-x: hidden;
}
#loader {
    position:absolute; top:0; bottom:0; width:100%;
    background:rgba(255, 255, 255, 1);
    transition:background 1s ease-out;
    -webkit-transition:background 1s ease-out;
}
#loader.done {
    background:rgba(255, 255, 255, 0);
}
#loader.hide {
    display:none;
}
#loader .message {
    position:absolute;
    left:50%;
    top:50%;
}
</style>
  </head>

<body>

  <div data-tap-disabled="true">

  <div id="mapdiv"></div>
<div id="infodiv" style="leaflet-popup-content-wrapper">
  <p><b>Altra Spesa<br></b>
  Mappa con offerte odierne attorno alla tua posizione powered by @piersoft</p>
</div>
<div id='loader'><span class='message'>loading</span></div>
</div>
  <script type="text/javascript">
  function convert() {
      var input = document.getElementById("input").innerHTML;

      var output_json = csvjson.csv2json(input, {
        delim: ",",
        textdelim: "\""
      });
      console.log("Converted CSV to JSON:", output_json);

      var output_csv = csvjson.json2csv(output_json, {
        delim: ",",
        textdelim: "\""
      });
      console.log("Converted JSON to CSV:", output_csv);

      document.getElementById("output").innerHTML = output_csv;
    }
		var lat=38.0907,
        lon=15.7207,
        zoom=12;
        var osm = new L.TileLayer('http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {minZoom: 0, maxZoom: 20, attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>, Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>'});

  //      var osm = new L.TileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {maxZoom: 20, attribution: 'Map Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'});
		var mapquest = new L.TileLayer('http://otile{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png', {subdomains: '1234', maxZoom: 18, attribution: 'Map Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'});
    var realvista = L.tileLayer.wms("http://213.215.135.196/reflector/open/service?", {
        layers: 'rv1',
        format: 'image/jpeg',attribution: '<a href="http://www.realvista.it/website/Joomla/" target="_blank">RealVista &copy; CC-BY Tiles</a> | <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'
      });


        var map = new L.Map('mapdiv', {
                    editInOSMControl: true,
            editInOSMControlOptions: {
                position: "topright"
            },
            center: new L.LatLng(lat, lon),
            zoom: zoom,
            layers: [osm]
        });
        var markeryou = L.marker([parseFloat('<?php printf($_GET['lat']); ?>'), parseFloat('<?php printf($_GET['lon']); ?>')]).addTo(map);
        markeryou.bindPopup("<b>Sei qui</b>");
        var baseMaps = {
    "Satellite": realvista,
    "Humanitarian": osm,
    "Mapquest Open": mapquest
        };
        L.control.layers(baseMaps).addTo(map);

       var ico=L.icon({iconUrl:'circle.png', iconSize:[20,20],iconAnchor:[0,0]});
    //   var markers = L.markerClusterGroup({spiderfyOnMaxZoom: true, showCoverageOnHover: true,zoomToBoundsOnClick: true});
       var markers=L.featureGroup();
        function loadLayer(url)
        {
          var text="";
                var myLayer = L.geoJson(url,{
                        onEachFeature:function onEachFeature(feature, layer) {
                                if (feature.properties && feature.properties.descrizione) {
                                  console.log(feature.properties.foto);
                              //    console.log(feature.properties);
                                  text ="<div>Inserita da: "+feature.properties.nome;
                                  text +="</br>Comune: "+feature.properties.city;
                                  text +="</br>Distanza: "+feature.properties.distanza;
                                if (feature.properties && feature.properties.descrizione)    text +="</br>Descrizione: "+feature.properties.descrizione;
                                if (feature.properties && feature.properties.qt)  text +="</br>Q.tà: "+feature.properties.qt;
                                if (feature.properties && feature.properties.tel)  text +="</br>Tel: "+feature.properties.tel;
                                if (feature.properties && feature.properties.email)  text +="</br>Email: "+feature.properties.email;
                                if (feature.properties && feature.properties.tipologia)  text +="</br>Tipologia: "+feature.properties.tipologia;
                                if (feature.properties && feature.properties.inizio)  text +="</br>Inizio: "+feature.properties.inizio;
                                if (feature.properties && feature.properties.fine)  text +="</br>Fine: "+feature.properties.fine;
                                if (feature.properties && feature.properties.foto)
                                    {
                                 if (feature.properties.foto.includes("jpg") || feature.properties.foto.includes("png") || feature.properties.foto.includes("gif"))  text +="</br><img src='"+feature.properties.foto+"' style='width:100%;min-width:200px;' ></div>";
                                    }
                                }

                        //}
                        if (document.body.clientWidth <= 767) {
		layer.bindPopup(text, {
                                maxWidth: "200",
				 maxHeight: "200",
                                closeButton: false
                            });
}else{
layer.bindPopup(text);
}
},
//
                        pointToLayer: function (feature, latlng)
                        {
                        var classs='circlered';
                        var marker = new L.Marker(latlng,
                          { icon : L.divIcon
                            (
                            {
                        			className : classs,
                              iconSize : [10,10],
                              html: '<div style="display: table; height:'+10+'px; overflow: hidden; "><div align="center" style="display: table-cell; vertical-align: middle;"><div style="width:'+10+'px;"></div></div></div>',
                              title: feature.properties.id
                            }
                            )
                          });

                        markers[feature.properties.id] = marker;
                      //  marker.bindPopup('',{maxWidth:200, autoPan:true});
                      //  marker.bindPopup(text,{maxWidth:200, autoPan:true});

                      //  marker.on('click',showMarker());
                        return marker;
                        }
                });
                //.addTo(map);

                markers.addLayer(myLayer);
                map.addLayer(markers);
              //  markers.on('click',showMarker);
                map.fitBounds(markers.getBounds());
        }

microAjax('db/mappaf.json',function (res) {
var feat=JSON.parse(res);
loadLayer(feat);
  finishedLoading();
} );

function startLoading() {
    loader.className = '';
}

function finishedLoading() {
    // first, toggle the class 'done', which makes the loading screen
    // fade out
    loader.className = 'done';
    setTimeout(function() {
        // then, after a half-second, add the class 'hide', which hides
        // it completely and ensures that the user can interact with the
        // map again.
        loader.className = 'hide';
    }, 500);
}
</script>

</body>
</html>
