/**
 * inscription_ai.js
 * À inclure dans inscription.php juste avant </body>
 * Ajoute : 1) Widget IA chatbot flottant
 *           2) Modal de simulation de paiement
 *
 * Prix synchronisés avec inscription.js : 10km=20, 21km=40, 42km=60 TND
 * Remises : 3-4 personnes → -10% | 5+ personnes → -20%
 */

(function () {

    
    var style = document.createElement('style');
    style.textContent = `
        :root{
            --bt-teal:#0f766e; --bt-teal-2:#14b8a6; --bt-ink:#102a43;
            --bt-muted:#627d98; --bt-line:#e2e8f0; --bt-bg:#ffffff;
            --bt-bg-soft:#f8fafb; --bt-bot:#f1f5f9;
        }
        html[data-theme="dark"]{
            --bt-ink:#e2e8f0; --bt-muted:#94a3b8; --bt-line:rgba(255,255,255,.08);
            --bt-bg:#0f172a; --bt-bg-soft:#1e293b; --bt-bot:#1e293b;
        }

        #ai-chat-bubble{
            position:fixed; bottom:28px; right:28px; z-index:9000;
            width:62px; height:62px; border-radius:50%;
            background:linear-gradient(135deg,var(--bt-teal) 0%,var(--bt-teal-2) 100%);
            border:none; color:#fff;
            box-shadow:0 10px 30px rgba(15,118,110,.45),0 4px 12px rgba(15,118,110,.25);
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:transform .25s cubic-bezier(.16,1,.3,1),box-shadow .25s ease;
            animation:btPulse 2.6s ease-in-out infinite;
        }
        #ai-chat-bubble svg{width:28px;height:28px;stroke:#fff;}
        #ai-chat-bubble:hover{transform:translateY(-3px) scale(1.06);box-shadow:0 14px 36px rgba(15,118,110,.55);}
        #ai-chat-bubble.active{animation:none;background:linear-gradient(135deg,#0b5a55,#0f766e);}
        #ai-chat-bubble.active svg.icon-chat{display:none;}
        #ai-chat-bubble.active svg.icon-close{display:block;}
        #ai-chat-bubble svg.icon-close{display:none;}
        #ai-chat-bubble .notif-dot{
            position:absolute; top:6px; right:6px; min-width:18px; height:18px; padding:0 5px;
            border-radius:999px; background:#ef4444; border:2px solid #fff;
            color:#fff; font-size:10px; font-weight:800; display:flex; align-items:center; justify-content:center;
        }
        @keyframes btPulse{
            0%,100%{box-shadow:0 10px 30px rgba(15,118,110,.45),0 0 0 0 rgba(15,118,110,.55);}
            50%{box-shadow:0 10px 30px rgba(15,118,110,.45),0 0 0 14px rgba(15,118,110,0);}
        }

        #ai-chat-panel{
            position:fixed; bottom:104px; right:28px; z-index:9000;
            width:380px; height:560px; max-height:calc(100vh - 130px);
            background:var(--bt-bg);
            border:1px solid rgba(16,42,67,.08); border-radius:22px; overflow:hidden;
            box-shadow:0 24px 60px rgba(16,42,67,.20),0 8px 20px rgba(16,42,67,.10);
            display:none; flex-direction:column;
            font-family:"Segoe UI",sans-serif;
            transform-origin:bottom right;
        }
        #ai-chat-panel.open{display:flex;animation:btPop .35s cubic-bezier(.16,1,.3,1);}
        @keyframes btPop{
            from{opacity:0;transform:translateY(20px) scale(.92);}
            to{opacity:1;transform:translateY(0) scale(1);}
        }
        @media(max-width:480px){
            #ai-chat-panel{right:12px;left:12px;bottom:92px;width:auto;height:75vh;}
            #ai-chat-bubble{right:18px;bottom:18px;}
        }

        .bt-head{
            background:linear-gradient(135deg,var(--bt-teal),var(--bt-teal-2));
            color:#fff; padding:16px 18px;
            display:flex; align-items:center; gap:12px;
            position:relative;
        }
        .bt-head::after{
            content:""; position:absolute; left:0; right:0; bottom:-1px; height:14px;
            background:linear-gradient(180deg,rgba(0,0,0,.06),transparent);
            pointer-events:none;
        }
        .bt-head .av{
            width:42px;height:42px;border-radius:50%;
            background:rgba(255,255,255,.18); backdrop-filter:blur(6px);
            display:flex;align-items:center;justify-content:center;
            font-size:22px; flex-shrink:0; border:1.5px solid rgba(255,255,255,.35);
        }
        .bt-head .inf{flex:1;min-width:0;}
        .bt-head .inf strong{color:#fff;font-size:.98rem;display:block;font-weight:800;letter-spacing:.2px;}
        .bt-head .inf span{color:rgba(255,255,255,.85);font-size:.74rem;display:inline-flex;align-items:center;gap:6px;}
        .bt-head .inf span::before{content:"";width:7px;height:7px;border-radius:50%;background:#86efac;box-shadow:0 0 8px #86efac;display:inline-block;}
        .bt-head .xbtn{
            margin-left:auto;background:rgba(255,255,255,.15);border:none;
            width:32px;height:32px;border-radius:50%;color:#fff;cursor:pointer;
            display:flex;align-items:center;justify-content:center;
            transition:background .2s,transform .2s;
        }
        .bt-head .xbtn:hover{background:rgba(255,255,255,.28);transform:rotate(90deg);}
        .bt-head .xbtn svg{width:16px;height:16px;}

        .bt-msgs{
            flex:1; overflow-y:auto; padding:18px 16px;
            display:flex; flex-direction:column; gap:10px;
            background:var(--bt-bg-soft);
            scrollbar-width:thin; scrollbar-color:#cbd5e1 transparent;
        }
        .bt-msgs::-webkit-scrollbar{width:6px;}
        .bt-msgs::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px;}
        html[data-theme="dark"] .bt-msgs::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);}
        .bt-m{
            max-width:82%; padding:10px 14px; border-radius:16px;
            font-size:.86rem; line-height:1.55; word-wrap:break-word;
            animation:btMsgIn .28s cubic-bezier(.16,1,.3,1);
        }
        @keyframes btMsgIn{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}
        .bt-m.bot{
            background:var(--bt-bot); color:var(--bt-ink);
            align-self:flex-start; border-bottom-left-radius:4px;
            box-shadow:0 1px 2px rgba(16,42,67,.04);
        }
        .bt-m.user{
            background:linear-gradient(135deg,var(--bt-teal),var(--bt-teal-2));
            color:#fff; align-self:flex-end; border-bottom-right-radius:4px;
            box-shadow:0 4px 12px rgba(15,118,110,.25);
        }
        .bt-dots{display:inline-flex;gap:4px;align-items:center;height:18px;}
        .bt-dots span{
            display:inline-block;width:7px;height:7px;border-radius:50%;
            background:var(--bt-teal);animation:btBlink 1.2s infinite;
        }
        .bt-dots span:nth-child(2){animation-delay:.18s;}
        .bt-dots span:nth-child(3){animation-delay:.36s;}
        @keyframes btBlink{0%,80%,100%{opacity:.25;transform:scale(.85);}40%{opacity:1;transform:scale(1);}}

        .bt-chips{
            padding:10px 14px; display:flex; flex-wrap:wrap; gap:6px;
            background:var(--bt-bg); border-top:1px solid var(--bt-line);
        }
        .bt-chip{
            background:var(--bt-bg-soft);
            border:1px solid var(--bt-line); border-radius:999px;
            padding:6px 12px; font-size:.78rem; color:var(--bt-ink);
            cursor:pointer; font-weight:600;
            transition:all .2s ease;
        }
        .bt-chip:hover{
            border-color:var(--bt-teal); color:var(--bt-teal);
            background:rgba(15,118,110,.06); transform:translateY(-1px);
        }

        .bt-inp{
            padding:12px 14px; border-top:1px solid var(--bt-line);
            display:flex; gap:8px; background:var(--bt-bg);
            align-items:center;
        }
        .bt-inp input{
            flex:1; background:var(--bt-bg-soft); border:1.5px solid var(--bt-line);
            border-radius:999px; padding:10px 16px;
            color:var(--bt-ink); font-size:.88rem; outline:none;
            transition:border-color .2s, box-shadow .2s, background .2s;
        }
        .bt-inp input::placeholder{color:var(--bt-muted);}
        .bt-inp input:focus{
            border-color:var(--bt-teal); background:var(--bt-bg);
            box-shadow:0 0 0 3px rgba(15,118,110,.12);
        }
        .bt-inp button{
            background:linear-gradient(135deg,var(--bt-teal),var(--bt-teal-2));
            border:none; border-radius:50%;
            width:40px; height:40px; cursor:pointer; color:#fff;
            display:flex; align-items:center; justify-content:center;
            transition:transform .2s, box-shadow .2s;
            box-shadow:0 4px 12px rgba(15,118,110,.30);
            flex-shrink:0;
        }
        .bt-inp button svg{width:16px;height:16px;}
        .bt-inp button:hover{transform:translateY(-2px) scale(1.05);box-shadow:0 6px 16px rgba(15,118,110,.40);}
        .bt-inp button:active{transform:scale(.95);}

        /* Modal paiement */
        #bt-pay-ov{
            position:fixed; inset:0; z-index:9500; background:rgba(16,42,67,.55);
            backdrop-filter:blur(4px);
            display:none; align-items:center; justify-content:center; padding:20px;
        }
        #bt-pay-ov.open{display:flex;animation:btFade .25s ease;}
        @keyframes btFade{from{opacity:0;}to{opacity:1;}}
        #bt-pay-box{
            background:var(--bt-bg);
            border-radius:22px; width:440px; max-width:100%;
            padding:28px 28px 24px;
            font-family:"Segoe UI",sans-serif; color:var(--bt-ink);
            box-shadow:0 24px 60px rgba(16,42,67,.30);
            animation:btPop .35s cubic-bezier(.16,1,.3,1);
            border:1px solid rgba(16,42,67,.06);
        }
        #bt-pay-box h2{margin:0 0 4px;font-size:1.3rem;color:var(--bt-ink);font-weight:800;}
        #bt-pay-box .sub{color:var(--bt-muted);font-size:.86rem;margin:0 0 22px;}
        .bt-amtbox{
            background:linear-gradient(135deg,rgba(15,118,110,.08),rgba(20,184,166,.08));
            border:1px solid rgba(15,118,110,.15);
            border-radius:14px; padding:16px 20px; margin-bottom:20px;
            display:flex; justify-content:space-between; align-items:center;
        }
        .bt-amtbox .lbl{color:var(--bt-muted);font-size:.84rem;font-weight:600;}
        .bt-amtbox .amt{color:var(--bt-teal);font-size:1.55rem;font-weight:800;}
        .bt-tabs{display:flex;gap:8px;margin-bottom:20px;}
        .bt-tab{
            flex:1; padding:11px 8px;
            border:1.5px solid var(--bt-line); border-radius:12px;
            background:var(--bt-bg-soft); color:var(--bt-ink);
            cursor:pointer; font-size:.84rem; font-weight:600;
            text-align:center; transition:all .2s ease;
        }
        .bt-tab:hover{border-color:var(--bt-teal);color:var(--bt-teal);transform:translateY(-1px);}
        .bt-tab.active{
            border-color:var(--bt-teal);
            background:linear-gradient(135deg,rgba(15,118,110,.10),rgba(20,184,166,.10));
            color:var(--bt-teal); font-weight:700;
        }
        .bt-flds{display:flex;flex-direction:column;gap:12px;}
        .bt-fl label{display:block;font-size:.82rem;color:var(--bt-ink);margin-bottom:6px;font-weight:600;}
        .bt-fl input{
            width:100%; box-sizing:border-box;
            background:var(--bt-bg-soft); border:1.5px solid var(--bt-line);
            border-radius:10px; padding:11px 14px;
            color:var(--bt-ink); font-size:.88rem; outline:none;
            transition:border-color .2s,box-shadow .2s,background .2s;
        }
        .bt-fl input:focus{
            border-color:var(--bt-teal); background:var(--bt-bg);
            box-shadow:0 0 0 3px rgba(15,118,110,.12);
        }
        .bt-fl-row{display:flex;gap:10px;}
        .bt-fl-row .bt-fl{flex:1;}
        .bt-infobox{
            background:var(--bt-bg-soft); border:1px solid var(--bt-line);
            border-radius:12px; padding:16px; font-size:.86rem;
            color:var(--bt-muted); line-height:1.7;
        }
        .bt-infobox strong{color:var(--bt-ink);}
        .bt-infobox p{margin:0 0 6px;}
        .bt-acts{margin-top:22px;display:flex;gap:10px;}
        .bt-ok{
            flex:1; padding:13px; border-radius:12px; border:none;
            background:linear-gradient(135deg,var(--bt-teal),var(--bt-teal-2));
            color:#fff; font-size:.95rem; font-weight:700; cursor:pointer;
            transition:transform .2s, box-shadow .2s, opacity .15s;
            box-shadow:0 6px 18px rgba(15,118,110,.30);
        }
        .bt-ok:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,118,110,.40);}
        .bt-ok:disabled{opacity:.5;cursor:not-allowed;transform:none;}
        .bt-no{
            padding:13px 20px; border-radius:12px;
            border:1.5px solid var(--bt-line); background:var(--bt-bg);
            color:var(--bt-ink); cursor:pointer; font-size:.9rem; font-weight:600;
            transition:all .2s;
        }
        .bt-no:hover{border-color:var(--bt-muted);background:var(--bt-bg-soft);}
        .bt-res{text-align:center;padding:14px 0;}
        .bt-res .ic{font-size:3.2rem;margin-bottom:12px;}
        .bt-res h3{margin:0 0 8px;font-size:1.1rem;font-weight:800;}
        .bt-res p{color:var(--bt-muted);font-size:.86rem;margin:0 0 6px;}
        .bt-res .txn{font-family:monospace;color:var(--bt-teal);font-size:.82rem;font-weight:600;}
        .bt-spin{display:flex;align-items:center;justify-content:center;gap:14px;padding:24px;color:var(--bt-muted);font-size:.92rem;}
        .bt-ring{
            width:26px;height:26px;border:3px solid var(--bt-line);
            border-top-color:var(--bt-teal); border-radius:50%;
            animation:btSpin .8s linear infinite;
        }
        @keyframes btSpin{to{transform:rotate(360deg);}}

        @media (prefers-reduced-motion: reduce){
            #ai-chat-bubble,#ai-chat-panel,.bt-m,#bt-pay-ov,#bt-pay-box,
            .bt-chip,.bt-tab,.bt-ok,.bt-inp button,.bt-head .xbtn{animation:none!important;transition:none!important;}
        }
    `;
    document.head.appendChild(style);


    /* ═══════════════════════════════════════════
       2. CHAT HTML
    ═══════════════════════════════════════════ */
    document.body.insertAdjacentHTML('beforeend', `
        <button id="ai-chat-bubble" type="button" aria-label="Ouvrir l'assistant IA" title="Assistant IA BarchaThon">
            <svg class="icon-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
            <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            <span class="notif-dot">1</span>
        </button>
        <div id="ai-chat-panel" role="dialog" aria-label="Assistant IA BarchaThon">
            <div class="bt-head">
                <div class="av">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;color:#fff;" aria-hidden="true">
                        <circle cx="12" cy="5" r="2"/><path d="M12 7v4"/><path d="M9 11h6l1 6-3 1-1-4-1 4-3-1z"/>
                    </svg>
                </div>
                <div class="inf">
                    <strong>Assistant BarchaThon</strong>
                    <span>IA · En ligne</span>
                </div>
                <button class="xbtn" id="bt-xbtn" type="button" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="bt-msgs" id="bt-msgs">
                <div class="bt-m bot">
                    👋 Bonjour ! Je suis l'assistant du <strong>Marathon Carthage BarchaThon</strong>.<br><br>
                    Posez-moi vos questions sur les inscriptions, tarifs, paiements ou dossards !
                </div>
            </div>
            <div class="bt-chips" id="bt-chips">
                <span class="bt-chip">💰 Tarifs des circuits</span>
                <span class="bt-chip">📋 Modes de paiement</span>
                <span class="bt-chip">🎽 Obtenir mon dossard ?</span>
                <span class="bt-chip">🔄 Politique d'annulation</span>
            </div>
            <div class="bt-inp">
                <input id="bt-q" type="text" placeholder="Posez votre question…" autocomplete="off">
                <button id="bt-go" type="button" aria-label="Envoyer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </div>
        </div>
    `);

    var panel = document.getElementById('ai-chat-panel');
    var bubble = document.getElementById('ai-chat-bubble');
    var msgs = document.getElementById('bt-msgs');
    var inp = document.getElementById('bt-q');

    function setOpen(open){
        panel.classList.toggle('open', open);
        bubble.classList.toggle('active', open);
        bubble.setAttribute('aria-label', open ? "Fermer l'assistant IA" : "Ouvrir l'assistant IA");
        var dot = bubble.querySelector('.notif-dot');
        if (dot) dot.style.display = 'none';
        if (open) setTimeout(function(){ try { inp.focus(); } catch(e){} }, 250);
    }
    bubble.addEventListener('click', function () { setOpen(!panel.classList.contains('open')); });
    document.getElementById('bt-xbtn').addEventListener('click', function () { setOpen(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.classList.contains('open')) setOpen(false);
    });
    document.addEventListener('click', function (e) {
        if (!panel.classList.contains('open')) return;
        if (panel.contains(e.target) || bubble.contains(e.target)) return;
        setOpen(false);
    });

    document.querySelectorAll('.bt-chip').forEach(function (c) {
        c.addEventListener('click', function () { doSend(c.textContent.replace(/^\S+\s/, '')); });
    });
    inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') doSend(inp.value); });
    document.getElementById('bt-go').addEventListener('click', function () { doSend(inp.value); });

    function addMsg(html, role) {
        var d = document.createElement('div');
        d.className = 'bt-m ' + role;
        d.innerHTML = html;
        msgs.appendChild(d);
        msgs.scrollTop = msgs.scrollHeight;
        return d;
    }

    function doSend(text) {
        text = text.trim();
        if (!text) return;
        inp.value = '';
        document.getElementById('bt-chips').style.display = 'none';
        addMsg(text, 'user');
        var t = addMsg('<span class="bt-dots"><span>●</span><span>●</span><span>●</span></span>', 'bot');
        fetch('../../Controller/ai_agent_chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            t.remove();
            if (d.reply) {
                addMsg(d.reply, 'bot');
            } else if (d.error) {
                addMsg('⚠️ ' + d.error, 'bot');
            } else {
                addMsg('⚠️ Réponse inattendue du serveur.', 'bot');
            }
        })
        .catch(function () { t.remove(); addMsg('⚠️ Connexion impossible.', 'bot'); });
    }


    
    var TARIFS = { "1": 20, "2": 40, "3": 60 };

    function calcMontant(nb, parcours) {
        var pu = TARIFS[String(parcours)];
        if (!pu || nb <= 0) return 0;
        var total = pu * nb;
        if (nb >= 5)      total *= 0.8;
        else if (nb >= 3) total *= 0.9;
        return parseFloat(total.toFixed(2));
    }


    
    document.body.insertAdjacentHTML('beforeend', `
        <div id="bt-pay-ov">
            <div id="bt-pay-box">
                <h2>💳 Simulation de Paiement</h2>
                <p class="sub">Environnement de test — aucun débit réel</p>
                <div class="bt-amtbox">
                    <span class="lbl">Montant total</span>
                    <span class="amt" id="bt-amt">— TND</span>
                </div>
                <div class="bt-tabs" id="bt-tabs">
                    <div class="bt-tab active" data-mode="card">💳 Carte</div>
                    <div class="bt-tab" data-mode="transfer">🏦 Virement</div>
                    <div class="bt-tab" data-mode="cash">💵 Espèces</div>
                </div>
                <div id="bt-fc"></div>
                <div class="bt-acts" id="bt-acts">
                    <button class="bt-ok" id="bt-okbtn">Confirmer le paiement</button>
                    <button class="bt-no" id="bt-nobtn">Annuler</button>
                </div>
            </div>
        </div>
    `);

    var ov      = document.getElementById('bt-pay-ov');
    var fc      = document.getElementById('bt-fc');
    var acts    = document.getElementById('bt-acts');
    var okBtn   = document.getElementById('bt-okbtn');
    var noBtn   = document.getElementById('bt-nobtn');
    var curId   = null, curMode = 'card', curAmt = 0;

    function renderForm(mode) {
        if (mode === 'card') {
            fc.innerHTML = `
                <div class="bt-flds">
                    <div class="bt-fl"><label>Numéro de carte</label>
                        <input id="bt-cn" type="text" placeholder="4242 4242 4242 4242" maxlength="19">
                    </div>
                    <div class="bt-fl"><label>Nom sur la carte</label>
                        <input id="bt-cname" type="text" placeholder="PRÉNOM NOM">
                    </div>
                    <div class="bt-fl-row">
                        <div class="bt-fl"><label>Expiration</label>
                            <input id="bt-cexp" type="text" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div class="bt-fl"><label>CVV</label>
                            <input id="bt-ccvv" type="text" placeholder="123" maxlength="3">
                        </div>
                    </div>
                </div>`;
            
            document.getElementById('bt-cn').addEventListener('input', function (e) {
                e.target.value = e.target.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim().slice(0,19);
            });
            document.getElementById('bt-cexp').addEventListener('input', function (e) {
                var v = e.target.value.replace(/\D/g,'');
                if (v.length >= 2) v = v.slice(0,2) + '/' + v.slice(2,4);
                e.target.value = v;
            });
        } else if (mode === 'transfer') {
            fc.innerHTML = `
                <div class="bt-infobox">
                    <p>Effectuez un virement vers :</p>
                    <p>🏦 <strong>Banque BarchaThon</strong></p>
                    <p>RIB : <strong>TN59 0800 0000 0012 3456 7890</strong></p>
                    <p>Référence : <strong>MARATHON-INS-${curId}</strong></p>
                    <p style="color:#e94560;margin-top:8px;">⏱ Confirmation sous 24h ouvrables</p>
                </div>`;
        } else {
            fc.innerHTML = `
                <div class="bt-infobox">
                    <p>💵 Présentez-vous au bureau d'inscription avec :</p>
                    <p>✅ Numéro d'inscription : <strong>#${curId}</strong></p>
                    <p>✅ Montant exact : <strong>${curAmt} TND</strong></p>
                    <p style="color:#e94560;margin-top:8px;">📍 Stade El Menzah — Bureau 3</p>
                </div>`;
        }
    }

    document.getElementById('bt-tabs').addEventListener('click', function (e) {
        var tab = e.target.closest('.bt-tab');
        if (!tab) return;
        document.querySelectorAll('.bt-tab').forEach(function(t){t.classList.remove('active');});
        tab.classList.add('active');
        curMode = tab.dataset.mode;
        renderForm(curMode);
    });

    noBtn.addEventListener('click', function () { ov.classList.remove('open'); });
    ov.addEventListener('click', function (e) { if (e.target === ov) ov.classList.remove('open'); });


    
    window.openPayModal = function (idInscription, nbPersonnes, idParcours) {
    curId  = idInscription;
    curMode = 'card';

    // Lire le prix depuis l'option du select (comme inscription.js le fait)
    var circuit = document.getElementById('circuit');
    var amt = 0;

    if (circuit) {
        // Chercher l'option qui correspond à cet id_parcours
        var opt = circuit.querySelector('option[value="' + idParcours + '"]');
        if (opt) {
            var distMatch = opt.textContent.match(/([\d.]+)\s*km/);
            if (distMatch) {
                var dist = parseFloat(distMatch[1]);
                var pu = dist < 15 ? 20 : dist < 25 ? 40 : 60;
                amt = pu * nbPersonnes;
                if (nbPersonnes >= 5)     amt *= 0.8;
                else if (nbPersonnes >= 3) amt *= 0.9;
                amt = parseFloat(amt.toFixed(2));
            }
        }
    }

    // Fallback si select pas trouvé
    if (!amt) amt = calcMontant(nbPersonnes, idParcours);

    curAmt = amt;
    document.getElementById('bt-amt').textContent = curAmt + ' TND';
    document.querySelectorAll('.bt-tab').forEach(function(t){ t.classList.remove('active'); });
    document.querySelector('.bt-tab[data-mode="card"]').classList.add('active');
    okBtn.disabled = false;
    acts.style.display = 'flex';
    renderForm('card');
    ov.classList.add('open');
};


    
    okBtn.addEventListener('click', function () {
        okBtn.disabled = true;
        acts.style.display = 'none';
        fc.innerHTML = `<div class="bt-spin"><div class="bt-ring"></div>Traitement en cours…</div>`;

        fetch('../../Controller/simulate_payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_inscription: curId, mode: curMode, montant: curAmt })
        })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (data.success) {
                fc.innerHTML = `
                    <div class="bt-res">
                        <div class="ic">✅</div>
                        <h3 style="color:#22c55e;">Paiement confirmé !</h3>
                        <p>${data.message}</p>
                        <p class="txn">${data.transaction ? data.transaction.transaction_id : ''}</p>
                    </div>`;

                
                var row = document.querySelector('tr[data-id="' + curId + '"]');
                if (row) {
                    row.dataset.statut = 'paid';
                    if (row.cells[5]) row.cells[5].innerHTML = '<span style="color:green;font-weight:bold;">Payé</span>';
                    var btn = row.querySelector('.btn-pay-trigger');
                    if (btn) {
                        var ok = document.createElement('span');
                        ok.style.cssText = 'color:green;font-weight:bold;';
                        ok.textContent = 'OK';
                        btn.replaceWith(ok);
                    }
                }
                setTimeout(function(){ ov.classList.remove('open'); }, 3200);

            } else {
                fc.innerHTML = `
                    <div class="bt-res">
                        <div class="ic">❌</div>
                        <h3 style="color:#e94560;">Paiement refusé</h3>
                        <p>${data.error || 'Une erreur est survenue.'}</p>
                    </div>`;
                acts.style.display = 'flex';
                okBtn.disabled = false;
            }
        })
        .catch(function() {
            fc.innerHTML = `
                <div class="bt-res">
                    <div class="ic">⚠️</div>
                    <h3>Erreur réseau</h3>
                    <p>Impossible de contacter le serveur de paiement.</p>
                </div>`;
            acts.style.display = 'flex';
            okBtn.disabled = false;
        });
    });

})();