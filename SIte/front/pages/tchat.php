<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');

$id_annonce = intval($_GET['id'] ?? 0);
if (!$id_annonce) {
    echo "<p class='text-red-600 text-center mt-12'>ID manquant.</p>";
    include_once('../includes/footer.php');
    exit;
}
?> <div class="max-w-4xl mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
        <a href="mes_livraisons.php" class="text-sm text-blue-600 hover:underline">Retour</a>
        <h2 class="text-xl font-bold flex-1 text-center">Tchat livraison</h2>
        <span class="w-20"></span>
    </div>
    <div id="participants" class="text-center text-sm text-gray-500 mb-4"></div>
    <div id="chat-box" class="h-[400px] overflow-y-auto border border-gray-300 rounded p-4 bg-white mb-4 space-y-3">
    </div>
    <div class="flex gap-2 mb-2">
        <button id="btn-handoff" class="hidden bg-yellow-600 text-white px-3 py-2 rounded">Proposer dépôt</button>
        <button id="btn-offer" class="hidden bg-green-600 text-white px-3 py-2 rounded">Faire une offre</button>
    </div>
    <form id="form-message" class="flex gap-2">
        <input type="text" id="input-message" class="flex-1 border border-gray-300 rounded px-3 py-2"
            placeholder="Votre message…" required>
        <button type="submit" class="bg-[#1976D2] text-white px-4 rounded hover:bg-blue-700">Envoyer</button>
    </form>
</div>
<div id="modal-handoff" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg w-full max-w-lg p-6 space-y-4">
        <h3 class="text-lg font-semibold text-center mb-2">Proposer un dépôt intermédiaire</h3>
        <select id="handoff_box_select" class="input-field">
            <option value="">Choisir une box…</option>
        </select>
        <div class="flex justify-end gap-3 pt-4">
            <button id="btn-cancel" class="px-4 py-2 rounded bg-gray-200">Annuler</button>
            <button id="btn-confirm" class="px-4 py-2 rounded bg-green-600 text-white">Envoyer</button>
        </div>
    </div>
</div>
<div id="modal-offer" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-center mb-2">Faire une offre</h3>
        <label class="block"> Montant (€) : <input type="number" id="offer_amount" class="input-field" min="1" required>
        </label>
        <div class="flex justify-end gap-3 pt-4">
            <button id="offer-cancel" class="px-4 py-2 rounded bg-gray-200">Annuler</button>
            <button id="offer-confirm" class="px-4 py-2 rounded bg-green-600 text-white">Envoyer</button>
        </div>
    </div>
</div>
<style>
.input-field {
    @apply border border-gray-300 rounded px-3 py-2 w-full;
}
</style>
<script>
const id_annonce = <?= $id_annonce ?>;
const BASE_API = "<?= BASE_API ?>";
const chatBox = document.getElementById('chat-box');
const btnHandoff = document.getElementById('btn-handoff');
const modal = document.getElementById('modal-handoff');
let roles = [];
let userId = null;
let hasPending = false;
let isAssignedDeliverer = false;

