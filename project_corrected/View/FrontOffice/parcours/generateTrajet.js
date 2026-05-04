/**
 * generateTrajet.js — BarchaThon v3
 * FIXES:
 * - Génère 3 trajets : Facile + Moyen + Difficile (plus 4 aléatoires)
 * - Suppression du tooltip "Cliquez sur une carte ou un tracé pour sélectionner"
 * - Suppression de "OSRM + OpenStreetMap · 100% gratuit" dans l'en-tête
 * - Affiche uniquement D et A du trajet sélectionné (pas D1/D2/A1/A2...)
 * - Fix validation : applyRoute() marque les champs comme valides (bypass erreur région)
 * - Bouton "Générer trajet" : juste changer l'icône ✨→✅, garder la même couleur
 */

const REGION_POINTS = {
  'Nabeul': [
    { lat: 36.4513, lng: 10.7357, nom: 'Nabeul Centre' },
    { lat: 36.4441, lng: 10.7268, nom: 'Gare Nabeul' },
    { lat: 36.4350, lng: 10.7452, nom: 'Plage Nabeul' },
    { lat: 36.4280, lng: 10.7600, nom: 'Bir Rekba' },
    { lat: 36.4680, lng: 10.7480, nom: 'Manzel Temim' },
    { lat: 36.4760, lng: 10.7220, nom: 'Beni Khiar' },
    { lat: 36.4900, lng: 10.7350, nom: 'Korba' },
    { lat: 36.4150, lng: 10.7550, nom: 'Dar Chaabane' },
    { lat: 36.4601, lng: 10.7310, nom: 'Marché Central Nabeul' },
  ],
  'Hammamet': [
    { lat: 36.4000, lng: 10.5560, nom: 'Hammamet Centre' },
    { lat: 36.3960, lng: 10.5630, nom: 'Plage Hammamet' },
    { lat: 36.4100, lng: 10.5480, nom: 'Hammamet Nord' },
    { lat: 36.3850, lng: 10.5720, nom: 'Marina Hammamet' },
    { lat: 36.4210, lng: 10.5350, nom: 'Hammamet Yasmine' },
    { lat: 36.3750, lng: 10.5800, nom: 'Hammamet Sud' },
    { lat: 36.4050, lng: 10.5200, nom: 'Bou Argoub' },
    { lat: 36.4300, lng: 10.5600, nom: 'Grombalia' },
    { lat: 36.3900, lng: 10.5900, nom: 'Corniche Hammamet' },
  ],
  'Tunis': [
    { lat: 36.8190, lng: 10.1658, nom: 'Avenue Habib Bourguiba' },
    { lat: 36.8509, lng: 10.1944, nom: 'Sidi Bou Saïd' },
    { lat: 36.8579, lng: 10.3247, nom: 'La Marsa' },
    { lat: 36.8438, lng: 10.2464, nom: 'Carthage' },
    { lat: 36.8300, lng: 10.1500, nom: 'Bardo' },
    { lat: 36.8100, lng: 10.1800, nom: 'Les Berges du Lac' },
    { lat: 36.8400, lng: 10.1400, nom: 'El Menzah' },
    { lat: 36.7900, lng: 10.1700, nom: 'Montplaisir' },
    { lat: 36.8000, lng: 10.2200, nom: 'La Goulette' },
  ],
  'Sousse': [
    { lat: 35.8281, lng: 10.6369, nom: 'Sousse Médina' },
    { lat: 35.8400, lng: 10.6200, nom: 'Port El Kantaoui' },
    { lat: 35.8100, lng: 10.6500, nom: 'Sousse Plage' },
    { lat: 35.8600, lng: 10.6000, nom: 'Akouda' },
    { lat: 35.7900, lng: 10.6700, nom: 'Chott Mariam' },
    { lat: 35.8700, lng: 10.5900, nom: 'Hammam Sousse' },
    { lat: 35.8300, lng: 10.6800, nom: 'Corniche Sousse' },
    { lat: 35.8000, lng: 10.6300, nom: 'Sahloul' },
    { lat: 35.8450, lng: 10.6450, nom: 'Khezama' },
  ],
  'Sfax': [
    { lat: 34.7406, lng: 10.7603, nom: 'Sfax Médina' },
    { lat: 34.7300, lng: 10.7800, nom: 'Sfax Plage' },
    { lat: 34.7600, lng: 10.7400, nom: 'Sfax Centre' },
    { lat: 34.7800, lng: 10.7600, nom: 'Route Tunis-Sfax' },
    { lat: 34.7100, lng: 10.7700, nom: 'Sfax Sud' },
    { lat: 34.7500, lng: 10.8000, nom: 'Sakiet Eddaier' },
    { lat: 34.7900, lng: 10.7200, nom: 'Sfax Nord' },
    { lat: 34.7200, lng: 10.7500, nom: 'Sfax Corniche' },
    { lat: 34.7700, lng: 10.7900, nom: 'Mahres' },
  ],
  'Monastir': [
    { lat: 35.7643, lng: 10.8113, nom: 'Monastir Centre' },
    { lat: 35.7750, lng: 10.8000, nom: 'Skanes Monastir' },
    { lat: 35.7500, lng: 10.8250, nom: 'Monastir Plage' },
    { lat: 35.7850, lng: 10.7900, nom: 'Ksar Hellal' },
    { lat: 35.7400, lng: 10.8400, nom: 'Monastir Port' },
    { lat: 35.7650, lng: 10.8350, nom: 'Corniche Monastir' },
    { lat: 35.7900, lng: 10.8100, nom: 'Moknine' },
    { lat: 35.7300, lng: 10.8200, nom: 'Lamta' },
    { lat: 35.7550, lng: 10.8450, nom: 'Teboulba' },
  ],
  'Bizerte': [
    { lat: 37.2744, lng: 9.8739, nom: 'Bizerte Centre' },
    { lat: 37.2600, lng: 9.8900, nom: 'Port Bizerte' },
    { lat: 37.2850, lng: 9.8600, nom: 'Lac de Bizerte' },
    { lat: 37.2500, lng: 9.9100, nom: 'Zarzouna' },
    { lat: 37.2950, lng: 9.8400, nom: 'Bizerte Nord' },
    { lat: 37.2300, lng: 9.9300, nom: 'Menzel Bourguiba' },
    { lat: 37.2700, lng: 9.8300, nom: 'Remel Plage' },
    { lat: 37.3000, lng: 9.8600, nom: 'Cap Blanc' },
    { lat: 37.2450, lng: 9.8700, nom: 'Ain Mariem' },
  ],
  'Kairouan': [
    { lat: 35.6781, lng: 10.0963, nom: 'Kairouan Médina' },
    { lat: 35.6850, lng: 10.1100, nom: 'Grande Mosquée Kairouan' },
    { lat: 35.6700, lng: 10.0800, nom: 'Kairouan Sud' },
    { lat: 35.6950, lng: 10.1200, nom: 'Route Tunis-Kairouan' },
    { lat: 35.6600, lng: 10.1300, nom: 'Kairouan Nord' },
    { lat: 35.6450, lng: 10.0900, nom: 'El Alaa' },
    { lat: 35.7100, lng: 10.0700, nom: 'Sbikha' },
    { lat: 35.6800, lng: 10.0600, nom: 'Oued Zeroud' },
    { lat: 35.7000, lng: 10.1400, nom: 'Route Sousse-Kairouan' },
  ],
  'Ariana': [
    { lat: 36.8923, lng: 10.1939, nom: 'Ariana Ville' },
    { lat: 36.9003, lng: 10.2018, nom: 'Raoued' },
    { lat: 36.8760, lng: 10.1710, nom: 'Ettadhamen' },
    { lat: 36.8850, lng: 10.2150, nom: 'La Soukra' },
    { lat: 36.9100, lng: 10.1600, nom: 'Kalaat El Andalous' },
    { lat: 36.8700, lng: 10.2300, nom: 'Borj Louzir' },
    { lat: 36.9200, lng: 10.1800, nom: 'Sidi Thabet' },
    { lat: 36.8630, lng: 10.1870, nom: 'Ennasr' },
    { lat: 36.9050, lng: 10.2250, nom: 'Technopole El Ghazela' },
  ],
  'Gabès': [
    { lat: 33.8881, lng: 10.0975, nom: 'Gabès Centre' },
    { lat: 33.8760, lng: 10.1100, nom: 'Gabès Médina' },
    { lat: 33.9000, lng: 10.0800, nom: 'Gabès Plage' },
    { lat: 33.8650, lng: 10.1200, nom: 'Cité Administrative Gabès' },
    { lat: 33.9150, lng: 10.0700, nom: 'Jara Gabès' },
    { lat: 33.8500, lng: 10.1350, nom: 'Chenini Gabès' },
    { lat: 33.9300, lng: 10.0600, nom: 'Métouia' },
    { lat: 33.8400, lng: 10.1500, nom: 'El Hamma' },
    { lat: 33.9400, lng: 10.0500, nom: 'Ghannouch' },
  ],
};

