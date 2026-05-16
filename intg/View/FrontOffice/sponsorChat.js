(function () {
    if (window.__sponsorChatLoaded) return;
    window.__sponsorChatLoaded = true;

    var endpoint = (typeof window.SPONSOR_CHAT_ENDPOINT === 'string' && window.SPONSOR_CHAT_ENDPOINT) || 'chatSponsor_process.php';

    var style = document.createElement('style');
    style.textContent = `
        :root{
            --sc-teal:#0f766e; --sc-teal-2:#14b8a6; --sc-ink:#102a43;
            --sc-muted:#627d98; --sc-line:#e2e8f0; --sc-bg:#ffffff;
            --sc-bg-soft:#f8fafb; --sc-bot:#f1f5f9;
        }
        html[data-theme="dark"]{
            --sc-ink:#e2e8f0; --sc-muted:#94a3b8; --sc-line:rgba(255,255,255,.08);
            --sc-bg:#0f172a; --sc-bg-soft:#1e293b; --sc-bot:#1e293b;
        }

        #sc-bubble{
            position:fixed; bottom:28px; right:28px; z-index:9000;
            width:62px; height:62px; border-radius:50%;
            background:linear-gradient(135deg,var(--sc-teal) 0%,var(--sc-teal-2) 100%);
            border:none; color:#fff;
            box-shadow:0 10px 30px rgba(15,118,110,.45),0 4px 12px rgba(15,118,110,.25);
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:transform .25s cubic-bezier(.16,1,.3,1),box-shadow .25s ease;
            animation:scPulse 2.6s ease-in-out infinite;
        }
        #sc-bubble svg{width:28px;height:28px;stroke:#fff;}
        #sc-bubble:hover{transform:translateY(-3px) scale(1.06);box-shadow:0 14px 36px rgba(15,118,110,.55);}
        #sc-bubble.active{animation:none;background:linear-gradient(135deg,#0b5a55,#0f766e);}
        #sc-bubble.active svg.sc-i-chat{display:none;}
        #sc-bubble.active svg.sc-i-x{display:block;}
        #sc-bubble svg.sc-i-x{display:none;}
        #sc-bubble .sc-dot{
            position:absolute; top:6px; right:6px; min-width:18px; height:18px; padding:0 5px;
            border-radius:999px; background:#ef4444; border:2px solid #fff;
            color:#fff; font-size:10px; font-weight:800;
            display:flex; align-items:center; justify-content:center;
        }
        @keyframes scPulse{
            0%,100%{box-shadow:0 10px 30px rgba(15,118,110,.45),0 0 0 0 rgba(15,118,110,.55);}
            50%{box-shadow:0 10px 30px rgba(15,118,110,.45),0 0 0 14px rgba(15,118,110,0);}
        }

        #sc-panel{
            position:fixed; bottom:104px; right:28px; z-index:9000;
            width:380px; height:560px; max-height:calc(100vh - 130px);
            background:var(--sc-bg);
            border:1px solid rgba(16,42,67,.08); border-radius:22px; overflow:hidden;
            box-shadow:0 24px 60px rgba(16,42,67,.20),0 8px 20px rgba(16,42,67,.10);
            display:none; flex-direction:column;
            font-family:"Segoe UI",sans-serif;
            transform-origin:bottom right;
        }
        #sc-panel.open{display:flex;animation:scPop .35s cubic-bezier(.16,1,.3,1);}
        @keyframes scPop{
            from{opacity:0;transform:translateY(20px) scale(.92);}
            to{opacity:1;transform:translateY(0) scale(1);}
        }
        @media(max-width:480px){
            #sc-panel{right:12px;left:12px;bottom:92px;width:auto;height:75vh;}
            #sc-bubble{right:18px;bottom:18px;}
        }

        .sc-head{
            background:linear-gradient(135deg,var(--sc-teal),var(--sc-teal-2));
            color:#fff; padding:16px 18px;
            display:flex; align-items:center; gap:12px; position:relative;
        }
        .sc-head::after{
            content:""; position:absolute; left:0; right:0; bottom:-1px; height:14px;
            background:linear-gradient(180deg,rgba(0,0,0,.06),transparent);
            pointer-events:none;
        }
        .sc-head .sc-av{
            width:42px;height:42px;border-radius:50%;
            background:rgba(255,255,255,.18); backdrop-filter:blur(6px);
            display:flex;align-items:center;justify-content:center;
            flex-shrink:0; border:1.5px solid rgba(255,255,255,.35);
            color:#fff;
        }
        .sc-head .sc-av svg{width:22px;height:22px;}
        .sc-head .sc-inf{flex:1;min-width:0;}
        .sc-head .sc-inf strong{color:#fff;font-size:.98rem;display:block;font-weight:800;letter-spacing:.2px;}
        .sc-head .sc-inf span{color:rgba(255,255,255,.85);font-size:.74rem;display:inline-flex;align-items:center;gap:6px;}
        .sc-head .sc-inf span::before{
            content:"";width:7px;height:7px;border-radius:50%;
            background:#86efac;box-shadow:0 0 8px #86efac;display:inline-block;
        }
        .sc-head .sc-x{
            margin-left:auto;background:rgba(255,255,255,.15);border:none;
            width:32px;height:32px;border-radius:50%;color:#fff;cursor:pointer;
            display:flex;align-items:center;justify-content:center;
            transition:background .2s,transform .2s;
        }
        .sc-head .sc-x:hover{background:rgba(255,255,255,.28);transform:rotate(90deg);}
        .sc-head .sc-x svg{width:16px;height:16px;}

        .sc-msgs{
            flex:1; overflow-y:auto; padding:18px 16px;
            display:flex; flex-direction:column; gap:10px;
            background:var(--sc-bg-soft);
            scrollbar-width:thin; scrollbar-color:#cbd5e1 transparent;
        }
        .sc-msgs::-webkit-scrollbar{width:6px;}
        .sc-msgs::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px;}
        html[data-theme="dark"] .sc-msgs::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);}
        .sc-m{
            max-width:82%; padding:10px 14px; border-radius:16px;
            font-size:.86rem; line-height:1.55; word-wrap:break-word;
            animation:scMsgIn .28s cubic-bezier(.16,1,.3,1);
        }
        @keyframes scMsgIn{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}
        .sc-m.bot{
            background:var(--sc-bot); color:var(--sc-ink);
            align-self:flex-start; border-bottom-left-radius:4px;
            box-shadow:0 1px 2px rgba(16,42,67,.04);
        }
        .sc-m.user{
            background:linear-gradient(135deg,var(--sc-teal),var(--sc-teal-2));
            color:#fff; align-self:flex-end; border-bottom-right-radius:4px;
            box-shadow:0 4px 12px rgba(15,118,110,.25);
        }
        .sc-dots{display:inline-flex;gap:4px;align-items:center;height:18px;}
        .sc-dots span{
            display:inline-block;width:7px;height:7px;border-radius:50%;
            background:var(--sc-teal);animation:scBlink 1.2s infinite;
        }
        .sc-dots span:nth-child(2){animation-delay:.18s;}
        .sc-dots span:nth-child(3){animation-delay:.36s;}
        @keyframes scBlink{0%,80%,100%{opacity:.25;transform:scale(.85);}40%{opacity:1;transform:scale(1);}}

        .sc-inp{
            padding:12px 14px; border-top:1px solid var(--sc-line);
            display:flex; gap:8px; background:var(--sc-bg);
            align-items:center;
        }
        .sc-inp input{
            flex:1; background:var(--sc-bg-soft); border:1.5px solid var(--sc-line);
            border-radius:999px; padding:10px 16px;
            color:var(--sc-ink); font-size:.88rem; outline:none;
            transition:border-color .2s, box-shadow .2s, background .2s;
        }
        .sc-inp input::placeholder{color:var(--sc-muted);}
        .sc-inp input:focus{
            border-color:var(--sc-teal); background:var(--sc-bg);
            box-shadow:0 0 0 3px rgba(15,118,110,.12);
        }
        .sc-inp button{
            background:linear-gradient(135deg,var(--sc-teal),var(--sc-teal-2));
            border:none; border-radius:50%;
            width:40px; height:40px; cursor:pointer; color:#fff;
            display:flex; align-items:center; justify-content:center;
            transition:transform .2s, box-shadow .2s;
            box-shadow:0 4px 12px rgba(15,118,110,.30);
            flex-shrink:0;
        }
        .sc-inp button svg{width:16px;height:16px;}
        .sc-inp button:hover{transform:translateY(-2px) scale(1.05);box-shadow:0 6px 16px rgba(15,118,110,.40);}
        .sc-inp button:active{transform:scale(.95);}

        @media (prefers-reduced-motion: reduce){
            #sc-bubble,#sc-panel,.sc-m,.sc-inp button,.sc-head .sc-x{animation:none!important;transition:none!important;}
        }
    `;
    document.head.appendChild(style);

    document.body.insertAdjacentHTML('beforeend', `
        <button id="sc-bubble" type="button" aria-label="Ouvrir l'assistant Sponsor" title="Assistant Sponsor">
            <svg class="sc-i-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
            <svg class="sc-i-x" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            <span class="sc-dot">1</span>
        </button>
        <div id="sc-panel" role="dialog" aria-label="Assistant Sponsor">
            <div class="sc-head">
                <div class="sc-av">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div class="sc-inf">
                    <strong>Assistant Sponsor</strong>
                    <span>IA · En ligne</span>
                </div>
                <button class="sc-x" id="sc-x" type="button" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="sc-msgs" id="sc-msgs">
                <div class="sc-m bot">
                    👋 Bonjour ! Je suis l'assistant <strong>Sponsor BarchaThon</strong>.<br><br>
                    Posez-moi vos questions sur les sponsors, contrats, montants, ou états.
                </div>
            </div>
            <div class="sc-inp">
                <input id="sc-q" type="text" placeholder="Posez votre question…" autocomplete="off">
                <button id="sc-go" type="button" aria-label="Envoyer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </div>
        </div>
    `);

    var panel  = document.getElementById('sc-panel');
    var bubble = document.getElementById('sc-bubble');
    var msgs   = document.getElementById('sc-msgs');
    var inp    = document.getElementById('sc-q');
    var goBtn  = document.getElementById('sc-go');
    var xBtn   = document.getElementById('sc-x');

    function setOpen(open){
        panel.classList.toggle('open', open);
        bubble.classList.toggle('active', open);
        bubble.setAttribute('aria-label', open ? "Fermer l'assistant Sponsor" : "Ouvrir l'assistant Sponsor");
        var dot = bubble.querySelector('.sc-dot');
        if (dot) dot.style.display = 'none';
        if (open) setTimeout(function(){ try { inp.focus(); } catch(e){} }, 250);
    }
    bubble.addEventListener('click', function () { setOpen(!panel.classList.contains('open')); });
    xBtn.addEventListener('click', function () { setOpen(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.classList.contains('open')) setOpen(false);
    });
    document.addEventListener('click', function (e) {
        if (!panel.classList.contains('open')) return;
        if (panel.contains(e.target) || bubble.contains(e.target)) return;
        setOpen(false);
    });

    function addMsg(text, role){
        var d = document.createElement('div');
        d.className = 'sc-m ' + role;
        d.textContent = text;
        msgs.appendChild(d);
        msgs.scrollTop = msgs.scrollHeight;
        return d;
    }

    function send(){
        var text = (inp.value || '').trim();
        if (!text) return;
        inp.value = '';
        addMsg(text, 'user');
        var thinking = document.createElement('div');
        thinking.className = 'sc-m bot';
        thinking.innerHTML = '<span class="sc-dots"><span></span><span></span><span></span></span>';
        msgs.appendChild(thinking);
        msgs.scrollTop = msgs.scrollHeight;

        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(text)
        })
        .then(function (r) { return r.text(); })
        .then(function (data) {
            thinking.textContent = data;
        })
        .catch(function (err) {
            thinking.textContent = '⚠️ ' + (err && err.message ? err.message : 'Erreur réseau');
        });
    }

    goBtn.addEventListener('click', send);
    inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') send(); });

    window.sendChat = send;
})();
