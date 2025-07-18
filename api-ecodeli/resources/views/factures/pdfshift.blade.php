<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h2>Facture - {{ $mois }}/{{ $annee }}</h2>
    <p><strong>Prestataire :</strong> {{ $prestataire['first_name'] }} {{ $prestataire['last_name'] }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Prestation</th>
                <th>Montant (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($prestations as $p)
                <tr>
                    <td>{{ $p->date }}</td>
                    <td>{{ $p->details }}</td>
                    <td>{{ number_format($p->price, 2, ',', ' ') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2"><strong>Total</strong></td>
                <td><strong>{{ number_format($total, 2, ',', ' ') }} €</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
