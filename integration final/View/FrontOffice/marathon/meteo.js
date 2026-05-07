

// ─── Helpers météo ────────────────────────────────────────────────────────────
function weatherIcon(code) {
    if (!code && code !== 0) return '🌡️';
    if (code >= 200 && code < 300) return '⛈️';
    if (code >= 300 && code < 400) return '🌦️';
    if (code >= 500 && code < 600) return '🌧️';
    if (code >= 600 && code < 700) return '❄️';
    if (code >= 700 && code < 800) return '🌫️';
    if (code === 800) return '☀️';
    if (code === 801) return '🌤️';
    if (code <= 804) return '⛅';
    return '🌡️';
}

function isBadWeather(temp, rain, wind) {
    const reasons = [];
    // 🌡️ Température : favorable si < 30°C, défavorable si >= 30°C
    if (temp >= 30) reasons.push('🔥 Chaleur excessive (' + temp + '°C — dangereux pour les coureurs, seuil : 30°C)');
    // 🌧️ Pluie : favorable si < 20 mm, défavorable si >= 20 mm
    if (rain >= 20) reasons.push('🌧️ Forte pluie prévue (' + rain.toFixed(1) + ' mm — seuil : 20 mm)');
    // 🌬️ Vent : favorable si < 40 km/h, défavorable si >= 40 km/h
    if (wind >= 40) reasons.push('🌪️ Vent fort (' + wind.toFixed(0) + ' km/h — seuil : 40 km/h)');
    return reasons;
}

function formatDate(dateStr) {
    const d = new Date(dateStr + 'T12:00:00');
    return d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

// ─── Parse regions from input (separator: tiret "-" ou virgule) ───────────────
function parseRegions(rawInput) {
    if (!rawInput) return [];
    // Support both "-" and "," as separator
    return rawInput.split(/[-,]/).map(s => s.trim()).filter(Boolean);
}

// ─── Fetch météo pour une date précise + une ville ───────────────────────────
async function fetchWeatherForDate(city, dateStr) {
    const geoUrl = `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(city)}&count=1&language=fr&format=json`;
    let lat, lon, resolvedCity;
    try {
        const geoResp = await fetch(geoUrl);
        const geoData = await geoResp.json();
        if (!geoData.results || geoData.results.length === 0) return null;
        lat = geoData.results[0].latitude;
        lon = geoData.results[0].longitude;
        resolvedCity = geoData.results[0].name + ', ' + (geoData.results[0].country || '');
    } catch (e) { return null; }

    const today = new Date();
    const target = new Date(dateStr + 'T12:00:00');
    const diffDays = Math.round((target - today) / (1000 * 86400));

    if (diffDays <= 16) {
        const wUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,windspeed_10m_max,weathercode&timezone=auto&start_date=${dateStr}&end_date=${dateStr}`;
        try {
            const wResp = await fetch(wUrl);
            const wData = await wResp.json();
            if (!wData.daily) return null;
            const d = wData.daily;
            return {
                city: resolvedCity, date: dateStr,
                tempMax: Math.round(d.temperature_2m_max[0]),
                tempMin: Math.round(d.temperature_2m_min[0]),
                rain: d.precipitation_sum[0] || 0,
                wind: d.windspeed_10m_max[0] || 0,
                code: d.weathercode[0],
                isEstimate: false
            };
        } catch (e) { return null; }
    } else {
        // Analyse climatique historique : moyenne des 3 dernières années
        const month = target.getMonth() + 1;
        const day = target.getDate();
        const pad = n => String(n).padStart(2, '0');
        const results = [];
        for (let y = 1; y <= 3; y++) {
            const yr = target.getFullYear() - y;
            const d1 = `${yr}-${pad(month)}-${pad(Math.max(1, day - 3))}`;
            const d2 = `${yr}-${pad(month)}-${pad(Math.min(28, day + 3))}`;
            try {
                const r = await fetch(`https://archive-api.open-meteo.com/v1/archive?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,windspeed_10m_max&timezone=auto&start_date=${d1}&end_date=${d2}`);
                const rj = await r.json();
                if (rj.daily && rj.daily.temperature_2m_max) {
                    const avg = arr => arr.reduce((a, b) => a + (b || 0), 0) / arr.length;
                    results.push({
                        tempMax: avg(rj.daily.temperature_2m_max),
                        tempMin: avg(rj.daily.temperature_2m_min),
                        rain: avg(rj.daily.precipitation_sum),
                        wind: avg(rj.daily.windspeed_10m_max)
                    });
                }
            } catch (e) {}
        }
        if (results.length === 0) return null;
        const avg = f => Math.round(results.reduce((a, b) => a + b[f], 0) / results.length * 10) / 10;
        const tm = avg('tempMax'), tr = avg('rain');
        const code = tr > 5 ? 500 : tm > 30 ? 800 : tm > 20 ? 801 : 803;
        return {
            city: resolvedCity, date: dateStr,
            tempMax: Math.round(avg('tempMax')),
            tempMin: Math.round(avg('tempMin')),
            rain: avg('rain'), wind: avg('wind'),
            code, isEstimate: true
        };
    }
}

