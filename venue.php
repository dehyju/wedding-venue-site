<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="company-logo.png" type="image/png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <link rel="stylesheet" href="globalstyles.css">
  <title>Venues</title>
</head>

<?php
  $servername = "sci-mysql";
  $username = "coa123wuser";
  $password = "grt64dkh!@2FD";
  $dbname = "coa123wdb";

  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $dbname); // Check connection

  function returnLocations($conn) {
    if(!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Fetch latitude and longitude data from your database
    $sql = "SELECT name, latitude, longitude FROM venue;";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Initialize an empty array to store latitude and longitude pairs
        $locations = array();

        // Loop through the results and add latitude and longitude pairs to the array
        while ($row = mysqli_fetch_assoc($result)) {
            $locations[] = array("name" => $row["name"], "latitude" => $row["latitude"], "longitude" => $row["longitude"]);
        }

        // Encode the array to JSON format
        $json_data = json_encode($locations);

        // Output the JSON-encoded data
    } else {
        echo "No location data found.";
    }
    return $json_data;
  }

  function generateComponents($option, $conn, $latitude, $longitude) {
    // Generate components based on the selected option
    switch($option) {
      case "option1":
        return displayByDefault($conn);
      case "option2":
        return orderByAlphabet($conn);
      case "option3":
        return orderByReviewScore($conn);
      case "option4":
        return orderByCatering($conn);
      case "option5":
        return orderByCapacity($conn);
      case "option6":
        return orderByDistance($conn,$latitude,$longitude);
      default:
        return displayByDefault($conn);
    }
  }

  //var_dump($_POST); //used for testing
  if(isset($_POST["option"])) {
    // Get the selected option value
    $selectedOption = $_POST["option"];

    $latitude = 0;
    $longitude = 0;
    
    if(isset($_POST["sentLocationData"])) {
      $locationData = json_decode($_POST["sentLocationData"]);

      // Get latitude and longitude values
      $latitude = $locationData->latitude; // Access latitude property of the object
      $longitude = $locationData->longitude; // Access longitude property of the object
  
      //echo "<script>console.log('Received latitude:', $latitude);</script>"; //testing code
      //echo "<script>console.log('Received longitude:', $longitude);</script>"; 
    } 

    
    echo generateComponents($selectedOption, $conn, $latitude, $longitude);
    exit;
    
  }

?>

<script>


  var jsonData = '<?php $locData = returnLocations($conn); echo $locData; ?>';
  var locData = JSON.parse(jsonData);

  //console.log(locData);
    
</script> 


<?php  
function sqlConnection($conn, $option, $latitude, $longitude) {
    if(!$conn) {
      die("Connection failed: " . mysqli_connect_error());
    }
    $target_latitude = $latitude;
    $target_longitude = $longitude;

    //echo $latitude;
    //echo $longitude;

    $sql = 
    "SELECT v.venue_id, v.name AS name, v.capacity, v.weekend_price, v.weekday_price, v.longitude, v.latitude, v.licensed, 
    AVG(r.score) AS average_score, 
    AVG(c.grade) AS average_catering_grade, 
    6371 * 
    ACOS(
    COS(RADIANS($target_latitude)) * COS(RADIANS(v.latitude)) * COS(RADIANS(v.longitude) - RADIANS($target_longitude)) + 
    SIN(RADIANS($target_latitude)) * SIN(RADIANS(v.latitude))
    ) AS distance
    FROM venue v 
    LEFT JOIN venue_review_score r ON v.venue_id = r.venue_id 
    LEFT JOIN catering c ON v.venue_id = c.venue_id 
    GROUP BY v.venue_id, v.name, v.capacity, v.weekend_price, v.weekday_price, v.longitude, v.latitude, v.licensed";
    if($option == "0") {
      $sql .= ";";
    } else if($option == "1") {
      $sql .= 
      " ORDER BY name;";
    } else if($option == "2") {
      $sql .= 
      " ORDER BY AVG(r.score) DESC;";
    } else if($option == "3") {
      $sql .=
      " ORDER BY AVG(c.grade) DESC;";
    } else if($option == "4") {
      $sql .= 
      " ORDER BY capacity DESC;";
    } else if($option == "5") {
      $sql .= 
      " ORDER BY distance;";
    }
    $table = mysqli_query($conn, $sql);

    return $table;
  }


  function renderCards($scoreAverageTable) {
    if (mysqli_num_rows($scoreAverageTable) > 0) {
      // output data of each row
      $finalOutput = "";
      while ($row = mysqli_fetch_array($scoreAverageTable)){
        //echo json_encode($row) . "<br>"; //Used to test database connection
        $licensed = $row["licensed"];
        $card_type = "text-bg-light";
        if ($licensed == 1) {
          $card_type = "text-bg-secondary";
        } 
        $venue_id = $row["venue_id"];
        $img_src = "venue-" . $venue_id . ".jpg";
        $name = $row["name"];
        $capacity = $row["capacity"];
        $avg_score =round($row["average_score"], 2);
        $weekend_price = $row["weekend_price"];
        $weekday_price = $row["weekday_price"];
        $avg_catering = round($row["average_catering_grade"], 1);
        $finalOutput .= "
        <div id=\"venue_$venue_id\" class=\"card $card_type align-items-center\" style=\"width: 18rem;\">
          <div class=\"text-center\" style=\"padding-top: 20px;\">
            <img src=\"$img_src\" class=\"img-thumbnail\" alt=\"...\" style=\"width: 200px; height: 200px;\">
            <div class=\"card-body text-center\">
              <h5 class=\"card-title\">$name</h5>
              <p class=\"card-text\"> Maximum Capacity: $capacity <br> 
              Weekday Price: $weekday_price <br> 
              Weekend Price: $weekend_price <br> 
              Average Review Score:  $avg_score / 10 <br> 
              Average Catering Grade: $avg_catering / 5</p>
              <button class=\"btn btn-primary\" onclick=\"bookVenue($venue_id)\">View Details</button>
            </div>
          </div>
        </div>";
      }
    }
    return $finalOutput;
  }

  

  function displayByDefault($conn) {
    
    $scoreAverageTable = sqlConnection($conn,"0",0,0);
    return renderCards($scoreAverageTable);
  }

  function orderByAlphabet($conn) {
    
    $scoreAverageTable = sqlConnection($conn,"1",0,0);
    return renderCards($scoreAverageTable);
  }
  
  function orderByReviewScore($conn) {
    
    $scoreAverageTable = sqlConnection($conn,"2",0,0);
    return renderCards($scoreAverageTable);
  }

  function orderByCatering($conn) {

    $scoreAverageTable = sqlConnection($conn,"3",0,0);
    return renderCards($scoreAverageTable);
  }

  function orderByCapacity($conn){
    $scoreAverageTable = sqlConnection($conn,"4",0,0);
    return renderCards($scoreAverageTable);
  }

  function orderByDistance($conn,$latitude,$longitude){

    $scoreAverageTable = sqlConnection($conn,"5",$latitude,$longitude);
    return renderCards($scoreAverageTable);
  }
  ?>

