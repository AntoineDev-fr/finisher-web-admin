(function () {
  function debounce(fn, delay) {
    var timer;
    return function () {
      var args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () {
        fn.apply(null, args);
      }, delay);
    };
  }

  window.initRaceSearch = function (inputId, targetId) {
    var input = document.getElementById(inputId);
    var target = document.getElementById(targetId);
    if (!input || !target) return;

    var url = input.getAttribute('data-search-url');
    if (!url) return;

    var runSearch = debounce(function () {
      var q = input.value || '';
      var fullUrl = url + '?q=' + encodeURIComponent(q);
      fetch(fullUrl, { headers: { 'X-Requested-With': 'fetch' } })
        .then(function (res) { return res.text(); })
        .then(function (html) { target.innerHTML = html; })
        .catch(function () {});
    }, 300);

    input.addEventListener('keyup', runSearch);
  };

  window.initRaceMap = function (mapId, latId, lngId) {
    if (!window.L) return;

    var mapEl = document.getElementById(mapId);
    var latEl = document.getElementById(latId);
    var lngEl = document.getElementById(lngId);
    if (!mapEl || !latEl || !lngEl) return;

    var lat = parseFloat(latEl.value) || 48.8566;
    var lng = parseFloat(lngEl.value) || 2.3522;

    var map = L.map(mapEl).setView([lat, lng], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([lat, lng], { draggable: false }).addTo(map);

    function updateInputs(latlng) {
      latEl.value = latlng.lat.toFixed(7);
      lngEl.value = latlng.lng.toFixed(7);
    }

    map.on('click', function (e) {
      marker.setLatLng(e.latlng);
      updateInputs(e.latlng);
    });

    function syncFromInputs() {
      var newLat = parseFloat(latEl.value);
      var newLng = parseFloat(lngEl.value);
      if (isNaN(newLat) || isNaN(newLng)) return;
      var latlng = L.latLng(newLat, newLng);
      marker.setLatLng(latlng);
      map.setView(latlng);
    }

    latEl.addEventListener('change', syncFromInputs);
    lngEl.addEventListener('change', syncFromInputs);
  };
})();