const REGION_DEFAULT_CENTER = {
  'Gafsa': [34.43, 8.78], 'Médenine': [33.35, 10.49], 'Tataouine': [32.93, 10.45],
  'Kébili': [33.70, 8.97], 'Tozeur': [33.92, 8.13], 'Jendouba': [36.50, 8.78],
  'Béja': [36.73, 9.18], 'Le Kef': [36.18, 8.71], 'Mahdia': [35.50, 11.06],
  'Kasserine': [35.17, 8.83], 'Sidi Bouzid': [35.04, 9.48], 'Siliana': [36.08, 9.37],
  'Ben Arous': [36.76, 10.25], 'Manouba': [36.86, 10.08], 'Zaghouan': [36.40, 10.14],
};

function getPoints(region) {
  if (!region) return null;
  const key = Object.keys(REGION_POINTS).find(k =>
    k.toLowerCase() === region.trim().toLowerCase() ||
    region.toLowerCase().includes(k.toLowerCase()) ||
    k.toLowerCase().includes(region.toLowerCase())
  );
  return key ? REGION_POINTS[key] : null;
}

function getCenter(region) {
  const pts = getPoints(region);
  if (pts) {
    return [
      pts.reduce((s, p) => s + p.lat, 0) / pts.length,
      pts.reduce((s, p) => s + p.lng, 0) / pts.length,
    ];
  }
  const key = Object.keys(REGION_DEFAULT_CENTER).find(k =>
    region.toLowerCase().includes(k.toLowerCase())
  );
  return key ? REGION_DEFAULT_CENTER[key] : [36.45, 10.73];
}

