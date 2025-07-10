let map = L.map('map').setView([1.5608, 103.6371], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

function fetchTrees() {
  const location = document.getElementById('location').value.split(',');
  const lat = parseFloat(location[0]);
  const lng = parseFloat(location[1]);

  fetch(`api/get_trees.php?lat=${lat}&lng=${lng}&radius=2`)
    .then(res => res.json())
    .then(data => {
      document.getElementById('treeList').innerHTML = '';
      map.eachLayer(layer => {
        if (layer instanceof L.Marker) map.removeLayer(layer);
      });

      data.forEach(tree => {
        const marker = L.marker([tree.Latitude, tree.Longitude])
          .addTo(map)
          .bindPopup(`<b>${tree.Name}</b><br>Age: ${tree['DBH age of tree (cm)']}<br>Height: ${tree['Height (m)']}m`);
        
        const item = document.createElement('li');
        item.innerHTML = `<strong>${tree.Name}</strong> (${tree['Height (m)']}m) - ${tree['DBH age of tree (cm)']} years`;
        document.getElementById('treeList').appendChild(item);
      });
    });
}

fetchTrees(); // auto run on load
