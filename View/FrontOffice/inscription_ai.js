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
        #ai-chat-bubble {
            position:fixed; bottom:28px; right:28px; z-index:9000;
            width:56px; height:56px; border-radius:50%;
            background:linear-gradient(135deg,#1a1a2e 0%,#16213e 60%,#0f3460 100%);
            border:2px solid #e94560; box-shadow:0 4px 20px rgba(233,69,96,.45);
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:transform .2s,box-shadow .2s; font-size:24px;
        }
        #ai-chat-bubble:hover{transform:scale(1.12);box-shadow:0 6px 28px rgba(233,69,96,.65);}
        #ai-chat-bubble .notif-dot{
            position:absolute; top:2px; right:2px; width:12px; height:12px;
            border-radius:50%; background:#e94560; border:2px solid #fff;
        }
        #ai-chat-panel{
            position:fixed; bottom:96px; right:28px; z-index:9000;
            width:360px; max-height:520px; background:#0d1117;
            border:1px solid #30363d; border-radius:16px; overflow:hidden;
            box-shadow:0 16px 48px rgba(0,0,0,.6);
            display:none; flex-direction:column; font-family:'Segoe UI',sans-serif;
        }
        #ai-chat-panel.open{display:flex;animation:btUp .25s ease;}
        @keyframes btUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        .bt-head{
            background:linear-gradient(90deg,#1a1a2e,#0f3460);
            border-bottom:1px solid #e9456033;
            padding:14px 16px; display:flex; align-items:center; gap:10px;
        }
        .bt-head .av{
            width:36px;height:36px;border-radius:50%;background:#e94560;
            display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;
        }
        .bt-head .inf strong{color:#e2e8f0;font-size:.9rem;display:block;}
        .bt-head .inf span{color:#64748b;font-size:.75rem;}
        .bt-head .xbtn{margin-left:auto;background:none;border:none;color:#64748b;font-size:20px;cursor:pointer;line-height:1;}
        .bt-head .xbtn:hover{color:#e2e8f0;}
        .bt-msgs{
            flex:1;overflow-y:auto;padding:14px;
            display:flex;flex-direction:column;gap:10px;
            scrollbar-width:thin;scrollbar-color:#30363d transparent;
        }
        .bt-m{max-width:82%;padding:10px 14px;border-radius:12px;font-size:.84rem;line-height:1.5;}
        .bt-m.bot{background:#161b22;color:#c9d1d9;border:1px solid #30363d;align-self:flex-start;border-bottom-left-radius:4px;}
        .bt-m.user{background:#e94560;color:#fff;align-self:flex-end;border-bottom-right-radius:4px;}
        .bt-dots span{display:inline-block;animation:btBlink 1.2s infinite;}
        .bt-dots span:nth-child(2){animation-delay:.2s;}
        .bt-dots span:nth-child(3){animation-delay:.4s;}
        @keyframes btBlink{0%,80%,100%{opacity:.2}40%{opacity:1}}
        .bt-chips{padding:0 14px 12px;display:flex;flex-wrap:wrap;gap:6px;}
        .bt-chip{
            background:#161b22;border:1px solid #30363d;border-radius:20px;
            padding:5px 12px;font-size:.76rem;color:#8b949e;cursor:pointer;
            transition:border-color .15s,color .15s;
        }
        .bt-chip:hover{border-color:#e94560;color:#e94560;}
        .bt-inp{padding:12px;border-top:1px solid #21262d;display:flex;gap:8px;background:#161b22;}
        .bt-inp input{
            flex:1;background:#0d1117;border:1px solid #30363d;
            border-radius:8px;padding:9px 12px;color:#e2e8f0;font-size:.84rem;outline:none;
        }
        .bt-inp input:focus{border-color:#e94560;}
        .bt-inp button{
            background:#e94560;border:none;border-radius:8px;
            width:38px;height:38px;cursor:pointer;color:#fff;
            font-size:16px;display:flex;align-items:center;justify-content:center;
            transition:background .15s;
        }
        .bt-inp button:hover{background:#c73652;}

        /* Modal paiement */
        #bt-pay-ov{
            position:fixed;inset:0;z-index:9500;background:rgba(0,0,0,.75);
            display:none;align-items:center;justify-content:center;
        }
        #bt-pay-ov.open{display:flex;}
        #bt-pay-box{
            background:#0d1117;border:1px solid #30363d;border-radius:16px;
            width:420px;max-width:95vw;padding:28px;
            font-family:'Segoe UI',sans-serif;color:#e2e8f0;animation:btUp .2s ease;
        }
        #bt-pay-box h2{margin:0 0 4px;font-size:1.2rem;color:#fff;}
        #bt-pay-box .sub{color:#64748b;font-size:.84rem;margin:0 0 22px;}
        .bt-amtbox{
            background:#161b22;border:1px solid #30363d;border-radius:10px;
            padding:14px 18px;margin-bottom:20px;
            display:flex;justify-content:space-between;align-items:center;
        }
        .bt-amtbox .lbl{color:#8b949e;font-size:.82rem;}
        .bt-amtbox .amt{color:#e94560;font-size:1.4rem;font-weight:700;}
        .bt-tabs{display:flex;gap:8px;margin-bottom:20px;}
        .bt-tab{
            flex:1;padding:10px;border:1px solid #30363d;border-radius:8px;
            background:#161b22;color:#8b949e;cursor:pointer;font-size:.82rem;
            text-align:center;transition:all .15s;
        }
        .bt-tab:hover{border-color:#e94560;color:#e2e8f0;}
        .bt-tab.active{border-color:#e94560;background:#e9456015;color:#e94560;font-weight:600;}
        .bt-flds{display:flex;flex-direction:column;gap:12px;}
        .bt-fl label{display:block;font-size:.78rem;color:#8b949e;margin-bottom:5px;}
        .bt-fl input{
            width:100%;box-sizing:border-box;background:#161b22;
            border:1px solid #30363d;border-radius:8px;
            padding:10px 12px;color:#e2e8f0;font-size:.84rem;outline:none;
        }
        .bt-fl input:focus{border-color:#e94560;}
        .bt-fl-row{display:flex;gap:10px;}
        .bt-fl-row .bt-fl{flex:1;}
        .bt-infobox{
            background:#161b22;border:1px solid #30363d;border-radius:8px;
            padding:14px;font-size:.82rem;color:#8b949e;line-height:1.6;
        }
        .bt-infobox strong{color:#e2e8f0;}
        .bt-acts{margin-top:22px;display:flex;gap:10px;}
        .bt-ok{
            flex:1;padding:13px;border-radius:10px;border:none;
            background:linear-gradient(135deg,#e94560,#c73652);
            color:#fff;font-size:.95rem;font-weight:600;cursor:pointer;transition:opacity .15s;
        }
        .bt-ok:hover{opacity:.88;} .bt-ok:disabled{opacity:.5;cursor:not-allowed;}
        .bt-no{
            padding:13px 20px;border-radius:10px;border:1px solid #30363d;
            background:transparent;color:#8b949e;cursor:pointer;font-size:.9rem;transition:all .15s;
        }
        .bt-no:hover{border-color:#e2e8f0;color:#e2e8f0;}
        .bt-res{text-align:center;padding:10px 0;}
        .bt-res .ic{font-size:3rem;margin-bottom:10px;}
        .bt-res h3{margin:0 0 8px;} .bt-res p{color:#8b949e;font-size:.84rem;}
        .bt-res .txn{font-family:monospace;color:#e94560;font-size:.82rem;}
        .bt-spin{display:flex;align-items:center;justify-content:center;gap:12px;padding:20px;color:#8b949e;font-size:.9rem;}
        .bt-ring{width:24px;height:24px;border:3px solid #30363d;border-top-color:#e94560;border-radius:50%;animation:btSpin .8s linear infinite;}
        @keyframes btSpin{to{transform:rotate(360deg)}}
    `;
    document.head.appendChild(style);


    /* ═══════════════════════════════════════════
       2. CHAT HTML
    ═══════════════════════════════════════════ */
    document.body.insertAdjacentHTML('beforeend', `
        <div id="ai-chat-bubble" title="Assistant IA BarchaThon">
            🤖<div class="notif-dot"></div>
        </div>
        <div id="ai-chat-panel">
            <div class="bt-head">
                <div class="av">🏃</div>
                <div class="inf">
                    <strong>Assistant BarchaThon</strong>
                    <span>IA · En ligne</span>
                </div>
                <button class="xbtn" id="bt-xbtn">✕</button>
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
                <span class="bt-chip">🎽 Comment obtenir mon dossard ?</span>
                <span class="bt-chip">🔄 Politique d'annulation</span>
            </div>
            <div class="bt-inp">
                <input id="bt-q" type="text" placeholder="Votre question..." autocomplete="off">
                <button id="bt-go">➤</button>
            </div>
        </div>
    `);

    var panel = document.getElementById('ai-chat-panel');
    var bubble = document.getElementById('ai-chat-bubble');
    var msgs = document.getElementById('bt-msgs');
    var inp = document.getElementById('bt-q');

    bubble.addEventListener('click', function () {
        panel.classList.toggle('open');
        bubble.querySelector('.notif-dot').style.display = 'none';
    });
    document.getElementById('bt-xbtn').addEventListener('click', function () { panel.classList.remove('open'); });

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
        curId   = idInscription;
        curAmt  = calcMontant(nbPersonnes, idParcours);
        curMode = 'card';

        document.getElementById('bt-amt').textContent = curAmt + ' TND';
        document.querySelectorAll('.bt-tab').forEach(function(t){t.classList.remove('active');});
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