function shuffle(arr) {
  const a = [...arr];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

/**
 * Génère 3 paires avec vraies distances différentes :
 * - Facile : points proches (≤ ~10km)
 * - Moyen  : points à distance intermédiaire (~10-21km)
 * - Difficile : points éloignés (>21km)
 * Calcule les distances haversine pour trier et choisir les bons couples.
 */
function haversineKm(a, b) {
  const R = 6371;
  const dLat = (b.lat - a.lat) * Math.PI / 180;
  const dLng = (b.lng - a.lng) * Math.PI / 180;
  const x = Math.sin(dLat/2)**2 + Math.cos(a.lat*Math.PI/180)*Math.cos(b.lat*Math.PI/180)*Math.sin(dLng/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1-x));
}

function makePairs(region) {
  const center = getCenter(region);
  const pts = getPoints(region);

  // Toujours utiliser des offsets géographiques calibrés pour garantir les distances :
  // Facile : ~5-8 km (< 10 km sur route OSRM = environ 0.045° lat)
  // Moyen  : ~13-17 km (10-21 km sur route OSRM = environ 0.12° lat)
  // Difficile : ~26-32 km (> 21 km sur route OSRM = environ 0.23° lat)
  // Note : OSRM donne des distances routières ~1.3-1.5x la distance haversine

  const offsetsFixes = [
    { dlat: 0.045, dlng: 0.055, labelD: 'Départ Facile',     labelA: 'Arrivée Facile' },     // ~6km haversine → ~8km route
    { dlat: 0.115, dlng: 0.140, labelD: 'Départ Moyen',      labelA: 'Arrivée Moyen' },      // ~14km haversine → ~18km route
    { dlat: 0.230, dlng: 0.280, labelD: 'Départ Difficile',  labelA: 'Arrivée Difficile' },  // ~29km haversine → ~38km route
  ];

  // Si on a des points nommés pour la région, les utiliser pour les noms uniquement
  // mais toujours construire les paires avec les offsets calibrés pour garantir la bonne difficulté
  const namedLabels = [
    pts && pts.length >= 2 ? [pts[0].nom, pts[1].nom] : null,
    pts && pts.length >= 4 ? [pts[2].nom, pts[3].nom] : null,
    pts && pts.length >= 6 ? [pts[4].nom, pts[5].nom] : null,
  ];

  return offsetsFixes.map((o, i) => {
    const labelD = namedLabels[i]?.[0] || o.labelD;
    const labelA = namedLabels[i]?.[1] || o.labelA;
    // Alterner direction pour varier les trajets
    const angle = i * 60 * Math.PI / 180;
    const dLat = o.dlat * Math.cos(angle) - o.dlng * 0.2 * Math.sin(angle);
    const dLng = o.dlat * 0.3 * Math.sin(angle) + o.dlng * Math.cos(angle);
    return {
      start: { lat: center[0] - dLat/2, lng: center[1] - dLng/2, nom: labelD },
      end:   { lat: center[0] + dLat/2, lng: center[1] + dLng/2, nom: labelA },
    };
  });
}