(async () => {
    try {
        const sessionRes = await fetch(`${BASE_API}/get_session`, {
            credentials: 'include'
        });
        const sessionData = await sessionRes.json().catch(() => ({}));
        roles = sessionData?.user?.roles ?? [];
        userId = sessionData?.user?.user_id ?? null;
        console.log('User roles:', roles, 'User ID:', userId);

        const participantsRes = await fetch(`${BASE_API}/tchat/participants?id_annonce=${id_annonce}`, {
            credentials: 'include'
        });
        const participantsData = await participantsRes.json().catch(() => ({}));
        
        if (participantsData?.success) {
            document.getElementById('participants').innerText = `${participantsData.client} - ${participantsData.livreur}`;
            isAssignedDeliverer = participantsData.is_assigned_deliverer || false;
            console.log('Is assigned deliverer:', isAssignedDeliverer);
            
            if (isAssignedDeliverer) {
                console.log('Showing handoff button for assigned deliverer');
                btnHandoff.classList.remove('hidden');
            } else {
                console.log('Showing offer button for non-assigned user');
                document.getElementById('btn-offer').classList.remove('hidden');
            }
        } else {
            console.log('Participants data failed, showing offer button by default');
            document.getElementById('btn-offer').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error initializing chat interface:', error);
        document.getElementById('btn-offer').classList.remove('hidden');
    }
})();

function renderSystem(m) {
    const el = document.createElement('div');
    el.className = 'text-center text-gray-500 text-sm';
    if (m.handoff_id && (m.meta.from || m.meta.to)) {
        el.innerHTML =
            `${m.contenu}<br><span class="text-xs">De : ${m.meta.from || '-'} - À : ${m.meta.to || '-'}</span>`;
    } else {
        el.textContent = m.contenu;
    }
    if (m.handoff_status === 'En attente') {
        hasPending = true;
        if (roles.includes('Customer') || roles.includes('Seller')) {
            ['accept', 'refuse'].forEach(act => {
                const b = document.createElement('button');
                b.textContent = act === 'accept' ? 'Accepter' : 'Refuser';
                b.className = `ml-2 underline ${act==='accept'?'text-green-600':'text-red-600'}`;
                b.onclick = () => actionHandoff(m.handoff_id, act);
                el.appendChild(b);
            });
        } else if (roles.includes('Deliverer') && m.meta.proposer_id === userId) {
            const c = document.createElement('button');
            c.textContent = 'Annuler';
            c.className = 'ml-2 underline text-yellow-600';
            c.onclick = () => actionHandoff(m.handoff_id, 'cancel');
            el.appendChild(c);
        }
    }
    if (m.meta?.negotiation_id) {
        const price = (typeof m.meta?.proposed_price === 'number') ? (m.meta.proposed_price / 100).toFixed(2) : '-';
        if (m.negotiation_status === 'pending') {
            el.textContent = `Offre proposée : ${price} €`;
            const isReceiver = m.meta.receiver_id === userId;
            if (isReceiver) {
                const a = document.createElement('button');
                a.textContent = 'Accepter';
                a.className = 'ml-2 underline text-green-600';
                a.onclick = () => actionNegotiation(m.meta.negotiation_id, 'accept');
                const r = document.createElement('button');
                r.textContent = 'Refuser';
                r.className = 'ml-2 underline text-red-600';
                r.onclick = () => actionNegotiation(m.meta.negotiation_id, 'reject');
                el.appendChild(a);
                el.appendChild(r);
            } else if (m.meta.sender_id === userId) {
                const c = document.createElement('button');
                c.textContent = 'Annuler';
                c.className = 'ml-2 underline text-yellow-600';
                c.onclick = () => actionNegotiation(m.meta.negotiation_id, 'cancel');
                el.appendChild(c);
            }
        } else if (m.negotiation_status === 'accepted') {
            el.innerHTML = `Offre acceptée : ${price} € <span class="text-xs text-gray-500">(acceptée)</span>`;
            if (
                !m.meta.paid &&
                userId === m.meta.sender_id &&
                (roles.includes('Customer') || roles.includes('Seller'))
            ) {
                const p = document.createElement('button');
                p.textContent = 'Payer';
                p.className = 'ml-2 underline text-blue-600 font-medium';
                p.onclick = createStripeSession;
                el.appendChild(p);
            }
        } else {
            el.innerHTML = `Offre : ${price} € <span class="text-xs text-gray-500">(${m.negotiation_status})</span>`;
        }
    }
    chatBox.appendChild(el);
}

function renderMessages(list) {
    chatBox.innerHTML = '';
    hasPending = false;
    list.forEach(m => {
        if (m.system) renderSystem(m);
        else {
            const div = document.createElement('div');
            div.className =
                `mb-2 p-3 rounded-lg max-w-[75%] ${m.est_moi?'bg-blue-100 text-right ml-auto':'bg-gray-100'}`;
            div.innerHTML =
                `<p class="text-sm font-medium mb-1">${m.nom}<span class="text-xs text-gray-500 ml-1">${m.date_envoi}</span></p><p>${m.contenu}</p>`;
            chatBox.appendChild(div);
        }
    });
    chatBox.scrollTop = chatBox.scrollHeight;
    btnHandoff.disabled = hasPending;
}
async function loadMessages() {
    const r = await fetch(`${BASE_API}/tchat/messages?id_annonce=${id_annonce}`, {
        credentials: 'include'
    });
    const d = await r.json().catch(() => ({}));
    if (d?.messages) renderMessages(d.messages);
}
setInterval(loadMessages, 3000);
loadMessages();
document.getElementById('form-message').addEventListener('submit', async e => {
    e.preventDefault();
    const val = document.getElementById('input-message').value.trim();
    if (!val) return;
    const r = await fetch(`${BASE_API}/tchat/envoyer`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id_annonce,
            contenu: val
        })
    });
    const j = await r.json().catch(() => ({}));
    if (j?.success) {
        document.getElementById('input-message').value = '';
        loadMessages();
    }
});
async function actionHandoff(id, act) {
    const r = await fetch(`${BASE_API}/handoff/${id}/${act}`, {
        method: 'POST',
        credentials: 'include'
    });
    const j = await r.json().catch(() => ({}));
    if (j?.success) loadMessages();
    else alert(j?.message || 'Erreur');
}
async function actionNegotiation(id, act) {
    const r = await fetch(`${BASE_API}/negotiation/${id}/${act}`, {
        method: 'POST',
        credentials: 'include'
    });
    const j = await r.json().catch(() => ({}));
    if (j?.success) {
        loadMessages();
    } else alert(j?.message || 'Erreur');
}
async function createStripeSession() {
    const r = await fetch(`${BASE_API}/stripe/create-session`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            request_id: id_annonce
        })
    });
    const j = await r.json().catch(() => ({}));
    if (j?.url) {
        window.location.href = j.url;
    } else alert(j?.error || 'Impossible de lancer le paiement');
}
btnHandoff.onclick = async () => {
    if (hasPending) return;
    if (!isAssignedDeliverer) {
        alert('Vous devez être le livreur assigné à cette livraison pour proposer un dépôt.');
        return;
    }
    const r = await fetch(`${BASE_API}/boxes`, {
        credentials: 'include'
    });
    const d = await r.json().catch(() => ({}));
    const sel = document.getElementById('handoff_box_select');
    sel.innerHTML = '<option value="">Choisir une box…</option>';
    if (d?.success) {
        d.boxes.forEach(b => {
            const o = document.createElement('option');
            o.value = b.box_id ?? b.id;
            o.textContent = `${b.label} (${b.location_city})`;
            sel.appendChild(o);
        });
    }
    modal.classList.remove('hidden');
};
document.getElementById('btn-confirm').onclick = async () => {
    const rawId = document.getElementById('handoff_box_select').value;
    const boxId = parseInt(rawId, 10);
    if (!boxId) return alert('Veuillez choisir une box');
    const r = await fetch(`${BASE_API}/handoff`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            assignment_id: id_annonce,
            type: 'BOX',
            box_id: boxId
        })
    });
    if (r.status === 409) return alert('Une proposition est déjà en attente');
    const j = await r.json().catch(() => ({}));
    if (j?.success) {
        modal.classList.add('hidden');
        loadMessages();
    } else alert(j?.message || 'Erreur');
};
document.getElementById('btn-cancel').onclick = () => modal.classList.add('hidden');
document.getElementById('btn-offer').onclick = () => {
    if (isAssignedDeliverer) {
        alert('Vous ne pouvez pas faire une offre sur votre propre livraison.');
        return;
    }
    document.getElementById('modal-offer').classList.remove('hidden');
};
document.getElementById('offer-cancel').onclick = () => document.getElementById('modal-offer').classList.add('hidden');
document.getElementById('offer-confirm').onclick = async () => {
    const amount = parseFloat(document.getElementById('offer_amount').value);
    if (isNaN(amount) || amount <= 0) return alert('Montant invalide');
    const r = await fetch(`${BASE_API}/negotiation/propose`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            request_id: id_annonce,
            proposed_price: Math.round(amount * 100)
        })
    });
    const j = await r.json().catch(() => ({}));
    if (j?.success) {
        document.getElementById('modal-offer').classList.add('hidden');
        loadMessages();
    } else alert(j?.message || 'Erreur');
};
</script> <?php include_once('../includes/footer.php'); ?>