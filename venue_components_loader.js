defaultComponents();

function defaultComponents() {
    $.ajax({
        type: "POST",
        url: "venue.php",
        data: { option: "option1" },
        success: function(response) {
            
            $("#venue-container").html(response);
        }
    });
    
    //console.log("default");
}

function loadComponents() {
    var selectedOption = $("input[name='btnradio']:checked").val();
    //console.log(selectedOption);
    if(selectedOption == "option6"){
        getLocation(function(locationData){
            var locationJsonData = JSON.stringify(locationData);
            $.ajax({
                type: "POST",
                url: "venue.php",
                data: { option: selectedOption, sentLocationData: locationJsonData },
                success: function(response) {
                    console.log(response);
                    console.log("Location data sent successfully", JSON.parse(locationJsonData));
                    $("#venue-container").html(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error sending location data:", error);
                }
                
            });
        });
        
    } else {
        $.ajax({
            type: "POST",
            url: "venue.php",
            data: { option: selectedOption },
            success: function(response) {
                console.log(response);
                
                $("#venue-container").html(response);
            }
        });
        
    }
}

function getLocation(callback) {
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(postion){
            var newLocationData = showPosition(postion);
            callback(newLocationData);
        }, showError);
    } else {
        alert("Geolocation is not supported by this browser.");
    }

}

function showPosition(position) {
    var latitude = position.coords.latitude;
    var longitude = position.coords.longitude;

    var initialRadius = 200;

    map.setView([latitude,longitude],13);
    var circle = L.circle([latitude,longitude], {
        color: '#3388ff',
        fillColor: '#3388ff',
        fillOpacity: 0.5,
        radius: initialRadius
    }).addTo(map).bindPopup("Your Location");

    map.on('zoomend', function() {
        // Get the current zoom level of the map
        var zoomLevel = map.getZoom();
        
        // Adjust radius based on zoom level
        // You can define your own logic to adjust the radius according to your requirements
        var newRadius = initialRadius * Math.pow(2, 13 - zoomLevel);
        
        // Update circle radius
        circle.setRadius(newRadius);
    });

    var localLocationData = {
        "latitude": latitude,
        "longitude": longitude
    };

    

    return localLocationData;
}

function showError(error) {
    switch (error.code) {
        case error.PERMISSION_DENIED:
            alert("User denied the request for Geolocation.");
            break;
        case error.POSITION_UNAVAILABLE:
            alert("Location information is unavailable.");
            break;
        case error.TIMEOUT:
            alert("The request to get user location timed out.");
            break;
        case error.UNKNOWN_ERROR:
            alert("An unknown error occurred.");
            break;
    }
}

function bookVenue(venue_id) {
    // Send AJAX request to booking.php
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // Response from booking.php, you can handle it here if needed
            console.log(this.responseText);
            window.location.href = "booking.php?venue_id=" + venue_id;
        }
    };
    xhttp.open("GET", "booking.php?venue_id=" + venue_id, true);
    xhttp.send();
}