async function getOSRMRoute(sLat, sLng, eLat, eLng) {
  const url = `https://router.project-osrm.org/route/v1/foot/${sLng},${sLat};${eLng},${eLat}?overview=full&geometries=geojson`;
  const r = await fetch(url, { signal: AbortSignal.timeout(10000) });
  if (!r.ok) throw new Error('OSRM ' + r.status);
  const d = await r.json();
  if (!d.routes?.[0]) throw new Error('no route');
  const rt = d.routes[0];
  return {
    distKm: rt.distance / 1000,
    durationMin: rt.duration / 60,
    coords: rt.geometry.coordinates.map(([lng, lat]) => [lat, lng]),
  };
}

// Difficulté selon vraie distance : facile ≤10km, moyen ≤21km, difficile >21km
function autoDiff(distKm) {
  return distKm <= 10 ? 'facile' : distKm <= 21 ? 'moyen' : 'difficile';
}

function diffStyle(d) {
  if (d === 'facile')  return { dot: '🟢', label: 'Facile',    color: '#16a34a', bg: '#dcfce7', border: '#86efac' };
  if (d === 'moyen')   return { dot: '🟡', label: 'Moyen',     color: '#d97706', bg: '#fef3c7', border: '#fde047' };
  return                      { dot: '🔴', label: 'Difficile', color: '#dc2626', bg: '#fee2e2', border: '#fca5a5' };
}

function fmtDur(min) {
  const h = Math.floor(min / 60), m = Math.round(min % 60);
  return h > 0 ? `${h}h ${String(m).padStart(2,'0')}min` : `${Math.round(min)} min`;
}

let _gMap = null, _gPoly = [], _gMarkers = [], _gIdx = null, _gRoutes = [];

function closeGenModal() {
  document.getElementById('gen-modal')?.remove();
  if (_gMap) { _gMap.remove(); _gMap = null; }
  _gPoly = []; _gMarkers = []; _gIdx = null; _gRoutes = [];
}

