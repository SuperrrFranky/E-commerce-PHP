<html>

<head>
    <style>
        #map {
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            left: 0;
        }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>

<body>
    <div id="map"></div>
    <script>
        var map = L.map('map').setView([0, 0], 1);
        L.tileLayer('https://api.maptiler.com/maps/streets-v2/{z}/{x}/{y}.png?key=ychDL9AY19lKwGY6sj49', {
            attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>'
        }).addTo(map);

        var marker = L.marker([71.0, -0.64]).addTo(map);
    </script>
</body>





</html>