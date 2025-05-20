
var map = L.map('map').setView([52.3555, -1.1743], 6);

// Add a tile layer to the map (you can choose different tile providers)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
attribution: 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
}).addTo(map);

for(var i = 0; i < locData.length; i++){
    var name = locData[i].name;
    var latitude = locData[i].latitude;
    var longitude = locData[i].longitude;

    
    L.marker([latitude, longitude]).addTo(map)
        .bindPopup(name);
        
}

function setUpView(newLat, newLong) {
    map.setView([newLat, newLong], 13);
}