<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="wedding.php">
        <img src="company-logo.png" width="50" height="50">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
      <div class="collapse navbar-collapse" id="navbarScroll">
        <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="wedding.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="venue.php">Venues</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  
  <div id="option-container" class="container text-center"> 
    <div class="row justify-content-md-center">
      <h3 >Venues Available</h3>
    </div> 
    <div class="row justify-content-md-center align-items-center" style="padding: 20px"> 
      <div class="col">   

      <form id="postcode-form" class="row justify-content-center align-items-center">
          <div class="col-auto" style="padding: 20px">
            <label for="validationServer05" class="form-label">Postcode</label>
            <input type="text" class="form-control" name="zip" id="validationServer05" aria-describedby="validationServer05Feedback" required>
            <div id="validationServer05Feedback" class="invalid-feedback"></div>
          </div>
          <div class="col-12">
            <button class="btn btn-primary" id="postcode-submit" type="submit" name="submit">Nearest Venue</button>
            <div class="form-text" id="basic-addon4">Input a UK Postcode or select Distance for Live Location</div>
          </div>
      </form>

      <script src="form-validation.js"></script>

      </div>
      <div id="map" class="col text-center" style="width: 600px; height: 400px;"></div>
    </div>
    
    <div class="row justify-content-start" style="--bs-gap: .5rem">
      <div class="col-auto">
        <p>Sort By:</p>
      </div>
      <div class="col-auto me-auto">
        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
          <input type="radio" class="btn-check" name="btnradio" id="btnradio1" value="option1" autocomplete="off" checked onchange="loadComponents()">
          <label class="btn btn-outline-primary" for="btnradio1">Default</label>

          <input type="radio" class="btn-check" name="btnradio" id="btnradio2" value="option2" autocomplete="off" onchange="loadComponents()">
          <label class="btn btn-outline-primary" for="btnradio2">Name</label>

          <input type="radio" class="btn-check" name="btnradio" id="btnradio3" value="option3" autocomplete="off" onchange="loadComponents()">
          <label class="btn btn-outline-primary" for="btnradio3">Review Score</label>

          <input type="radio" class="btn-check" name="btnradio" id="btnradio4" value="option4" autocomplete="off" onchange="loadComponents()">
          <label class="btn btn-outline-primary" for="btnradio4">Catering Grade</label>

          <input type="radio" class="btn-check" name="btnradio" id="btnradio5" value="option5" autocomplete="off" onchange="loadComponents()">
          <label class="btn btn-outline-primary" for="btnradio5">Capacity</label>

          <input type="radio" class="btn-check" name="btnradio" id="btnradio6" value="option6" autocomplete="off" onchange="loadComponents()">
          <label class="btn btn-outline-primary" for="btnradio6">Distance</label>
        </div>
      </div>
      <div class="col-auto justify-content-end align-items-end">
        <span class="badge text-bg-secondary">Licensed</span>
        <span class="badge text-bg-light">Non-Licensed</span>
      </div>
    </div>
  </div>
  <section class="bg-light p-3">
    <div id="venue-container" class="row row-cols-1 row-cols-md-3 g-4 justify-content-center"></div>
  </section>

  
  
  <script src="venue_components_loader.js"></script>
  <script src="map_script.js"></script>
  
</body>
</html>