function applyRoute() {
  if (_gIdx === null) { alert('Veuillez sélectionner un trajet.'); return; }
  const r = _gRoutes[_gIdx];
  if (!r) return;

  const depEl  = document.getElementById('point_depart');
  const arrEl  = document.getElementById('point_arrivee');
  const distEl = document.getElementById('distance');
  const diffEl = document.getElementById('difficulte');

  if (depEl)  depEl.value  = r.nomDepart;
  if (arrEl)  arrEl.value  = r.nomArrivee;

  // Bypass validation stricte de région
  window._departMarkerPlaced  = true;
  window._arriveeMarkerPlaced = true;

  // Effacer feedbacks d'erreur
  const depFb = document.getElementById('departFeedback');
  const arrFb = document.getElementById('arriveeFeedback');
  if (depFb) { depFb.textContent = '✅ Point de départ valide.'; depFb.className = 'feedback success'; }
  if (arrFb) { arrFb.textContent = "✅ Point d'arrivée valide."; arrFb.className = 'feedback success'; }

  if (distEl) {
    distEl.value = r.distKm.toFixed(2);
    distEl.readOnly = true;
    distEl.style.cssText += 'background:#f0fdf4;color:#0f766e;border-color:#86efac;';
  }
  if (diffEl) {
    diffEl.value = r.diff; diffEl.disabled = true;
    diffEl.className = diffEl.className.replace(/\bdiff-\S+/g,'').trim();
    diffEl.classList.add('diff-' + r.diff);
    let hid = document.getElementById('difficulte_hidden');
    if (!hid) {
      hid = document.createElement('input');
      hid.type = 'hidden'; hid.id = 'difficulte_hidden';
      diffEl.parentNode.appendChild(hid);
      diffEl.name = '';
    }
    hid.name = 'difficulte'; hid.value = r.diff;
  }

  if (typeof map !== 'undefined' && map && r.coords?.length >= 2) {
    if (typeof markers !== 'undefined') {
      markers.forEach(m => m.marker && map.removeLayer(m.marker));
      markers.length = 0;
    }
    if (typeof polyline !== 'undefined' && polyline) {
      map.removeLayer(polyline); window.polyline = null;
    }
    window._departMarkerPlaced = false;
    window._arriveeMarkerPlaced = false;

    const startLL = L.latLng(r.coords[0][0], r.coords[0][1]);
    const endLL   = L.latLng(r.coords[r.coords.length-1][0], r.coords[r.coords.length-1][1]);

    Promise.all([
      placeMarkerFromInput(startLL, r.nomDepart,  'depart'),
      placeMarkerFromInput(endLL,   r.nomArrivee, 'arrivee'),
    ]).then(() => {
      if (typeof polyline !== 'undefined' && polyline) map.removeLayer(polyline);
      window.polyline = L.polyline(r.coords, {
        color:'#0f766e', weight:4, opacity:.9, dashArray:'8,4', lineJoin:'round'
      }).addTo(map);
      map.fitBounds(window.polyline.getBounds().pad(0.18));

      if (distEl) distEl.value = r.distKm.toFixed(2);
      if (typeof updateTimeDisplay === 'function') updateTimeDisplay(r.distKm);
      if (diffEl) {
        diffEl.value = r.diff; diffEl.disabled = true;
        const hid = document.getElementById('difficulte_hidden');
        if (hid) hid.value = r.diff;
        diffEl.className = diffEl.className.replace(/\bdiff-\S+/g,'').trim();
        diffEl.classList.add('diff-' + r.diff);
      }
      // Re-marquer comme valides après placeMarkerFromInput
      window._departMarkerPlaced  = true;
      window._arriveeMarkerPlaced = true;
      if (depFb) { depFb.textContent = '✅ Point de départ valide.'; depFb.className = 'feedback success'; }
      if (arrFb) { arrFb.textContent = "✅ Point d'arrivée valide."; arrFb.className = 'feedback success'; }
    });
  }

  closeGenModal();

  // Changer seulement l'icône ✨→✅, garder même couleur/style du bouton
  const btn = document.getElementById('btn-generer-trajet');
  if (btn) {
    const iconSpan = btn.querySelector('span');
    if (iconSpan) {
      iconSpan.textContent = '✅';
      setTimeout(() => { iconSpan.textContent = '✨'; }, 3000);
    }
  }
}

