<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="company-logo.png" type="image/png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="globalstyles.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    

    <title>Booking</title>
</head>

<?php
global $eventsJson;
global $capacity;

$servername = "sci-mysql";
$username = "coa123wuser";
$password = "grt64dkh!@2FD";
$dbname = "coa123wdb";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
$venue_id = $_GET['venue_id'];


function generateComponent($conn, $venue_id) {
  $sqlInfo = 
  "SELECT v.venue_id, v.name AS name, v.capacity, v.weekend_price, v.weekday_price, v.longitude, v.latitude, v.licensed, 
  AVG(r.score) AS average_score, 
  AVG(c.grade) AS average_catering_grade
  FROM venue v
  LEFT JOIN venue_review_score r ON v.venue_id = r.venue_id 
  LEFT JOIN catering c ON v.venue_id = c.venue_id 
  WHERE v.venue_id = $venue_id
  GROUP BY v.venue_id, v.name, v.capacity, v.weekend_price, v.weekday_price, v.longitude, v.latitude, v.licensed;";

  $sqlCatering = 
  "SELECT grade, cost
  FROM catering
  WHERE venue_id = $venue_id;";

  $sqlBooking = 
  "SELECT booking_date
  FROM venue_booking
  WHERE venue_id = $venue_id;";

  $tableInfo = mysqli_query($conn, $sqlInfo);
  $tableCatering = mysqli_query($conn, $sqlCatering);
  $tableBooking = mysqli_query($conn, $sqlBooking);

  $bookedDates = [];

  while ($bookingData = mysqli_fetch_array($tableBooking)) {
      $bookedDates[] = $bookingData['booking_date'];
  }

  // Convert booked dates to FullCalendar event format
  $events = [];
  foreach ($bookedDates as $bookedDate) {
      $events[] = [
          'title' => 'Booked',
          'start' => $bookedDate,
          'end' => $bookedDate,
          'color' => 'red' // Customize the color for booked dates
      ];
  }
  global $eventsJson;
  // Encode events array to JSON
  $eventsJson = json_encode($events);

  if (mysqli_num_rows($tableInfo) > 0) {

    $finalOutput = "";
    while ($infoData = mysqli_fetch_array($tableInfo)){
      //Card generateed here
      $licensed = $infoData["licensed"];
      $venue_id = $infoData["venue_id"];
      $img_src = "venue-" . $venue_id . ".jpg";
      $name = $infoData["name"];
      global $capacity;
      $capacity = $infoData["capacity"];
      $avg_score =round($infoData["average_score"], 2);
      $weekend_price = $infoData["weekend_price"];
      $weekday_price = $infoData["weekday_price"];
      $avg_catering = round($infoData["average_catering_grade"], 1);

      $licenseBadge = "";
      if ($licensed == 1) {
        $licenseBadge .=
        "<div class=\"col-auto justify-content-end align-items-end\">
          <span class=\"badge text-bg-secondary\">Licensed</span>
        </div>";
      } else {
        $licenseBadge .=
        "<div class=\"col-auto justify-content-end align-items-end\">
          <span class=\"badge text-bg-light\">Non-Licensed</span>
        </div>";
      }
      $finalOutput .= "
      <section class=\"bg-light p-3\">
      <a href=\"venue.php\" class=\"btn btn-primary justify-content-start\">See other venues</a> 
      <div class=\"row justify-content-center\"> $licenseBadge </div>
      <div id=\"venue-container\" class=\"row row-cols-1 row-cols-md-3 g-4 justify-content-center\" style=\"padding: 20px;\">
      
      ";
      $cateringCard = "
      <div class=\"card align-items-center mx-3\" style=\"width: 18rem;\">
        <div class=\"text-center\" style=\"padding-top: 20px;\">
          <img src=\"catering-logo.jpg\" class=\"img-thumbnail\" alt=\"...\" style=\"width: 200px; height: 200px;\">
          <div class=\"card-body text-center px-3 pt-2 my-3\" style=\"max-height: 200px; overflow-y: auto;\">
            <h5 class=\"card-title\">Catering Options</h5>
            <p class=\"card-text\">";
      $caterer = 0;
      while($cateringData = mysqli_fetch_array($tableCatering)) {
        $grade = $cateringData["grade"];
        $cost = $cateringData["cost"];
        $caterer += 1;
        $cateringCard .= "
        <span class=\"fw-bold\">Catering Option $caterer </span><br>
        Catering Grade: $grade <br>
        Price Per Person: $cost <br>
        <br>
        ";
      }
      $caterer = 0;
      $cateringCard .= "
            </p>
          </div>
        </div>
      </div>";

      $calendarCard = "
      <div id=\"calendar\"></div>
      <script>
        var selectedDate;
        document.addEventListener('DOMContentLoaded', function() {
          var calendarEl = document.getElementById('calendar');
          var calendar = new FullCalendar.Calendar(calendarEl, {
            // Configure your calendar options here
            initialView: 'dayGridMonth',
            events: $eventsJson,
            selectable: 'single', // Allow date selection
            select: function(info) { // Handle date selection
              var startDate = info.startStr;
              selectedDate = startDate;
              //sendDataToServer(startDate);
            }
          });
          calendar.render();
        });
      </script>
      ";

      $mainCard = "
      <div class=\"card align-items-center\" style=\"width: 18rem;\">
        <div class=\"text-center\" style=\"padding-top: 20px;\">
          <img src=\"$img_src\" class=\"img-thumbnail\" alt=\"...\" style=\"width: 200px; height: 200px;\">
            <div class=\"card-body text-center px-3 pt-2 my-3\">
              <h5 class=\"card-title\">$name</h5>
              <p class=\"card-text\"> Maximum Capacity: $capacity <br> 
              Weekday Price: $weekday_price <br> 
              Weekend Price: $weekend_price <br> 
              Average Review Score:  $avg_score / 10 <br> 
              Average Catering Grade: $avg_catering / 5</p>
            </div>
        </div>
      </div>";

      $finalOutput .= $mainCard . $cateringCard. $calendarCard;
    }
    $finalOutput .= "
    </div>
    </section>
  ";

    return $finalOutput;
  }
}