// ─── Fetch météo pour TOUTES les régions + merger les résultats ───────────────
async function fetchWeatherAllRegions(regions, dateStr) {
    const results = await Promise.all(regions.map(r => fetchWeatherForDate(r, dateStr)));
    const valid = results.filter(Boolean);
    if (valid.length === 0) return null;
    if (valid.length === 1) return valid[0];

    // Merger : prendre le pire cas pour la sécurité (tempMax le plus chaud, rain/wind le plus fort)
    const merged = {
        city: regions.join(', '),
        date: dateStr,
        tempMax: Math.max(...valid.map(d => d.tempMax)),
        tempMin: Math.min(...valid.map(d => d.tempMin)),
        rain: Math.max(...valid.map(d => d.rain)),
        wind: Math.max(...valid.map(d => d.wind)),
        code: valid[0].code, // code météo de la 1ère région
        isEstimate: valid.some(d => d.isEstimate),
        perRegion: valid // données détaillées par région
    };
    return merged;
}

// ─── Fetch les meilleures dates pour TOUTES les régions (score combiné) ────────
async function fetchBestDatesAllRegions(regions) {
    try {
        // Géocoder toutes les régions
        const geoResults = await Promise.all(regions.map(async city => {
            try {
                const geoResp = await fetch(`https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(city)}&count=1&language=fr&format=json`);
                const geoData = await geoResp.json();
                if (!geoData.results || geoData.results.length === 0) return null;
                return { latitude: geoData.results[0].latitude, longitude: geoData.results[0].longitude, city };
            } catch(e) { return null; }
        }));
        const validGeo = geoResults.filter(Boolean);
        if (validGeo.length === 0) return null;

        const start = new Date(); start.setDate(start.getDate() + 1);
        const end   = new Date(); end.setDate(end.getDate() + 14);
        const startStr = start.toISOString().slice(0,10);
        const endStr   = end.toISOString().slice(0,10);

        // Fetch météo pour chaque région
        const weatherAll = await Promise.all(validGeo.map(async geo => {
            try {
                const wUrl = `https://api.open-meteo.com/v1/forecast?latitude=${geo.latitude}&longitude=${geo.longitude}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,windspeed_10m_max,weathercode&timezone=auto&start_date=${startStr}&end_date=${endStr}`;
                const wResp = await fetch(wUrl);
                const wData = await wResp.json();
                if (!wData.daily) return null;
                return { geo, daily: wData.daily };
            } catch(e) { return null; }
        }));
        const validWeather = weatherAll.filter(Boolean);
        if (validWeather.length === 0) return null;

        // Référence des dates depuis la 1ère région
        const dates = validWeather[0].daily.time;

        // Calculer : garder seulement les jours avec conditions FAVORABLES
        // Favorable = temp < 30°C ET pluie < 20mm ET vent < 40km/h
        const allDays = dates.map((dt, i) => {
            // Prendre le pire cas de chaque métrique parmi toutes les régions
            const tempMax = Math.max(...validWeather.map(w => Math.round(w.daily.temperature_2m_max[i] || 0)));
            const tempMin = Math.min(...validWeather.map(w => Math.round(w.daily.temperature_2m_min[i] || 0)));
            const rain    = Math.max(...validWeather.map(w => w.daily.precipitation_sum[i] || 0));
            const wind    = Math.max(...validWeather.map(w => w.daily.windspeed_10m_max[i] || 0));
            const code    = validWeather[0].daily.weathercode[i];

            // Conditions favorables selon les critères définis
            const isFavorable = tempMax < 30 && rain < 20 && wind < 40;

            // Score de confort (pour trier les meilleurs)
            let score = 100;
            if (tempMax > 20) score -= (tempMax - 20) * 3; // préférer 20-29°C
            if (rain > 0)  score -= rain * 2;
            if (wind > 20) score -= (wind - 20) * 1.5;

            return { date: dt, tempMax, tempMin, rain, wind, code, score, isFavorable };
        });

        // Garder UNIQUEMENT les jours favorables, triés par score décroissant
        const goodDays = allDays.filter(d => d.isFavorable).sort((a, b) => b.score - a.score);
        return goodDays; // peut être vide si aucune date favorable
    } catch (e) { return null; }
}