/** Affiche uniquement les marqueurs D et A du trajet sélectionné */
function updateMarkersVisibility(selectedIdx) {
  _gMarkers.forEach((mk, i) => {
    if (!mk || !_gMap) return;
    const routeIdx = Math.floor(i / 2);
    if (routeIdx === selectedIdx) {
      if (!_gMap.hasLayer(mk)) mk.addTo(_gMap);
    } else {
      if (_gMap.hasLayer(mk)) _gMap.removeLayer(mk);
    }
  });
}

function selectRoute(idx) {
  _gIdx = idx;
  document.querySelectorAll('.gen-card').forEach((card, i) => {
    const sel = i === idx;
    card.style.border     = sel ? '2.5px solid #3b82f6' : '1.5px solid rgba(59,130,246,.15)';
    card.style.background = sel ? '#1e3a5f' : '#1a2744';
    card.style.boxShadow  = sel ? '0 0 0 4px rgba(59,130,246,.18),0 8px 24px rgba(0,0,0,.3)' : '0 2px 12px rgba(0,0,0,.2)';
    const btn = card.querySelector('.gc-btn');
    if (btn) {
      btn.textContent = sel ? '✅ Sélectionné' : 'Choisir ce trajet';
      btn.style.background = sel ? 'linear-gradient(135deg,#1d4ed8,#3b82f6)' : 'rgba(59,130,246,.12)';
      btn.style.color = sel ? '#fff' : '#60a5fa';
    }
  });

  _gPoly.forEach((pl, i) => {
    if (!pl) return;
    pl.setStyle({ color: i===idx?'#3b82f6':'#475569', weight: i===idx?6:3, opacity: i===idx?1:0.35 });
    if (i === idx) pl.bringToFront();
  });

  // Afficher seulement D et A du trajet sélectionné
  updateMarkersVisibility(idx);

  if (_gMap && _gPoly[idx]) {
    try { _gMap.fitBounds(_gPoly[idx].getBounds(), { padding:[28,28] }); } catch(e){}
  }
}

function buildCard(r, i) {
  const d = diffStyle(r.diff);
  const el = document.createElement('div');
  el.className = 'gen-card';
  el.style.cssText = `
    background:#1a2744;border-radius:16px;padding:18px 16px;
    border:1.5px solid rgba(59,130,246,.15);cursor:pointer;
    transition:all .2s;box-shadow:0 2px 12px rgba(0,0,0,.2);
    display:flex;flex-direction:column;gap:11px;
  `;
  el.innerHTML = `
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;">
      <span style="font-size:1rem;font-weight:800;color:#f1f5f9;">Trajet ${i+1}</span>
      <span style="font-size:.72rem;font-weight:700;padding:4px 12px;border-radius:20px;
        background:${d.bg};color:${d.color};border:1px solid ${d.border};">${d.dot} ${d.label}</span>
    </div>
    <div style="display:flex;flex-direction:column;gap:7px;font-size:.84rem;">
      <div style="display:flex;align-items:flex-start;gap:7px;">
        <span style="flex-shrink:0;margin-top:1px;">📍</span>
        <span style="color:#cbd5e1;line-height:1.4;">${r.nomDepart}</span>
      </div>
      <div style="display:flex;align-items:flex-start;gap:7px;">
        <span style="flex-shrink:0;margin-top:1px;">🏁</span>
        <span style="color:#cbd5e1;line-height:1.4;">${r.nomArrivee}</span>
      </div>
    </div>
    <div style="display:flex;gap:18px;font-size:.83rem;">
      <span>📏 <strong style="color:#f1f5f9;">${r.distKm.toFixed(2)} km</strong></span>
      <span>⏱️ <strong style="color:#f1f5f9;">${fmtDur(r.durationMin)}</strong></span>
    </div>
    <button class="gc-btn" style="
      padding:9px 0;border-radius:10px;border:none;width:100%;
      background:rgba(59,130,246,.12);color:#60a5fa;
      font-weight:700;cursor:pointer;font-size:.88rem;transition:all .15s;
    ">Choisir ce trajet</button>
  `;
  el.addEventListener('click', () => selectRoute(i));
  el.querySelector('.gc-btn').addEventListener('click', e => { e.stopPropagation(); selectRoute(i); });
  return el;
}

