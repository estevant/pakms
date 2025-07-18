<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>
<div class="max-w-md mx-auto mt-10 bg-white rounded shadow p-6 text-center">
    <h2 class="text-2xl font-bold mb-4">Mon QR code livreur</h2>
    <div id="qr-code" class="flex justify-center mb-4"></div>
    <p id="qr-code-value" class="text-xs text-gray-500"></p>
    <button onclick="window.location.href='dashboard.php'" class="mt-4 bg-gray-600 text-white px-4 py-2 rounded">Retour au tableau de bord</button>
    <div id="error-msg" class="text-red-600 text-center text-sm font-medium mt-6"></div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const API_BASE = "<?= BASE_API ?>";
async function loadQrCode() {
    try {
        const res = await fetch(`${API_BASE}/qr/generate`, {
            method: 'GET',
            credentials: 'include'
        });
        const data = await res.json();
        if (!data.success) {
            document.getElementById('error-msg').innerText = 'Erreur lors de la génération du QR code';
            return;
        }
        new QRCode(document.getElementById('qr-code'), {
            text: data.qr_code,
            width: 220,
            height: 220
        });
        document.getElementById('qr-code-value').innerText = data.qr_code;
    } catch (e) {
        document.getElementById('error-msg').innerText = 'Erreur lors de la génération du QR code';
    }
}
loadQrCode();
</script>
<?php include_once('../includes/footer.php'); ?> 