// ─── Fetch les 14 meilleures dates (wrapper single city) ─────────────────────
async function fetchBestDates(city) {
    return fetchBestDatesAllRegions([city]);
}

// ─── Render résultat météo (multi-région) ─────────────────────────────────────
function renderWeatherResult(data) {
    const zone = document.getElementById('meteoResult');
    if (!data) {
        zone.innerHTML = `<div class="meteo-alert"><div class="meteo-alert-title">⚠️ Météo indisponible</div><p>Vérifiez le nom de la région ou votre connexion internet.</p></div>`;
        zone.style.display = 'block';
        return;
    }
    const icon = weatherIcon(data.code);
    const reasons = isBadWeather(data.tempMax, data.rain, data.wind);
    const isGood = reasons.length === 0;

    const estimateNote = data.isEstimate
        ? `<div class="estimate-note"><strong>📊 Estimation climatique</strong> — Pour les dates lointaines, la météo exacte n'est pas disponible. BarchaThon utilise des moyennes historiques des années précédentes à la même période afin d'estimer les conditions générales et d'aider à choisir les dates les plus favorables pour l'organisation d'un marathon.</div>`
        : '';

    // Affichage détaillé par région si multi-région
    let perRegionHTML = '';
    if (data.perRegion && data.perRegion.length > 1) {
        perRegionHTML = `<div style="margin-top:12px;padding:10px 14px;background:rgba(255,255,255,0.7);border-radius:12px;font-size:0.88rem;">
            <div style="font-weight:700;color:#475569;margin-bottom:6px;">📍 Détail par région :</div>
            ${data.perRegion.map(r => {
                const bad = isBadWeather(r.tempMax, r.rain, r.wind);
                const ok = bad.length === 0;
                return `<div style="display:flex;align-items:center;gap:8px;padding:4px 0;color:${ok?'#065f46':'#b91c1c'};">
                    <span>${weatherIcon(r.code)}</span>
                    <span style="font-weight:700;min-width:90px;">${r.city.split(',')[0]}</span>
                    <span>${r.tempMax}°C / ${r.tempMin}°C · ${r.rain.toFixed(1)}mm · ${r.wind.toFixed(0)}km/h</span>
                    <span>${ok ? '✅' : '⚠️'}</span>
                </div>`;
            }).join('')}
        </div>`;
    }

    const alertHTML = !isGood
        ? `<div class="meteo-alert" style="margin-top:12px;">
            <div class="meteo-alert-title">⚠️ Conditions défavorables détectées</div>
            <p>Cette date présente des risques pour les coureurs :</p>
            <ul class="meteo-alert-reasons">${reasons.map(r => '<li>' + r + '</li>').join('')}</ul>
            <p>Nous vous recommandons de choisir une autre date.</p>
            <button type="button" class="suggested-date-btn" onclick="suggestBetterDate()">📅 Voir les meilleures dates recommandées par IA</button>
           </div>`
        : '';

    zone.innerHTML = `
    ${estimateNote}
    <div class="meteo-result" style="background:${isGood ? 'linear-gradient(135deg,#f0fdf9,#e8f4fd)' : 'linear-gradient(135deg,#fff7ed,#fef2f2)'};border-color:${isGood ? '#a7f3d0' : '#fca5a5'};">
        <div class="meteo-header">
            <div class="meteo-icon-big">${icon}</div>
            <div>
                <div class="meteo-title">${isGood ? '✅ Conditions favorables' : '⚠️ Conditions à risque'} — ${data.city}</div>
                <div class="meteo-subtitle">${formatDate(data.date)}</div>
            </div>
        </div>
        <div class="meteo-grid">
            <div class="meteo-cell"><div class="val">${data.tempMax}°C</div><div class="lbl">Temp. max</div></div>
            <div class="meteo-cell"><div class="val">${data.tempMin}°C</div><div class="lbl">Temp. min</div></div>
            <div class="meteo-cell"><div class="val">${data.rain.toFixed(1)} mm</div><div class="lbl">Pluie prévue</div></div>
            <div class="meteo-cell"><div class="val">${data.wind.toFixed(0)} km/h</div><div class="lbl">Vent max</div></div>
        </div>
        ${perRegionHTML}
        ${isGood ? '<div style="color:#065f46;font-weight:700;font-size:0.95rem;">🎉 Excellent choix ! Les conditions sont idéales pour un marathon.</div>' : ''}
    </div>
    ${alertHTML}`;
    zone.style.display = 'block';
}

