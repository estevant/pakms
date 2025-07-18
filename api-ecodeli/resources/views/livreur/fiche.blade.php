<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Fiche livreur</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 40px;
    }

    .card {
        border: 1px solid #ccc;
        padding: 20px;
        max-width: 500px;
    }

    .list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .list li {
        margin-bottom: 12px;
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
    }

    .btn {
        display: inline-block;
        padding: 6px 12px;
        background: #2d6a4f;
        color: #fff;
        text-decoration: none;
    }

    img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
    }
    </style>
</head>

<body>
    <div class="card">
        <h2>Livreur</h2>
        <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;"> @if($livreur->profile_picture)
            <img src="{{ asset('storage/'.$livreur->profile_picture) }}" alt="Photo"> @else <img
                src="https://via.placeholder.com/80?text={{ strtoupper(substr($livreur->first_name,0,1).substr($livreur->last_name,0,1)) }}"
                alt=""> @endif <div>
                <strong>{{ $livreur->first_name }} {{ $livreur->last_name }}</strong><br> ID :
                {{ $livreur->user_id }}<br> Téléphone : {{ $livreur->phone ?? '—' }}
            </div>
        </div>
        <h3>Livraisons en cours</h3>
        <ul class="list"> @forelse($livraisons as $l) <li> Commande #{{ $l->request_id }}<br> Client :
                {{ $l->client_name }}<br> Statut : {{ $l->status }}<br> @if($l->status === 'en attente de validation')
                <form method="POST" action="{{ route('livraison.valider', $l->request_id) }}"> @csrf <button
                        class="btn">Valider la remise</button>
                </form> @endif </li> @empty <li>Aucune livraison active.</li> @endforelse </ul>
    </div>
</body>

</html>