function generateOptions($conn, $venue_id) {
  $sqlCatering = 
  "SELECT grade, cost
  FROM catering
  WHERE venue_id = $venue_id;";

  $tableCatering = mysqli_query($conn, $sqlCatering);

  $cateringOptions = "";
  $caterer = 0;
  while($cateringData = mysqli_fetch_array($tableCatering)){
    $caterer += 1;
    $cateringOptions .= "
    <option>$caterer</option>
    ";
  }
  
  $formCard = "
  <div class=\"col-auto\">
      <label for=\"numGuests\" class=\"form-label align-items-center\">Number of Guests</label>
      <input type=\"number\" class=\"form-control\" id=\"numGuests\" required>
  </div>
  <div class=\"col-auto\">
      <label for=\"cateringOption\" class=\"form-label\">Catering Option</label>
      <select class=\"form-select\" id=\"cateringOption\" required>
          <option selected disabled value=\"\">Choose...</option>
          $cateringOptions
      </select>
  </div>
  ";

  return $cateringOptions;
}
function returnWeekendPrice($conn, $venue_id, $numGuests, $cateringOption) {
  $sql = "SELECT weekend_price FROM venue WHERE venue_id = $venue_id";

  $result = mysqli_query($conn, $sql);
  
  if (!$result) {
    return "Error fetching weekend price: " . mysqli_error($conn);
  }

  $data = mysqli_fetch_array($result);
  $price = $data["weekend_price"];
  
  $sql = 
  "SELECT cost
  FROM catering
  WHERE venue_id = $venue_id;
  ";

  $data = mysqli_query($conn, $sql);
  $count = 0;
  $cateringCost = 0;
  while($row = mysqli_fetch_array($data)){
    $count++;
    if ($count == $cateringOption) {
      $cateringCost = $row['cost'];
      break; // Exit loop if catering option matches
    }
  }
  $totalPrice = ($numGuests * $cateringCost) + $price;

  return $totalPrice;
}

function returnWeekdayPrice($conn, $venue_id, $numGuests, $cateringOption) {
  $sql = "SELECT weekday_price FROM venue WHERE venue_id = $venue_id";

  $result = mysqli_query($conn, $sql);
  
  if (!$result) {
    return "Error fetching weekday price: " . mysqli_error($conn);
  }

  $data = mysqli_fetch_array($result);
  $price = $data["weekday_price"];
  $sql = 
  "SELECT cost
  FROM catering
  WHERE venue_id = $venue_id;
  ";

  $data = mysqli_query($conn, $sql);
  $count = 0;
  $cateringCost = 0;
  while($row = mysqli_fetch_array($data)){
    $count++;
    if ($count == $cateringOption) {
      $cateringCost = $row['cost'];
      break; // Exit loop if catering option matches
    }
  }
  $totalPrice = ($numGuests * $cateringCost) + $price;

  return $totalPrice;
}

