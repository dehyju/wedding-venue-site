var postcodeInitialRadius = 200;
var postcodeCircle = L.circle([0,0], {
    color: 'red',
    fillColor: 'red',
    fillOpacity: 0.5,
    radius: postcodeInitialRadius
});

document.addEventListener("DOMContentLoaded", function() {
var form = document.getElementById('postcode-form');

form.addEventListener('submit', function(event) {
    // Prevent form submission
    event.preventDefault();

    // Validate postcode input
    var postcodeInput = document.getElementById('validationServer05');
    var postcodeValue = postcodeInput.value.trim();

    // Regular expression pattern for UK postcode validation
    var postcodePattern = /^[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]? [0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}$/;

    // Check if the postcode is valid
    if (!postcodeValue.match(postcodePattern)) {
    // If not valid, add 'is-invalid' class to the input field and show error message
    postcodeInput.classList.add('is-invalid');
    document.getElementById('validationServer05Feedback').textContent = 'Please provide a valid UK Postcode.';
    } else {
    // If valid, remove 'is-invalid' class from the input field
    postcodeInput.classList.remove('is-invalid');

    // Proceed with form submission
    submitForm();
    }
});
});

function submitForm() {
    var postcode = document.getElementById('validationServer05').value;
    var nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${postcode}`;

    // Make a GET request to the Nominatim API
    fetch(nominatimUrl)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                // Extract latitude and longitude from the first result
                var newLatitude = data[0].lat;
                var newLongitude = data[0].lon;
                var locationData = {
                latitude: newLatitude,
                longitude: newLongitude
                }
                map.setView([newLatitude,newLongitude],13);

                if (map.hasLayer(postcodeCircle)) {
                    // Remove the postcodeCircle layer from the map
                    map.removeLayer(postcodeCircle);
                }
                postcodeCircle.setLatLng([newLatitude, newLongitude]);
                postcodeCircle.bindPopup(postcode);
                postcodeCircle.addTo(map);

                map.invalidateSize();
                
                map.on('zoomend', function() {
                // Get the current zoom level of the map
                var zoomLevel = map.getZoom();
                
                // Adjust radius based on zoom level
                // You can define your own logic to adjust the radius according to your requirements
                var tempNewRadius = postcodeInitialRadius * Math.pow(2, 13 - zoomLevel);
                
                // Update circle radius
                postcodeCircle.setRadius(tempNewRadius);
                });

                var locationJsonData = JSON.stringify(locationData);
                $.ajax({
                type: "POST",
                url: "venue.php",
                data: { option: "option6" , sentLocationData: locationJsonData },
                success: function(response) {
                    console.log(response);
                    console.log("Location data sent successfully", JSON.parse(locationJsonData));
                    $("#venue-container").html(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error sending location data:", error);
                }
                });
                // Use latitude and longitude as needed
                console.log(`Latitude: ${newLatitude}, Longitude: ${newLongitude} of Postcode`);


                // Call the function to load components with latitude and longitude
                //loadComponents(newLatitude, newLongitude);
            } else {
                console.error('No results found');
            }
        })
        .catch(error => console.error('Error:', error));
}