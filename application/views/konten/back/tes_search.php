<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Search by Name or Lat,Lng</title>
    <style>
      #map { height: 80vh; }
      #searchbar {
        position: absolute; top: 10px; left: 50%; transform: translateX(-50%);
        z-index: 5; background: white; padding: 8px 10px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.15);
        display: flex; gap: 8px; align-items: center; width: min(680px, 90vw);
      }
      #q { flex: 1; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; }
      #addr { font: 12px/1.4 system-ui, sans-serif; margin: 6px 0 0; color: #333; }
    </style>
  </head>
  <body>
    <div id="searchbar">
      <input id="q" placeholder="Cari nama/alamat… atau ketik: -7.8014,110.3649" />
      <button id="go">Go</button>
    </div>
    <div id="map"></div>
    <div id="addr"></div>

    <script>
      let map, marker, geocoder, autocomplete;

      function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
          center: { lat: -7.797068, lng: 110.370529 }, // Yogyakarta sebagai default
          zoom: 13,
          mapTypeControl: false,
        });

        geocoder = new google.maps.Geocoder();
        marker = new google.maps.Marker({ map });

        const input = document.getElementById("q");
        const goBtn = document.getElementById("go");
        const addrEl = document.getElementById("addr");

        // Autocomplete untuk nama/alamat
        autocomplete = new google.maps.places.Autocomplete(input, {
          fields: ["geometry", "name", "formatted_address"],
          // (opsional) batasi ke Indonesia:
          componentRestrictions: { country: "id" },
        });

        autocomplete.addListener("place_changed", () => {
          const place = autocomplete.getPlace();
          if (!place.geometry || !place.geometry.location) return;
          const loc = place.geometry.location;
          goToLocation(loc.lat(), loc.lng(), place.formatted_address || place.name);
        });

        // Tombol Go & Enter
        goBtn.addEventListener("click", () => handleFreeText(input.value));
        input.addEventListener("keydown", (e) => {
          if (e.key === "Enter") handleFreeText(input.value);
        });

        function handleFreeText(text) {
          const coords = parseLatLng(text);
          if (coords) {
            // Jika input koordinat, center langsung lalu reverse geocode untuk alamat
            goToLocation(coords.lat, coords.lng);
            geocoder.geocode({ location: coords }, (res, status) => {
              if (status === "OK" && res?.[0]) addrEl.textContent = res[0].formatted_address;
            });
          } else {
            // Jika bukan koordinat mentah, coba geocode teks biasa (fallback jika user tidak pilih dari dropdown autocomplete)
            geocoder.geocode({ address: text }, (res, status) => {
              if (status === "OK" && res?.[0]) {
                const loc = res[0].geometry.location;
                goToLocation(loc.lat(), loc.lng(), res[0].formatted_address);
              }
            });
          }
        }

        function goToLocation(lat, lng, label) {
          const pos = { lat: Number(lat), lng: Number(lng) };
          map.setCenter(pos);
          map.setZoom(17);
          marker.setPosition(pos);
          marker.setTitle(label || `${lat.toFixed(6)}, ${lng.toFixed(6)}`);
          addrEl.textContent = label || `${lat}, ${lng}`;
        }

        function parseLatLng(str) {
          const m = String(str).trim().match(/^\s*(-?\d+(\.\d+)?)\s*,\s*(-?\d+(\.\d+)?)\s*$/);
          if (!m) return null;
          const lat = parseFloat(m[1]), lng = parseFloat(m[3]);
          if (isNaN(lat) || isNaN(lng)) return null;
          if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return null;
          return { lat, lng };
        }
      }
    </script>

    <script async defer
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCdAD-PAuK58pHbGGHwCKQFo8tDsOd7dTQ&libraries=places&callback=initMap&v=weekly"></script>
  </body>
</html>