// ─── Modal best-dates overlay ─────────────────────────────────────────────────
function ensureBestDatesModal() {
    if (document.getElementById('bestDatesModal')) return;
    const style = document.createElement('style');
    style.textContent = `
    #bestDatesModal {
        display:none; position:fixed; inset:0; z-index:9999;
        background:rgba(16,42,67,.55); backdrop-filter:blur(4px);
        align-items:center; justify-content:center; padding:20px;
    }
    #bestDatesModal.open { display:flex; }
    #bestDatesModalBox {
        background:#fff; border-radius:24px; width:min(560px,100%);
        max-height:85vh; display:flex; flex-direction:column;
        box-shadow:0 32px 80px rgba(16,42,67,.22); overflow:hidden;
        animation:bdSlideIn .25s cubic-bezier(.22,1,.36,1);
    }
    @keyframes bdSlideIn { from{transform:translateY(24px);opacity:0} to{transform:translateY(0);opacity:1} }
    #bestDatesModalHeader {
        display:flex; align-items:center; justify-content:space-between;
        padding:20px 24px 16px; border-bottom:1px solid #e6edf3; flex-shrink:0;
    }
    #bestDatesModalHeader .bd-title { font-size:1.05rem; font-weight:800; color:#065f46; display:flex; align-items:center; gap:8px; }
    #bestDatesModalClose {
        background:none; border:none; cursor:pointer; font-size:1.4rem; line-height:1;
        color:#94a3b8; padding:4px 8px; border-radius:8px; transition:background .15s;
    }
    #bestDatesModalClose:hover { background:#f1f5f9; color:#475569; }
    #bestDatesModalBody { overflow-y:auto; padding:16px 20px; flex:1; }
    #bestDatesModalBody::-webkit-scrollbar { width:6px; }
    #bestDatesModalBody::-webkit-scrollbar-thumb { background:#a7f3d0; border-radius:6px; }
    .bd-date-option {
        background:#f8fafc; border-radius:14px; padding:14px 16px;
        display:flex; align-items:center; gap:12px; justify-content:space-between;
        margin-bottom:10px; border:1px solid #e2e8f0; cursor:pointer;
        transition:transform .15s, box-shadow .15s, background .15s;
    }
    .bd-date-option:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(16,42,67,.1); background:#fff; border-color:#a7f3d0; }
    .bd-date-option .bd-icon { font-size:1.5rem; flex-shrink:0; }
    .bd-date-option .bd-label { font-weight:700; color:#102a43; font-size:0.95rem; }
    .bd-date-option .bd-weather { display:flex; align-items:center; gap:8px; font-size:0.88rem; color:#64748b; flex-wrap:wrap; margin-top:4px; }
    .bd-date-option .bd-btn { background:linear-gradient(135deg,#0f766e,#14b8a6); color:#fff; border:none; border-radius:10px; padding:8px 16px; font-weight:700; font-size:0.85rem; cursor:pointer; white-space:nowrap; flex-shrink:0; }
    .bd-date-option .bd-btn:hover { opacity:.88; }
    `;
    document.head.appendChild(style);

    const modal = document.createElement('div');
    modal.id = 'bestDatesModal';
    modal.innerHTML = `
    <div id="bestDatesModalBox">
        <div id="bestDatesModalHeader">
            <span class="bd-title">🌤️ Meilleures dates (14 prochains jours)</span>
            <button id="bestDatesModalClose" type="button" title="Fermer">✕</button>
        </div>
        <div id="bestDatesModalBody"></div>
    </div>`;
    document.body.appendChild(modal);

    document.getElementById('bestDatesModalClose').addEventListener('click', closeBestDatesModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeBestDatesModal(); });
}

