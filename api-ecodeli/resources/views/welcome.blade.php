<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture - {{ $month }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; text-align: right; }
    </style>
</head>
<body>

    <h1>Facture du mois de {{ $month }}</h1>

    <p><strong>Prestataire :</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
    <p><strong>Date d’émission :</strong> {{ now()->format('d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Prestation</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Adresse</th>
                <th>Montant (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($prestaDetails as $presta)
                <tr>
                    <td>{{ $presta->service_type_name }}</td>
                    <td>{{ $presta->date }}</td>
                    <td>{{ $presta->start_time }} - {{ $presta->end_time }}</td>
                    <td>{{ $presta->address }}</td>
                    <td>{{ number_format($presta->price, 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">Total à verser : {{ number_format($total, 2, ',', ' ') }} €</p>

</body>
</html>
