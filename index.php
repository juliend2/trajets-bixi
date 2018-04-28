<?php

define('BIXI_STATIONS_JSON_ENDPOINT', 'https://api-core.bixi.com/gbfs/en/station_status.json');

define('STATION_ID_LEMAN_CHATEAUBRIAND', 338); // pres de chez moi (Foucher)
define('STATION_ID_BOYER_JARRY', 342); // pres du IGA
define('STATION_ID_LAJEUNESSE_JARRY', 339); // pres du metro Jarry
//define('STATION_ID_SQUARE_ST_LOUIS', 212); // pres du metro Sherbrooke, au parc Saint-Louis, vers lg2
define('STATION_ID_METRO_SHERBROOKE', 19); // pres du metro Sherbrooke, et de l'ITHQ
define('STATION_ID_CLARK_PRINCE_ARTHUR', 432); // pres de lg2

define('GREEN_THRESHOLD', 3);
define('YELLOW_THRESHOLD', 2);
define('ORANGE_THRESHOLD', 1);
define('RED_THRESHOLD', 0);

function array_flatten($array,$return) {
  for($x = 0; $x <= count($array); $x++) {
    if(isset($array[$x]) && is_array($array[$x])) {
      $return = array_flatten($array[$x], $return);
    }
    else {
      if(isset($array[$x])) {
        $return[] = $array[$x];
      }
    }
  }
  return $return;
}

$color_statuses = [
  'green' => GREEN_THRESHOLD.'+ left',
  'yellow' => YELLOW_THRESHOLD.' left',
  'orange' => ORANGE_THRESHOLD.' left',
  'red' => 'none left',
];

$stations_couples = [
  [STATION_ID_CLARK_PRINCE_ARTHUR, STATION_ID_METRO_SHERBROOKE], // job, metro sherbrooke
  [STATION_ID_LAJEUNESSE_JARRY, STATION_ID_LEMAN_CHATEAUBRIAND], // metro jarry, chez moi
  [STATION_ID_LEMAN_CHATEAUBRIAND, STATION_ID_BOYER_JARRY], // chez moi, IGA
];

$station_ids = array_unique(array_flatten($stations_couples, []));

$stations_json = json_decode(file_get_contents(BIXI_STATIONS_JSON_ENDPOINT));
$all_station_statuses = $stations_json->data->stations;
$interesting_stations_statuses = array_filter($all_station_statuses, function($station_status) use ($station_ids) {
  return in_array($station_status->station_id, $station_ids);
});

function score_departure($station) {
  if ($station->num_bikes_available >= GREEN_THRESHOLD) {
    return 'green';
  } elseif ($station->num_bikes_available >= YELLOW_THRESHOLD) {
    return 'yellow';
  } elseif ($station->num_bikes_available >= ORANGE_THRESHOLD) {
    return 'orange';
  } else {
    return 'red';
  }
}
function score_arrival($station) {
  if ($station->num_docks_available >= GREEN_THRESHOLD) {
    return 'green';
  } elseif ($station->num_docks_available >= YELLOW_THRESHOLD) {
    return 'yellow';
  } elseif ($station->num_docks_available >= ORANGE_THRESHOLD) {
    return 'orange';
  } else {
    return 'red';
  }
}


// returns an array where position:
// [0] = direction A to B safety score
// [1] = direction B to A safety score
function display_stations_status($station_statuses, $station_id_a, $station_id_b) {
  $station_a = array_values(array_filter($station_statuses, function($station_status) use ($station_id_a) {
    return $station_status->station_id == $station_id_a;
  }))[0];
  $station_b = array_values(array_filter($station_statuses, function($station_status) use ($station_id_b) {
    return $station_status->station_id == $station_id_b;
  }))[0];

  $direction_AtoB = [score_departure($station_a), score_arrival($station_b)];
  $direction_BtoA = [score_departure($station_b), score_arrival($station_a)];
  return [$direction_AtoB, $direction_BtoA];
}

function format_score($color) {
  global $color_statuses;
  return '<span class="'.$color.'">'.$color_statuses[$color].'</span>';
}

function format_trip($interesting_stations_statuses, $from, $to, $from_str, $to_str) {
  ?>
  <p><?php echo $from_str ?> vers <?php echo $to_str ?>:</p>
  <?php
    $result = display_stations_status($interesting_stations_statuses, $from, $to);
    echo format_score($result[0][0]).' → '.format_score($result[0][1]);
  ?>
  <p><?php echo $to_str ?> vers <?php echo $from_str ?>:</p>
  <?php
    echo format_score($result[1][0]).' → '.format_score($result[1][1]);
}

?><!DOCTYPE html>
<head>
  <meta charset="utf-8"/>
  <title>Bixis</title>
  <link rel="stylesheet" href="css/styles.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  .green { background-color: #09E340; }
  .yellow { background-color: #FFDD00; }
  .orange { background-color: #FF9500; }
  .red { background-color: #FF0000; }
</style>

</head>
<body>
  <div class="wrapper">
    <div class="container">
      <header>
        <h1>Bixis</h1>
      </header>
      <section class="station-couples">
        <?php format_trip($interesting_stations_statuses, STATION_ID_METRO_SHERBROOKE, STATION_ID_CLARK_PRINCE_ARTHUR, 'Métro Sherbrooke', 'Lg2'); ?>
      </section>
      <hr>

      <section class="station-couples">
        <?php format_trip($interesting_stations_statuses, STATION_ID_LAJEUNESSE_JARRY, STATION_ID_LEMAN_CHATEAUBRIAND, 'Metro Jarry', 'Chez moi'); ?>
      </section>
      <hr>
      <section class="station-couples">
        <?php format_trip($interesting_stations_statuses, STATION_ID_LEMAN_CHATEAUBRIAND, STATION_ID_BOYER_JARRY, 'Chez Moi', 'IGA'); ?>
      </section>
<hr>
<pre>
      <?php
print_r($interesting_stations_statuses);
      ?>
</pre>
    </div>
  </div>

  <script src="http://code.jquery.com/jquery-latest.js"></script>
  
  <script type="text/javascript">
    $(function(){
    
    });
  </script>
</body>
</html>
