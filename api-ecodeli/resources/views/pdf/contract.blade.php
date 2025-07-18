<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Contrat #{{ $contract->contract_id }}</title>
  <style>
    /* un peu de styling inline pour le PDF */
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { text-align: center; }
    .terms { margin-top: 1em; }
  </style>
</head>
<body>
  <h1>Contrat #{{ $contract->contract_id }}</h1>
  <p><strong>Début :</strong> {{ $contract->start_date }}</p>
  <p><strong>Fin  :</strong> {{ $contract->end_date }}</p>
  <div class="terms">
    <strong>Conditions : </strong><br>
    {!! nl2br(e($contract->terms)) !!}
  </div>
</body>
</html>
