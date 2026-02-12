// script.js - O Cérebro do Frontend

let map, userMarker;
let mapKey = null; // Será preenchida pelo backend

// 1. Inicialização
document.addEventListener('DOMContentLoaded', async () => {
    // Pede a chave "pública" ao backend apenas para iniciar o mapa visual
    try {
        const response = await fetch('api.php?action=get_map_key');
        const data = await response.json();
        mapKey = data.key;
        initMap();
    } catch (error) {
        console.error("Erro ao obter configuração:", error);
        alert("Erro de conexão com o servidor.");
    }
});

function initMap() {
    map = tt.map({
        key: mapKey,
        container: 'map',
        center: [-46.6333, -23.5505],
        zoom: 10,
        theme: { style: 'main', layer: 'basic', source: 'vector' }
    });
    map.addControl(new tt.NavigationControl());

    // Geolocalização
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            const { latitude, longitude } = pos.coords;
            map.flyTo({ center: [longitude, latitude], zoom: 14 });
            createCustomMarker([longitude, latitude], '#4285F4', true);
        });
    }

    // Ativa Autocomplete
    setupAutocomplete('origemInput', 'origemSuggestions', 'origemCoords');
    setupAutocomplete('destinoInput', 'destinoSuggestions', 'destinoCoords');
}

// 2. Autocomplete (Via Backend)
function setupAutocomplete(inputId, listId, hiddenId) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);
    const hidden = document.getElementById(hiddenId);
    let timeout;

    input.addEventListener('input', () => {
        const query = input.value;
        clearTimeout(timeout);
        
        if (query.length < 3) { list.style.display = 'none'; return; }

        timeout = setTimeout(async () => {
            // Chama api.php em vez da TomTom direto
            const res = await fetch(`api.php?action=search&query=${encodeURIComponent(query)}`);
            const data = await res.json();
            
            list.innerHTML = '';
            if (data.results && data.results.length > 0) {
                list.style.display = 'block';
                data.results.forEach(result => {
                    const item = document.createElement('div');
                    const text = result.poi ? result.poi.name : result.address.freeformAddress;
                    const details = result.address.freeformAddress;
                    
                    item.innerHTML = `<strong>${text}</strong><br><small style='color:#888'>${details}</small>`;
                    item.onclick = () => {
                        input.value = text;
                        // Guarda a coordenada para usar no cálculo da rota
                        hidden.value = `${result.position.lat},${result.position.lon}`;
                        list.style.display = 'none';
                    };
                    list.appendChild(item);
                });
            } else { list.style.display = 'none'; }
        }, 300);
    });

    document.addEventListener('click', (e) => { if (e.target !== input) list.style.display = 'none'; });
}

// 3. Calcular Rotas (Via Backend)
async function calcularRotas() {
    const start = document.getElementById('origemCoords').value;
    const end = document.getElementById('destinoCoords').value;
    
    if (!start || !end) { alert("Por favor, selecione os endereços na lista de sugestões."); return; }

    document.getElementById('loading').style.display = 'block';
    document.getElementById('results-container').innerHTML = '';

    try {
        const res = await fetch(`api.php?action=route&start=${start}&end=${end}`);
        const data = await res.json();
        
        processRoutes(data);
    } catch (error) {
        console.error(error);
        alert("Erro ao calcular rotas.");
    } finally {
        document.getElementById('loading').style.display = 'none';
    }
}

function processRoutes(data) {
    if (!data.routes) return;

    // Processa dados
    let rotas = data.routes.map((rota, idx) => {
        const summary = rota.summary;
        const segundos = summary.travelTimeInSeconds;
        const horas = Math.floor(segundos / 3600);
        const minutos = Math.floor((segundos % 3600) / 60);
        
        return {
            index: idx,
            tempoSec: segundos,
            tempoTexto: (horas > 0 ? `${horas}h ` : "") + `${minutos}min`,
            distTexto: (summary.lengthInMeters / 1000).toFixed(1) + " km",
            distMetros: summary.lengthInMeters,
            atraso: summary.trafficDelayInSeconds,
            pontos: rota.legs[0].points
        };
    });

    // Ordenação
    const rapida = [...rotas].sort((a,b) => a.tempoSec - b.tempoSec)[0];
    const lenta = [...rotas].sort((a,b) => b.tempoSec - a.tempoSec)[0];
    const curta = [...rotas].sort((a,b) => a.distMetros - b.distMetros)[0];

    const container = document.getElementById('results-container');

    // Renderiza Cards
    createCard(rapida, 'fast', 'Recomendada', container);
    
    if (rotas.length > 1 && lenta.index !== rapida.index) {
        createCard(lenta, 'slow', 'Rota Lenta', container);
    }
    
    if (curta.index !== rapida.index) {
        createCard(curta, 'short', 'Menor Distância', container);
    }

    // Desenha a rápida no mapa
    drawRouteOnMap(rapida.pontos, '#00e676');
}

function createCard(rota, type, label, container) {
    const div = document.createElement('div');
    div.className = `card ${type}`;
    div.style.display = 'block';
    
    let badgeClass = type === 'fast' ? 'bg-green' : (type === 'slow' ? 'bg-red' : 'bg-yellow');
    let color = type === 'fast' ? '#00e676' : (type === 'slow' ? '#ff3d00' : '#ffea00');

    div.innerHTML = `
        <span class="badge ${badgeClass}">${label}</span>
        <div class="stats">
            <span>${rota.tempoTexto}</span>
            <span style="font-size: 0.7em; opacity: 0.7; align-self: center;">${rota.distTexto}</span>
        </div>
        ${rota.atraso > 0 ? `<div class="traffic-warn" style="color:#ff3d00; font-size:0.8em; margin-top:5px">⚠️ +${Math.floor(rota.atraso/60)} min trânsito</div>` : ''}
    `;
    
    div.onclick = () => drawRouteOnMap(rota.pontos, color);
    container.appendChild(div);
}

function drawRouteOnMap(points, color) {
    if (map.getLayer('route')) { map.removeLayer('route'); map.removeSource('route'); }
    
    const geoJson = points.map(p => [p.longitude, p.latitude]);
    
    map.addLayer({
        'id': 'route', 'type': 'line',
        'source': { 'type': 'geojson', 'data': { 'type': 'Feature', 'geometry': { 'type': 'LineString', 'coordinates': geoJson } } },
        'layout': { 'line-join': 'round', 'line-cap': 'round' },
        'paint': { 'line-color': color, 'line-width': 6, 'line-opacity': 0.8 }
    });

    // Marcadores e Zoom
    const start = geoJson[0];
    const end = geoJson[geoJson.length - 1];
    
    // Remove marcadores antigos (exceto o do usuario) e adiciona novos
    document.querySelectorAll('.marker-route').forEach(e => e.remove());
    createCustomMarker(start, '#00e676', false);
    createCustomMarker(end, '#ff3d00', false);

    const bounds = new tt.LngLatBounds();
    geoJson.forEach(p => bounds.extend(p));
    map.fitBounds(bounds, { padding: 80 });
}

function createCustomMarker(coords, color, isUser) {
    const el = document.createElement('div');
    el.className = isUser ? 'marker-user' : 'marker-route';
    el.style.width = isUser ? '20px' : '15px';
    el.style.height = isUser ? '20px' : '15px';
    el.style.backgroundColor = isUser ? '#4285F4' : '#fff';
    el.style.border = `3px solid ${color}`;
    el.style.borderRadius = '50%';
    el.style.boxShadow = `0 0 10px ${color}`;

    new tt.Marker({element: el}).setLngLat(coords).addTo(map);
}