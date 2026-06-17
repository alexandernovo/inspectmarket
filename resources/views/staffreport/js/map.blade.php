<script>
    let mapData = [];
    let deletedmapData = [];
    let indexSelected;
    let map;
    let marker;
    let defaultCoords = [11.2784, 122.0421];

    let defaultMapSingleData = {
        "map_id": 0,
        "image": "",
        "map_url": ""
    };

    function populateMap() {
        let htmlJoinMapData = mapData.map((x, index) => {
            let fileLabel = x.image ? `${x.image.name}` : "";
            return `
                <div class="row mx-auto px-0 align-items-start mb-4 mt-1">
                    <div class="col-5">
                        <div class="form-group position-relative">
                            <label class="mb-0">Image</label>
                            <input type="file" accept="image/*" class="form-control file-input" data-index="${index}">
                            ${fileLabel ? `<small class="position-absolute text-success" style="bottom: -20px">File: ${fileLabel}</small>` : ``}
                        </div>
                    </div>
                    <div style="flex: 1">
                        <div class="form-group">
                            <label class="mb-0">Specific Location</label>
                            <input type="text" placeholder="Click here to open map" value="${x.map_url}" data-index="${index}" class="form-control specific_location_map" readonly>
                        </div>
                    </div>
                    <div class="col-1 mb-2 d-flex justify-content-center align-self-end">
                        <i class="bi bi-x-lg remove-row text-danger" data-index="${index}" style="cursor:pointer;"></i>
                    </div>
                </div>
            `;
        }).join('');

        $(".divDataClone").html(htmlJoinMapData);
    }

    function initialMapHtml() {
        mapData.push(cloneData(defaultMapSingleData));
        populateMap();
    }

    initialMapHtml();

    function initMap() {
        if (map) return; // already initialized

        map = L.map('mapIncident').setView(defaultCoords, 20);

        // Base layers
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
            subdomains: 'abcd',
            maxZoom: 18
        }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}{r}.png', {
            subdomains: 'abcd',
            maxZoom: 18,
            pane: 'overlayPane'
        }).addTo(map);

        // Custom icon
        const customIcon = L.icon({
            iconUrl: '{{ asset('assets/images/marker-icon.png') }}',
            iconSize: [28, 38],
            iconAnchor: [19, 38],
            popupAnchor: [0, -35]
        });

        // Default marker
        marker = L.marker(defaultCoords, {
                icon: customIcon,
                draggable: true
            })
            .addTo(map)
            .bindPopup("Drag or click map to change marker.")
            .openPopup();

        // Click to move marker
        map.on('click', function(e) {
            marker.setLatLng(e.latlng)
                .bindPopup(`📍 ${e.latlng.lat.toFixed(5)}, ${e.latlng.lng.toFixed(5)}`)
                .openPopup();
        });

        // Satellite imagery
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri'
        }).addTo(map);

        // Geocoder
        L.Control.geocoder({
            defaultMarkGeocode: false
        }).on('markgeocode', function(e) {
            const center = e.geocode.center;
            marker.setLatLng(center)
                .bindPopup(`📍 ${e.geocode.name}<br>${center.lat.toFixed(5)}, ${center.lng.toFixed(5)}`)
                .openPopup();
            map.fitBounds(e.geocode.bbox);
        }).addTo(map);
    }

    function showMap() {
        initMap(); // ensure map is created

        let findData = mapData[indexSelected];
        let coords = defaultCoords;

        if (findData && findData.map_url) {
            let parts = findData.map_url.split(",");
            if (parts.length === 2) {
                coords = [parseFloat(parts[0]), parseFloat(parts[1])];
            }
        }

        // update view + marker
        map.setView(coords, 20);
        marker.setLatLng(coords);
    }

    // Save selected map position
    $('#incidentMapSelect').on('click', function() {
        let center = marker.getLatLng();
        let lat = center.lat;
        let lng = center.lng;
        let findData = mapData[indexSelected];
        if (findData) {
            findData.map_url = `${lat}, ${lng}`;
            populateMap();
            $(".mapModal").modal("hide");
        }
    });

    // When user clicks location field
    $(document).on("click", ".specific_location_map", function() {
        indexSelected = $(this).data("index");
        $(".mapModal").modal("show");
    });

    // Open map modal
    $(document).on("show.bs.modal", ".mapModal", function() {
        setTimeout(() => {
            showMap();
        }, 500);
    });

    // Add new row
    $(document).on("click", "#addMapDataBtn", function() {
        mapData.push(cloneData(defaultMapSingleData));
        populateMap();
    });

    // Remove row
    $(document).on("click", ".remove-row", function() {
        let indexSelected = $(this).data("index");
        let findData = mapData[indexSelected];
        if (findData) {
            if (findData.map_id != 0) {
                deletedmapData.push(findData);
            }
            mapData.splice(indexSelected, 1);
            populateMap();
        }

    });

    // Handle file upload
    $(document).on("change", ".file-input", function(e) {
        let index = $(this).data("index");
        let file = e.target.files[0];
        if (file) {
            // store file name + base64
            let reader = new FileReader();
            reader.onload = function(evt) {
                mapData[index].image = {
                    name: file.name,
                    data: evt.target.result
                };
                populateMap(); // refresh UI to show filename
            };
            reader.readAsDataURL(file);
        }
    });

    function cloneData(data) {
        return JSON.parse(JSON.stringify(data));
    }
</script>