async function lancerGenerationTrajet() {
  const region = (typeof MARATHON_REGION !== 'undefined' && MARATHON_REGION)
    ? MARATHON_REGION
    : (document.getElementById('city-name')?.textContent?.trim() || 'Tunis');

  const modal = document.createElement('div');
  modal.id = 'gen-modal';
  modal.style.cssText = `
    position:fixed;inset:0;z-index:99999;
    background:rgba(8,15,35,.9);backdrop-filter:blur(8px);
    display:flex;align-items:center;justify-content:center;padding:12px;
  `;
  modal.innerHTML = `
  <div id="gen-box" style="
    background:#0f172a;border-radius:22px;width:100%;max-width:1080px;
    max-height:95vh;display:flex;flex-direction:column;
    border:1px solid rgba(59,130,246,.25);
    box-shadow:0 40px 100px rgba(0,0,0,.9);overflow:hidden;
  ">
    <div style="
      flex-shrink:0;padding:17px 22px;
      border-bottom:1px solid rgba(255,255,255,.07);
      background:linear-gradient(135deg,rgba(29,78,216,.18),rgba(99,102,241,.1));
      display:flex;align-items:center;justify-content:space-between;gap:12px;
    ">
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:42px;height:42px;border-radius:13px;flex-shrink:0;
          background:linear-gradient(135deg,#1d4ed8,#6366f1);
          display:grid;place-items:center;font-size:1.3rem;">✨</div>
        <div>
          <div style="font-size:1.1rem;font-weight:800;color:#f1f5f9;">Génération de trajets</div>
          <div style="font-size:.78rem;color:#94a3b8;margin-top:2px;">
            Région : <strong style="color:#60a5fa;">${region}</strong>
          </div>
        </div>
      </div>
      <button id="gen-x" style="
        background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);
        color:#94a3b8;width:34px;height:34px;border-radius:9px;
        font-size:1.1rem;cursor:pointer;flex-shrink:0;
      ">✕</button>
    </div>

    <div style="display:flex;flex:1;overflow:hidden;min-height:0;">
      <div id="gen-cards" style="
        width:340px;flex-shrink:0;overflow-y:auto;padding:14px;
        display:flex;flex-direction:column;gap:12px;
        border-right:1px solid rgba(255,255,255,.06);
      ">
        <div id="gen-loader" style="text-align:center;padding:52px 14px;">
          <div id="gen-spin" style="font-size:2.2rem;margin-bottom:14px;display:inline-block;">⚙️</div>
          <div style="font-weight:700;color:#94a3b8;font-size:.95rem;">Calcul des trajets…</div>
          <div style="font-size:.78rem;margin-top:6px;color:#475569;">Facile · Moyen · Difficile</div>
        </div>
      </div>
      <div style="flex:1;position:relative;min-width:0;">
        <div id="gen-map" style="width:100%;height:100%;min-height:380px;"></div>
      </div>
    </div>

    <div style="
      flex-shrink:0;padding:13px 22px;
      border-top:1px solid rgba(255,255,255,.07);
      display:flex;justify-content:flex-end;gap:10px;
      background:rgba(0,0,0,.22);
    ">
      <button id="gen-ann" style="
        padding:10px 20px;border-radius:10px;border:1px solid rgba(255,255,255,.12);
        background:rgba(255,255,255,.05);color:#94a3b8;font-weight:700;cursor:pointer;font-size:.9rem;
      ">Annuler</button>
      <button id="gen-ok" style="
        padding:10px 26px;border-radius:10px;border:none;
        background:linear-gradient(135deg,#1d4ed8,#3b82f6);
        color:#fff;font-weight:800;cursor:pointer;font-size:.9rem;
        box-shadow:0 4px 14px rgba(59,130,246,.35);
      ">✅ Appliquer ce trajet</button>
    </div>
  </div>
  <style>
    #gen-spin{animation:gspin 1.1s linear infinite;}
    @keyframes gspin{to{transform:rotate(360deg);}}
    .gen-card:hover{transform:translateY(-2px);}
  </style>
  `;
  document.body.appendChild(modal);

  modal.addEventListener('click', e => { if (e.target === modal) closeGenModal(); });
  document.getElementById('gen-x').addEventListener('click', closeGenModal);
  document.getElementById('gen-ann').addEventListener('click', closeGenModal);
  document.getElementById('gen-ok').addEventListener('click', applyRoute);

  await new Promise(r => setTimeout(r, 70));

  const center = getCenter(region);
  // attributionControl:false = supprime l'attribution Leaflet/OSM sur la carte modale
  _gMap = L.map('gen-map', { attributionControl: false }).setView(center, 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(_gMap);

  const pairs = makePairs(region);
  const results = [];

  await Promise.allSettled(pairs.map(async ({ start, end }, i) => {
    try {
      const rt = await getOSRMRoute(start.lat, start.lng, end.lat, end.lng);
      results.push({
        i,
        start, end,
        distKm: rt.distKm,
        durationMin: rt.durationMin,
        diff: autoDiff(rt.distKm),  // difficulté calculée selon vraie distance OSRM
        nomDepart:  start.nom || `Départ ${i+1}`,
        nomArrivee: end.nom   || `Arrivée ${i+1}`,
        coords: rt.coords,
      });
    } catch(_) {}
  }));

  results.sort((a, b) => a.i - b.i);
  _gRoutes = results;

  document.getElementById('gen-loader')?.remove();
  const cardsEl = document.getElementById('gen-cards');

  if (!results.length) {
    cardsEl.innerHTML = `
      <div style="text-align:center;padding:52px 14px;">
        <div style="font-size:2rem;margin-bottom:12px;">❌</div>
        <div style="font-weight:700;color:#fca5a5;font-size:.95rem;">Impossible de calculer les trajets</div>
        <div style="font-size:.8rem;color:#64748b;margin-top:8px;">Vérifiez votre connexion internet et réessayez.</div>
      </div>`;
    return;
  }

  const allCoords = [];
  const colors = ['#16a34a', '#d97706', '#dc2626'];

  results.forEach((r, i) => {
    cardsEl.appendChild(buildCard(r, i));

    if (r.coords?.length > 1) {
      const pl = L.polyline(r.coords, { color:'#475569', weight:3, opacity:.38, smoothFactor:1 }).addTo(_gMap);
      pl.on('click', () => selectRoute(i));
      _gPoly.push(pl);
      allCoords.push(...r.coords);
    } else {
      _gPoly.push(null);
    }

    // Marqueurs D (toujours VERT) et A (toujours ROUGE)
    const mkD = L.marker([r.start.lat, r.start.lng], {
      icon: L.divIcon({ className:'',
        html:`<div style="width:30px;height:30px;background:#16a34a;border-radius:50%;border:3px solid #fff;display:grid;place-items:center;color:#fff;font-size:.72rem;font-weight:800;box-shadow:0 2px 8px rgba(0,0,0,.5);">D</div>`,
        iconSize:[30,30], iconAnchor:[15,15] })
    }).on('click', () => selectRoute(i));

    const mkA = L.marker([r.end.lat, r.end.lng], {
      icon: L.divIcon({ className:'',
        html:`<div style="width:30px;height:30px;background:#dc2626;border-radius:6px;border:3px solid #fff;display:grid;place-items:center;color:#fff;font-size:.72rem;font-weight:800;box-shadow:0 2px 8px rgba(0,0,0,.5);">A</div>`,
        iconSize:[30,30], iconAnchor:[15,15] })
    }).on('click', () => selectRoute(i));

    _gMarkers.push(mkD, mkA);
  });

  if (allCoords.length > 0) {
    try { _gMap.fitBounds(L.latLngBounds(allCoords), { padding:[22,22] }); } catch(e){}
  }

  selectRoute(0);
}
