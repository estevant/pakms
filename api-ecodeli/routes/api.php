<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/check-auth', function (Request $request) {
    return response()->json([
        'message' => 'Utilisateur connecté ✅',
        'user' => $request->user()
    ]);
});

// Contrôleurs
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\PrestationController;
use App\Http\Controllers\PrestationDisponibiliteController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\AdminFinanceController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\JustificatifController;
use App\Http\Controllers\InvoiceController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/get_session', [AuthController::class, 'getSession']);

Route::middleware(['web','auth.session'])->get('/user/me', function (Request $request) {
    return response()->json($request->user());
});

Route::post('annonce/estimate', [AnnonceController::class, 'estimate']);

// Routes protégées avec vérification de bannissement
Route::middleware(['check.banned'])->group(function () {
    Route::get('/prestataire/prestations', [PrestationController::class, 'index']);
    Route::post('/prestataire/prestations', [PrestationController::class, 'store']);
    Route::put('/prestataire/prestations/{id}', [PrestationController::class, 'update']);
    Route::patch('/prestataire/prestations/{id}', [PrestationController::class, 'update']);
    Route::delete('/prestataire/prestations/{id}', [PrestationController::class, 'destroy']);
    Route::get('/prestataire/historique-prestations', [PrestationController::class, 'historique']);
    Route::get('/prestataire/prestations-totaux', [PrestationController::class, 'prestationsTotaux']);

    Route::get('/prestataire/prestations/{id}/disponibilites', [PrestationDisponibiliteController::class, 'index']);
    Route::post('/prestataire/prestations/{id}/disponibilites', [PrestationDisponibiliteController::class, 'store']);
    Route::delete('/prestataire/disponibilites/{id}', [PrestationDisponibiliteController::class, 'destroy']);

    Route::get('/prestataire/evaluations', [EvaluationController::class, 'getForPrestataire']);

    Route::get('/prestataire/factures', [FactureController::class, 'index']);

    Route::get('/client/mes-reservations', [ClientController::class, 'mesReservations']);
    Route::delete('/client/annuler-reservation/{availability_id}', [ClientController::class, 'annulerReservation']);
    Route::get('/client/prestations-disponibles', [ClientController::class, 'prestationsDisponibles']);
    Route::post('/client/reserver', [ClientController::class, 'reserver']);
    Route::get('/client/anciennes-reservations', [ClientController::class, 'reservationsPassees']);
    Route::post('/client/evaluation', [ClientController::class, 'storeReview']);
    Route::get('/client/mes-factures', [ClientController::class, 'mesFactures']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']);

    Route::get('/prestataire/mes-types-prestations', [PrestationController::class, 'getPrestataireServiceTypes']);

    Route::post('/prestataire/proposer-type', [ServiceTypeController::class, 'proposerNouveauType']);

    Route::get('/prestataire/mes-demandes-types', [ServiceTypeController::class, 'mesDemandesTypes']);

    Route::get('/prestataire/mes-justificatifs', [ServiceTypeController::class, 'mesJustificatifs']);

    Route::post('/prestataire/demande-prestation', [PrestationController::class, 'demandePrestation']);

    Route::post('/justificatifs', [JustificatifController::class, 'upload']);
});

Route::post('/stripe/custom-webhook', [StripeWebhookController::class, 'handle']);
Route::post('/stripe/create-session', [StripeController::class, 'createSession']);
Route::post('/stripe/create-session-service', [StripeController::class, 'createSessionService']);

Route::get('/admin/validations', [AdminController::class, 'listPending']);
Route::patch('/admin/validations/{id}', [AdminController::class, 'validateUser']);
Route::delete('/admin/validations/{id}', [AdminController::class, 'rejectUser']);
Route::get('/wallet', [WalletController::class, 'show']);

Route::middleware(['auth.session', 'check.banned'])->group(function () {
Route::get('/wallet', [WalletController::class, 'show']);
Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
Route::get('/wallet/received', [WalletController::class, 'received']);
Route::get('/wallet/withdrawals', [WalletController::class, 'listWithdrawals']);


Route::get('/admin/ca-total', [AdminFinanceController::class, 'caTotal']);
Route::get('/admin/retraits', [AdminFinanceController::class, 'listeRetraits']);
Route::get('/admin/encaissements', [AdminFinanceController::class, 'listeEncaissements']);

});

Route::get('/prestataires', [AdminController::class, 'getPrestataires']);
Route::patch('/admin/prestataires/{id}/valider', [AdminController::class, 'validerPrestataire']);
Route::patch('/admin/prestataires/{id}/refuser', [AdminController::class, 'refuserPrestataire']);
Route::delete('/admin/prestataires/{id}', [AdminController::class, 'supprimerPrestataire']);

Route::get('/servicetypes', [ServiceTypeController::class, 'index']);
Route::post('/servicetypes', [ServiceTypeController::class, 'store']);
Route::put('/servicetypes/{id}', [ServiceTypeController::class, 'update']);
Route::patch('/servicetypes/{id}', [ServiceTypeController::class, 'update']);
Route::delete('/servicetypes/{id}', [ServiceTypeController::class, 'destroy']);

Route::get('/tarifs-ecodeli', [PricingController::class, 'getTarifs']);

// Routes admin pour les propositions de types de prestations
Route::get('/admin/propositions-types', [AdminController::class, 'getPropositionsTypes']);
Route::patch('/admin/propositions-types/{id}/valider', [AdminController::class, 'validerPropositionType']);
Route::patch('/admin/propositions-types/{id}/refuser', [AdminController::class, 'refuserPropositionType']);

Route::post('/justificatifs', [JustificatifController::class, 'upload']);

// Ajout manuel d'une prestation à un prestataire (admin)
Route::post('/admin/prestataires/{id}/ajouter-prestation', [App\Http\Controllers\AdminController::class, 'ajouterPrestationPourPrestataire']);
Route::get('/admin/prestataires/{id}', [AdminController::class, 'showPrestataire']);