function calculateBookingPrice($venue_id, $price, $numGuests, $cateringOption) {
  global $conn;
  $sql = 
  "SELECT cost
  FROM catering
  WHERE venue_id = $venue_id;
  ";
  $data = mysqli_query($conn, $sql);
  $count = 0;
  $cateringCost = 0;
  while($row = mysqli_fetch_array($data)){
    $count++;
    if ($count == $cateringOption) {
      $cateringCost = $row['cost'];
      break; // Exit loop if catering option matches
    }
  }
  $totalPrice = ($numGuests * $cateringCost) + $price;
  return $totalPrice;
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
            <a class="nav-link" href="venue.php">Venues</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <?php echo generateComponent($conn,$venue_id) ?>

  <div id="priceDisplay" class="text-center"></div>

  <div class="container center-text">
    <form id="bookingForm" class="row g-3 needs-validation justify-content-center">
    <input type="hidden" name="venue_id" value="<?php echo $venue_id; ?>">
      <div class="col-auto">
        <label for="numGuests" class="form-label align-items-center">Number of Guests</label>
        <input type="number" class="form-control" id="numGuests" required>
        <div id="validationFeedbackCapacity" class="invalid-feedback"></div>
      </div>
      <div class="col-auto">
        <label for="cateringOption" class="form-label">Catering Option</label>
        <select class="form-select" id="cateringOption" required>
          <option selected disabled value="">Choose...</option>
          <?php echo generateOptions($conn, $venue_id); ?>
        </select>
      </div>
      <div class="col-auto">
        <label for="bookingDate" class="form-label">Select Date</label>
        <input type="date" class="form-control" id="bookingDate" name="bookingDate" required>
        <div id="validationFeedback" class="invalid-feedback"></div>
      </div>
      <div class="row py-2">
        <div class="col text-center">
          <button id="calculatePriceBtn" class="btn btn-primary align-items-center" type="submit">Calculate Booking Price</button>
        </div>
      </div>
    </form>
  </div>

  

  <script> 
  document.addEventListener("DOMContentLoaded", function() {
    var form = document.getElementById('bookingForm');

    form.addEventListener('submit', function(event) {
      event.preventDefault();
      
      var btnElement = document.getElementById('bookingDate');

      var numGuests = $('#numGuests').val();
      var cateringOption = $('#cateringOption').val();
      var totalPrice;

      var selectedDateObj = new Date($('#bookingDate').val());

      var bookedDates = <?php echo $eventsJson; ?>;
      
      var dayOfWeek = selectedDateObj.getDay(); 
      var venue_id = $('[name="venue_id"]').val(); // Get venue_id from the hidden input field
      function formatDate(date) {
        var year = date.getFullYear();

        // Add leading zero if month or day is less than 10
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var day = date.getDate().toString().padStart(2, '0');

        return year + '-' + month + '-' + day;
      }
      var formattedDate = formatDate(selectedDateObj);
      var maxCapacity = <?php global $capacity; echo $capacity; ?>;
      if (numGuests > maxCapacity) {
        document.getElementById('priceDisplay').textContent="";
        document.getElementById('numGuests').classList.add('is-invalid');
        document.getElementById('validationFeedbackCapacity').textContent = 'Not enough capacity';
      } else if (numGuests < 1) {
        document.getElementById('priceDisplay').textContent="";
        document.getElementById('numGuests').classList.add('is-invalid');
        document.getElementById('validationFeedbackCapacity').textContent = 'Cannot be lower than 1';
      } else {
        document.getElementById('numGuests').classList.remove('is-invalid');
        $.ajax({
          type: 'POST',
          url: 'booking.php?venue_id='+venue_id,
          data: { numGuests: numGuests, cateringOption: cateringOption, selectedDate: formattedDate, 
                  dayOfWeek: dayOfWeek, bookedDates: JSON.stringify(bookedDates), btnElement: 'bookingDate' },
          success: function(response){
              console.log(response);
              $("#priceDisplay").html(response);
          }
        });
      } 
      
    });
  });
    
  </script>
  <?php 

  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $numGuests = $_POST['numGuests'];
    $cateringOption = $_POST['cateringOption'];

    $jsonData = $_POST['bookedDates'];
    $bookedDates = json_decode($jsonData);

    $btnElement = $_POST['btnElement'];

    $selectedDate = $_POST['selectedDate'];
    //echo "Selected " . $selectedDate;
    $dayOfWeek = $_POST['dayOfWeek'];
    $totalPrice;

    if ($dayOfWeek == 0 || $dayOfWeek == 6) {
      $totalPrice = returnWeekendPrice($conn, $venue_id, $numGuests, $cateringOption);
    } else {
      $totalPrice = returnWeekdayPrice($conn, $venue_id, $numGuests, $cateringOption);
    }
    $found = false;
    foreach ($bookedDates as $bookedDate) {
      //echo "Date: " . $bookedDate->end . " ";
      if ($bookedDate->end == $selectedDate) {
        //echo "FOUND";
        $found = true;
      } 
    } 
    if ($found == true) {
      echo "
        <script>
        var btnElement = document.getElementById('$btnElement');
        var priceElement = document.getElementById('priceDisplay');
        btnElement.classList.add('is-invalid');
        priceElement.style.color = 'red';
        priceElement.innerHTML = '<span class=\"fw-bold\">This date is booked</span>';

        </script>
        ";
    } else {
      echo "
        <script>
        var btnElement = document.getElementById('$btnElement');
        var priceElement = document.getElementById('priceDisplay');
        btnElement.classList.remove('is-invalid');
        var totalPrice = $totalPrice;
        priceElement.classList.remove('invalid-feedback');
        priceElement.style.color = 'black';
        priceElement.innerHTML = '<span class=\"fw-bold\">Total Price: </span>' + totalPrice;
        </script>
      ";
    }
    
  }
  ?>
  
</body>
</html>