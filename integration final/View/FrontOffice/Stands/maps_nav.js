// ═══════════════════════════════════════════════
// BarchaThon — Real-time Voice Navigation Engine
// ═══════════════════════════════════════════════

(function(){
    const map = L.map('map', {zoomControl:false}).setView([36.8065,10.1815],11);
    L.control.zoom({position:'topright'}).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap',maxZoom:19}).addTo(map);

    // Icons
    const userIcon = L.divIcon({className:'x',html:'<div style="width:20px;height:20px;border-radius:50%;background:#2563eb;border:3px solid white;box-shadow:0 2px 8px rgba(37,99,235,.5)"></div>',iconSize:[20,20],iconAnchor:[10,10]});
    const standIcon = L.divIcon({className:'x',html:'<div style="width:32px;height:32px;border-radius:10px;background:#10b981;border:3px solid white;box-shadow:0 3px 10px rgba(16,185,129,.4);display:flex;align-items:center;justify-content:center;font-size:15px">🏪</div>',iconSize:[32,32],iconAnchor:[16,16]});
    const activeIcon = L.divIcon({className:'x',html:'<div style="width:38px;height:38px;border-radius:12px;background:#e11d48;border:3px solid white;box-shadow:0 4px 14px rgba(225,29,72,.5);display:flex;align-items:center;justify-content:center;font-size:18px">🏪</div>',iconSize:[38,38],iconAnchor:[19,19]});

    let userMarker=null, userLat=36.8065, userLon=10.1815;
    let currentRoute=null, routeSteps=[], currentStepIndex=0;
    let watchId=null, navigating=false;
    let standMarkers={}, activeMarkerId=null;
    let selectedTarget=null;

    // ── Stands from PHP (inline in maps.php via data attributes) ──
    const standItems = document.querySelectorAll('.stand-item');
    const standsArr = [];
    standItems.forEach((el,i)=>{
        const lat=parseFloat(el.dataset.lat), lon=parseFloat(el.dataset.lon);
        const name=el.dataset.name, pos=el.dataset.pos;
        standsArr.push({lat,lon,name,pos,el,idx:i});
        const m = L.marker([lat,lon],{icon:standIcon}).addTo(map);
        m.bindPopup('<strong>'+name+'</strong><br><span style="color:#64748b">📍 '+pos+'</span><br><button onclick="selectStandByIdx('+i+')" style="margin-top:6px;width:100%;padding:6px;border:none;border-radius:8px;background:#2563eb;color:white;font-weight:700;cursor:pointer">🧭 Itinéraire</button>');
        m.sIdx = i;
        standMarkers[i] = m;
    });

    // ── Geolocation ──
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(pos=>{
            userLat=pos.coords.latitude; userLon=pos.coords.longitude;
            userMarker=L.marker([userLat,userLon],{icon:userIcon}).addTo(map).bindPopup('<strong>📍 Vous êtes ici</strong>').openPopup();
            const pts=[[userLat,userLon]];
            standsArr.forEach(s=>pts.push([s.lat,s.lon]));
            map.fitBounds(L.latLngBounds(pts).pad(0.1));
            updateDists();
        },()=>{
            userLat=36.8065;userLon=10.1815;
            userMarker=L.marker([userLat,userLon],{icon:userIcon}).addTo(map).bindPopup('<strong>📍 Position par défaut</strong>').openPopup();
            updateDists();
        },{timeout:8000,enableHighAccuracy:true});
    }

    function haversine(a,b,c,d){const R=6371,dL=(c-a)*Math.PI/180,dN=(d-b)*Math.PI/180,x=Math.sin(dL/2)**2+Math.cos(a*Math.PI/180)*Math.cos(c*Math.PI/180)*Math.sin(dN/2)**2;return R*2*Math.atan2(Math.sqrt(x),Math.sqrt(1-x))}

    function updateDists(){
        if(!userLat)return;
        standItems.forEach(el=>{
            const d=haversine(userLat,userLon,parseFloat(el.dataset.lat),parseFloat(el.dataset.lon));
            el.querySelector('.stand-item-dist').textContent=d.toFixed(1)+' km';
        });
    }

    // ── OSRM instruction translation ──
    function translateInstruction(step){
        let type=step.maneuver.type, mod=step.maneuver.modifier||'';
        let road=step.name||'';
        let icon='➡️', text='';

        switch(type){
            case'depart': icon='🚶'; text='Commencez à marcher'+(road?' sur '+road:''); break;
            case'arrive': icon='🏁'; text='Vous êtes arrivé à destination'+(road?' sur '+road:''); break;
            case'turn':
                if(mod.includes('left')){icon='⬅️';text='Tournez à gauche'+(road?' sur '+road:'');}
                else if(mod.includes('right')){icon='➡️';text='Tournez à droite'+(road?' sur '+road:'');}
                else if(mod.includes('straight')){icon='⬆️';text='Continuez tout droit'+(road?' sur '+road:'');}
                else{icon='↪️';text='Tournez '+(road?' sur '+road:'');}
                break;
            case'new name': icon='⬆️'; text='Continuez sur '+(road||'cette route'); break;
            case'merge': icon='🔀'; text='Rejoignez '+(road||'la route'); break;
            case'fork':
                if(mod.includes('left')){icon='↙️';text='Prenez à gauche'+(road?' sur '+road:'');}
                else{icon='↗️';text='Prenez à droite'+(road?' sur '+road:'');}
                break;
            case'roundabout':case'rotary':
                const exit=step.maneuver.exit||1;
                icon='🔄';text='Au rond-point, prenez la '+exit+(exit===1?'ère':'ème')+' sortie'+(road?' vers '+road:'');
                break;
            case'end of road':
                if(mod.includes('left')){icon='⬅️';text='En fin de route, tournez à gauche'+(road?' sur '+road:'');}
                else{icon='➡️';text='En fin de route, tournez à droite'+(road?' sur '+road:'');}
                break;
            case'continue': icon='⬆️'; text='Continuez'+(road?' sur '+road:' tout droit'); break;
            default: icon='➡️'; text=(road?'Suivez '+road:'Continuez sur cette route');
        }
        const dist=step.distance;
        if(dist>0 && type!=='arrive') text+=' pendant '+(dist>=1000?(dist/1000).toFixed(1)+' km':Math.round(dist)+' m');
        return{icon,text};
    }

    // ── Voice ──
    function speak(text){
        if(!('speechSynthesis' in window))return;
        speechSynthesis.cancel();
        const u=new SpeechSynthesisUtterance(text);
        u.lang='fr-FR'; u.rate=0.95; u.pitch=1;
        const voices=speechSynthesis.getVoices();
        const fr=voices.find(v=>v.lang.startsWith('fr'));
        if(fr)u.voice=fr;
        speechSynthesis.speak(u);
    }

    // ── Select Stand ──
    window.selectStand=function(el){
        const lat=parseFloat(el.dataset.lat),lon=parseFloat(el.dataset.lon),name=el.dataset.name;
        standItems.forEach(e=>e.classList.remove('active'));
        el.classList.add('active');
        if(activeMarkerId!==null&&standMarkers[activeMarkerId])standMarkers[activeMarkerId].setIcon(standIcon);
        standsArr.forEach((s,i)=>{if(s.lat===lat&&s.lon===lon){standMarkers[i].setIcon(activeIcon);activeMarkerId=i;}});
        selectedTarget={lat,lon,name};
        document.getElementById('routeLoading').classList.add('visible');
        document.getElementById('routeInfo').classList.remove('visible');
        if(currentRoute){map.removeLayer(currentRoute);currentRoute=null;}

        fetch('https://router.project-osrm.org/route/v1/foot/'+userLon+','+userLat+';'+lon+','+lat+'?overview=full&geometries=geojson&steps=true')
        .then(r=>r.json()).then(data=>{
            document.getElementById('routeLoading').classList.remove('visible');
            if(data.code!=='Ok'||!data.routes.length){alert("Itinéraire impossible.");return;}
            const route=data.routes[0];
            const coords=route.geometry.coordinates.map(c=>[c[1],c[0]]);
            currentRoute=L.polyline(coords,{color:'#e11d48',weight:5,opacity:.8,dashArray:'10,6',lineCap:'round'}).addTo(map);
            map.fitBounds(currentRoute.getBounds().pad(0.15));
            routeSteps=route.legs[0].steps;
            currentStepIndex=0;

            const distKm=(route.distance/1000).toFixed(1);
            const durMin=Math.ceil(route.duration/60);
            document.getElementById('routeStandName').textContent='🧭 Vers '+name;
            document.getElementById('routeDist').textContent=distKm+' km';
            document.getElementById('routeTime').textContent=durMin>=60?Math.floor(durMin/60)+'h '+(durMin%60)+'min':durMin+' min';

            // Steps list
            const sl=document.getElementById('stepsList');
            sl.innerHTML='';
            routeSteps.forEach((st,i)=>{
                const t=translateInstruction(st);
                sl.innerHTML+='<div class="step-item" id="step-'+i+'"><span class="step-icon">'+t.icon+'</span><span>'+t.text+'</span></div>';
            });

            document.getElementById('routeInfo').classList.add('visible');
            document.getElementById('btnStartNav').style.display='flex';
            document.getElementById('btnStopNav').style.display='none';
        }).catch(()=>{
            document.getElementById('routeLoading').classList.remove('visible');
            alert("Erreur réseau.");
        });
    };

    window.selectStandByIdx=function(i){selectStand(standsArr[i].el);};

    // ── Start Navigation ──
    window.startNavigation=function(){
        if(!routeSteps.length||!selectedTarget)return;
        navigating=true;
        document.getElementById('btnStartNav').style.display='none';
        document.getElementById('btnStopNav').style.display='flex';
        document.getElementById('navPanel').classList.add('visible');
        currentStepIndex=0;
        announceStep(0);

        watchId=navigator.geolocation.watchPosition(pos=>{
            userLat=pos.coords.latitude;userLon=pos.coords.longitude;
            if(userMarker)userMarker.setLatLng([userLat,userLon]);
            map.setView([userLat,userLon],16);
            updateDists();
            checkStepProgress();
        },err=>{console.warn("GPS error:",err.message);},{enableHighAccuracy:true,maximumAge:2000,timeout:10000});

        speak("Navigation démarrée. "+translateInstruction(routeSteps[0]).text);
    };

    // ── Stop Navigation ──
    window.stopNavigation=function(){
        navigating=false;
        if(watchId!==null){navigator.geolocation.clearWatch(watchId);watchId=null;}
        document.getElementById('btnStartNav').style.display='flex';
        document.getElementById('btnStopNav').style.display='none';
        document.getElementById('navPanel').classList.remove('visible');
        speechSynthesis.cancel();
        speak("Navigation terminée.");
    };

    // ── Announce current step ──
    function announceStep(idx){
        if(idx>=routeSteps.length)return;
        const t=translateInstruction(routeSteps[idx]);
        document.getElementById('navIcon').textContent=t.icon;
        document.getElementById('navInstruction').textContent=t.text;
        document.getElementById('navStep').textContent=(idx+1)+'/'+routeSteps.length;

        // Next step preview
        if(idx+1<routeSteps.length){
            const n=translateInstruction(routeSteps[idx+1]);
            document.getElementById('navNext').textContent='Ensuite: '+n.text;
        }else{
            document.getElementById('navNext').textContent='';
        }

        // Remaining distance & time
        let remDist=0,remTime=0;
        for(let i=idx;i<routeSteps.length;i++){remDist+=routeSteps[i].distance;remTime+=routeSteps[i].duration;}
        document.getElementById('navDist').textContent=remDist>=1000?(remDist/1000).toFixed(1)+' km':Math.round(remDist)+' m';
        const rm=Math.ceil(remTime/60);
        document.getElementById('navTime').textContent=rm>=60?Math.floor(rm/60)+'h'+rm%60:rm+' min';

        // Highlight in list
        document.querySelectorAll('.step-item').forEach(e=>e.classList.remove('active'));
        const el=document.getElementById('step-'+idx);
        if(el){el.classList.add('active');el.scrollIntoView({behavior:'smooth',block:'nearest'});}
    }

    // ── Check progress toward next step ──
    function checkStepProgress(){
        if(!navigating||currentStepIndex>=routeSteps.length)return;
        const step=routeSteps[currentStepIndex];
        const loc=step.maneuver.location; // [lon,lat]
        const dist=haversine(userLat,userLon,loc[1],loc[0])*1000; // meters

        // If within 30m of next maneuver point, advance
        if(dist<30 && currentStepIndex<routeSteps.length-1){
            currentStepIndex++;
            const t=translateInstruction(routeSteps[currentStepIndex]);
            announceStep(currentStepIndex);
            speak(t.text);
        }

        // Check arrival
        const destDist=haversine(userLat,userLon,selectedTarget.lat,selectedTarget.lon)*1000;
        if(destDist<40){
            speak("Vous êtes arrivé à "+selectedTarget.name+". Bonne visite !");
            stopNavigation();
        }
    }

    // Load voices
    if('speechSynthesis' in window) speechSynthesis.onvoiceschanged=()=>speechSynthesis.getVoices();
})();
