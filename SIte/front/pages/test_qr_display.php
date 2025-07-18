<?php
include_once('../includes/header.php');
?>
<div class="max-w-md mx-auto mt-10 bg-white rounded shadow p-6 text-center">
    <h2 class="text-2xl font-bold mb-4">QR Code de Test</h2>
    <div id="qr-code" class="flex justify-center mb-4"></div>
    <p class="text-xs text-gray-500 mb-4">QR Code du livreur : ECODELI_919a6610d0c80a4d</p>
    <p class="text-sm text-gray-600 mb-4">Utilisez ce QR code pour tester le scanner depuis l'application Android</p>
    <button onclick="window.location.href='dashboard.php'" class="bg-gray-600 text-white px-4 py-2 rounded">Retour au tableau de bord</button>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new QRCode(document.getElementById('qr-code'), {
        text: 'ECODELI_919a6610d0c80a4d',
        width: 220,
        height: 220
    });
});
</script>
<?php include_once('../includes/footer.php'); ?> 