function closeBestDatesModal() {
    const m = document.getElementById('bestDatesModal');
    if (m) m.classList.remove('open');
}

function openBestDatesModal(days) {
    ensureBestDatesModal();
    const body = document.getElementById('bestDatesModalBody');
    if (!days || days.length === 0) {
        body.innerHTML = `<div style="padding:20px;text-align:center;">
            <div style="font-size:2rem;margin-bottom:12px;">⚠️</div>
            <div style="font-weight:700;color:#b91c1c;font-size:1rem;margin-bottom:8px;">Aucune date favorable dans les 14 prochains jours</div>
            <div style="color:#64748b;font-size:0.9rem;line-height:1.6;">Les conditions météo prévues sont défavorables pour toutes les dates disponibles.<br>
            <strong>Critères favorables :</strong> Température &lt; 30°C · Pluie &lt; 20mm · Vent &lt; 40 km/h<br><br>
            Réessayez dans quelques jours ou choisissez une autre région.</div>
        </div>`;
    } else {
        body.innerHTML = days.map(day => `
        <div class="bd-date-option">
            <div class="bd-icon">${weatherIcon(day.code)}</div>
            <div style="flex:1;min-width:0;">
                <div class="bd-label">${formatDate(day.date)}</div>
                <div class="bd-weather">
                    <span>🌡️ ${day.tempMax}°C / ${day.tempMin}°C</span>
                    <span>💧 ${day.rain.toFixed(1)}mm</span>
                    <span>💨 ${day.wind.toFixed(0)}km/h</span>
                </div>
            </div>
            <button type="button" class="bd-btn" onclick="chooseDate('${day.date}')">Choisir →</button>
        </div>`).join('');
    }
    document.getElementById('bestDatesModal').classList.add('open');
}

// ─── Render meilleures dates (kept for backward compat, now opens modal) ──────
function renderBestDates(days) {
    openBestDatesModal(days);
    const panel = document.getElementById('bestDatesPanel');
    if (panel) panel.style.display = 'none';
}

// ─── Choisir une date suggérée ────────────────────────────────────────────────
function chooseDate(dateStr) {
    document.getElementById('date_marathon').value = dateStr;
    closeBestDatesModal();
    const panel = document.getElementById('bestDatesPanel');
    if (panel) panel.style.display = 'none';
    clearTimeout(window._meteoTimer);
    window._meteoTimer = setTimeout(handleDateChange, 300);
}

// ─── Suggérer de meilleures dates (toutes les régions) ───────────────────────
async function suggestBetterDate() {
    const rawRegion = document.getElementById('region_marathon').value.trim();
    if (!rawRegion) { alert("Veuillez d'abord saisir la région du marathon."); return; }
    const { regions, valid: regionValid, invalid: invalidRegion } = parseAndValidateRegions(rawRegion);
    if (!regionValid) {
        alert(`« ${invalidRegion || rawRegion} » n'est pas un gouvernorat tunisien valide.\nVeuillez corriger la région avant de chercher des dates.`);
        return;
    }
    ensureBestDatesModal();
    // Update modal title to show all regions
    const titleEl = document.querySelector('#bestDatesModalHeader .bd-title');
    if (titleEl) titleEl.innerHTML = `🌤️ Meilleures dates — ${regions.join(', ')}`;
    const body = document.getElementById('bestDatesModalBody');
    body.innerHTML = '<div style="display:flex;align-items:center;gap:10px;padding:20px;color:#64748b;font-weight:600;"><div style="width:20px;height:20px;border:3px solid #e2e8f0;border-top-color:#0f766e;border-radius:50%;animation:spin .8s linear infinite;flex-shrink:0;"></div>Chargement des meilleures dates…</div>';
    document.getElementById('bestDatesModal').classList.add('open');
    const days = await fetchBestDatesAllRegions(regions);
    openBestDatesModal(days);
}

// ─── Liste des 24 gouvernorats tunisiens ────────────────────────────────────
const TUNISIA_GOVERNORATES = ['Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa','Jendouba','Kairouan','Kasserine','Kébili','Kef','Mahdia','Manouba','Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'];

function normalizeGov(s) { return s.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }

function parseAndValidateRegions(rawInput) {
    if (!rawInput) return { regions: [], valid: false };
    const parts = rawInput.split(/[-,]/).map(s => s.trim()).filter(Boolean);
    if (parts.length === 0) return { regions: [], valid: false };
    const resolved = [];
    for (const p of parts) {
        const match = TUNISIA_GOVERNORATES.find(g => normalizeGov(g) === normalizeGov(p));
        if (!match) return { regions: [], valid: false, invalid: p };
        resolved.push(match);
    }
    return { regions: resolved, valid: true };
}

// ─── Gestionnaire principal (analyse TOUTES les régions) ─────────────────────
async function handleDateChange() {
    const dateEl = document.getElementById('date_marathon');
    const date = dateEl ? dateEl.value : '';
    const regionRaw = document.getElementById('region_marathon').value.trim();
    const { regions, valid: regionValid, invalid: invalidRegion } = parseAndValidateRegions(regionRaw);
    const isMultiRegion = regions.length > 1;
    const TODAY = dateEl ? dateEl.getAttribute('data-today') : '';

    // Vérifier que la date est dans le futur avant tout
    if (!date) { document.getElementById('meteoZone').style.display = 'none'; return; }
    const selectedDate = new Date(date + 'T00:00:00');
    const today = new Date(); today.setHours(0,0,0,0);
    if (selectedDate <= today) {
        document.getElementById('meteoZone').style.display = 'none'; return;
    }

    document.getElementById('meteoZone').style.display = 'block';

    // Supprimer note multi-région si présente (sera recréée si nécessaire)
    const oldNote = document.getElementById('meteoMultiNote');
    if (oldNote) oldNote.remove();

    // Si la région n'est pas saisie ou invalide → ne pas lancer l'analyse météo
    if (!regionRaw) {
        document.getElementById('meteoResult').innerHTML = '<div class="meteo-result" style="background:#f8fafc;border:1px solid #e2e8f0;"><div style="color:#64748b;font-weight:600;">🏙️ Saisissez la <strong>région</strong> (gouvernorat tunisien) pour voir la météo prévue.</div></div>';
        document.getElementById('meteoResult').style.display = 'block';
        document.getElementById('bestDatesPanel').style.display = 'none';
        return;
    }
    if (!regionValid) {
        document.getElementById('meteoResult').innerHTML = `<div class="meteo-alert"><div class="meteo-alert-title">❌ Région invalide</div><p>« <strong>${invalidRegion || regionRaw}</strong> » n'est pas un gouvernorat tunisien valide.<br>La météo ne peut être analysée que pour les 24 gouvernorats de Tunisie.<br><em>Corrigez la région avant de lancer l'analyse.</em></p></div>`;
        document.getElementById('meteoResult').style.display = 'block';
        document.getElementById('bestDatesPanel').style.display = 'none';
        return;
    }

    document.getElementById('meteoLoading').style.display = 'flex';
    document.getElementById('meteoResult').style.display = 'none';
    document.getElementById('bestDatesPanel').style.display = 'none';

    // Analyser TOUTES les régions
    const data = await fetchWeatherAllRegions(regions, date);
    document.getElementById('meteoLoading').style.display = 'none';
    renderWeatherResult(data);
}

// ─── Attacher les événements météo ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const dateEl = document.getElementById('date_marathon');
    const regionEl = document.getElementById('region_marathon');

    if (dateEl) {
        dateEl.addEventListener('change', () => {
            clearTimeout(window._meteoTimer);
            window._meteoTimer = setTimeout(handleDateChange, 300);
        });
    }
    if (regionEl) {
        regionEl.addEventListener('change', () => {
            if (dateEl && dateEl.value) {
                clearTimeout(window._meteoTimer);
                window._meteoTimer = setTimeout(handleDateChange, 500);
            }
        });
        regionEl.addEventListener('blur', () => {
            if (dateEl && dateEl.value) {
                clearTimeout(window._meteoTimer);
                window._meteoTimer = setTimeout(handleDateChange, 300);
            }
        });
    }

    // Auto-déclencher au chargement si date et région déjà remplies (page modifier)
    if (dateEl && dateEl.value && regionEl && regionEl.value.trim()) {
        window._meteoTimer = setTimeout(handleDateChange, 600);
    }
});
