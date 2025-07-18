<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\{
    AuthController,
    AnnonceController,
    LivreurController,
    LivreurRouteController,
    MessageController,
    SuiviController,
    NotificationController,
    AdminController,
    JustificatifController,
    PrestataireController,
    BoxController,
    HandoffController,
    ContractController,
    ReviewController,
    ReportController,
    ProfileController,
    StripeController,
    StripeWebhookController,
    InvoiceController,
    NegotiationController,
    AdminReviewController,
    ClientController,
    QrCodeController,
    RoleChangeController,
    WalletController,
};

Route::prefix('api')->middleware('web')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login'])
                         ->name('login');
    Route::post('logout',   [AuthController::class, 'logout']);
    Route::get('get_session',[AuthController::class, 'getSession']);
});



Route::prefix('api')
    ->middleware(['web', 'auth:sanctum'])
    ->group(function () {
        Route::get('should_show_tutorial', [ClientController::class, 'shouldShowTutorial']);
        Route::post('mark_tutorial_done', [ClientController::class, 'markTutorialDone']);
    });

Route::prefix('api')
    ->middleware(['web', 'auth.session'])
    ->group(function () {
        Route::post('report', [ReportController::class, 'store']);
    });

Route::prefix('api/annonce')
    ->middleware(['web', 'auth.session'])
    ->group(function () {
        Route::get('mes',        [AnnonceController::class, 'mes']);
        Route::post('submit',    [AnnonceController::class, 'submit']);
        Route::post('delete',    [AnnonceController::class, 'delete']);
        Route::get('annonce',    [AnnonceController::class, 'show']);
        Route::post('update',    [AnnonceController::class, 'update']);
        Route::get('suivi',      [AnnonceController::class, 'getTracking']);
        Route::get('terminees',  [AnnonceController::class, 'terminees']);
        Route::delete('photos/{photoId}', [AnnonceController::class, 'deletePhoto']);
    });

Route::get('api/annonce/villes', [AnnonceController::class, 'villes']);

Route::prefix('api/livreur')
    ->middleware('web')
    ->group(function () {
        Route::get('disponibles',     [LivreurController::class, 'disponibles']);
        Route::post('attribuer',      [LivreurController::class, 'attribuer']);
        Route::get('mes',             [LivreurController::class, 'mes']);
        Route::get('livraison',       [LivreurController::class, 'show']);
        Route::post('livree',         [LivreurController::class, 'livree']);
        Route::post('desattribuer',   [LivreurController::class, 'desattribuer']);
        Route::get('terminees',       [LivreurController::class, 'terminees']);
        Route::post('encours',        [LivreurController::class, 'enCours']);
        Route::post('annuler',        [LivreurController::class, 'annuler']);

        Route::post('suivi/add',      [LivreurController::class, 'addTracking']);
        Route::get('suivi/list',      [LivreurController::class, 'listTracking']);

        Route::get('boxes',           [BoxController::class, 'index']);
        Route::post('boxes/deposit',  [BoxController::class, 'deposit']);
        Route::post('boxes/retrieve', [BoxController::class, 'retrieve']);

        Route::get('justificatifs',                [JustificatifController::class, 'liste']);
        Route::post('justificatifs',               [JustificatifController::class, 'upload']);
        Route::get('justificatifs/{id}/download', [JustificatifController::class, 'telecharger']);
        Route::delete('justificatifs/{id}',        [JustificatifController::class, 'supprimer']);
    });

Route::prefix('api/livreur/routes')
    ->middleware('web')
    ->group(function () {
        Route::get('/',          [LivreurRouteController::class, 'index']);
        Route::post('/',         [LivreurRouteController::class, 'store']);
        Route::put('{id}',       [LivreurRouteController::class, 'update']);
        Route::delete('{id}',    [LivreurRouteController::class, 'destroy']);
    });

Route::prefix('api/tchat')
    ->middleware('web')
    ->group(function () {
        Route::get('messages',      [MessageController::class, 'index']);
        Route::post('envoyer',      [MessageController::class, 'store']);
        Route::get('participants',  [MessageController::class, 'participants']);
    });

Route::prefix('api/notifications')
    ->middleware('web')
    ->group(function () {
        Route::get('/',           [NotificationController::class, 'index']);
        Route::patch('{id}/read', [NotificationController::class, 'markAsRead']);
    });

Route::prefix('api')
    ->middleware(['web', 'auth.session', 'role:Customer,Seller,Deliverer'])
    ->group(function () {
        Route::get('boxes',      [BoxController::class, 'listAll']);
        Route::get('boxes/near', [BoxController::class, 'near']);
    });

Route::prefix('api/admin')
    ->middleware(['web', 'auth.session', 'role:Admin'])
    ->group(function () {
        Route::get('stats', [AdminController::class, 'stats']);

        // Clients
        Route::get('clients',         [AdminController::class, 'clients']);
        Route::get('clients/{id}',    [AdminController::class, 'getClient']);
        Route::put('clients/{id}',    [AdminController::class, 'updateClient']);

        // Livreurs
        Route::get('livreurs',            [AdminController::class, 'listeLivreurs']);
        Route::get('livreurs/{id}',       [AdminController::class, 'getLivreur']);
        Route::put('livreurs/{id}',       [AdminController::class, 'updateLivreur']);
        Route::patch('livreurs/valider',  [AdminController::class, 'validerLivreur']);
        Route::patch('livreurs/invalider', [AdminController::class, 'invaliderLivreur']);
        Route::delete('livreurs/{id}',    [AdminController::class, 'supprimerLivreur']);

        // Prestataires
        Route::get('prestataires/{id}/edit', [AdminController::class, 'getPrestataire']);
        Route::get('prestataires/{id}', [AdminController::class, 'showPrestataire']);
        Route::get('prestataires', [AdminController::class, 'listePrestataires']);
        Route::put('prestataires/{id}',    [AdminController::class, 'updatePrestataire']);
        Route::delete('prestataires/{id}', [AdminController::class, 'deletePrestataire']);

        // Commerçants
        Route::get('commercants',         [AdminController::class, 'listeCommercants']);
        Route::get('commercants/{id}',    [AdminController::class, 'getCommercant']);
        Route::put('commercants/{id}',    [AdminController::class, 'updateCommercant']);
        Route::delete('commercants/{id}', [AdminController::class, 'deleteCommercant']);
        
        //Commerçants 
        Route::get('commercants',         [AdminController::class,'listeCommercants']);
        Route::get('commercants/{id}',    [AdminController::class,'getCommercant']);
        Route::put('commercants/{id}',    [AdminController::class,'updateCommercant']);
        Route::delete('commercants/{id}', [AdminController::class,'deleteCommercant']);

        // Annonces
        Route::get('annonces',          [AdminController::class, 'listeAnnonces']);
        Route::get('annonces/{id}',     [AdminController::class, 'getAnnonce']);
        Route::put('annonces/{id}',     [AdminController::class, 'updateAnnonce']);
        Route::delete('annonces/{id}',  [AdminController::class, 'deleteAnnonce']);
        Route::get('annonces/villes',   [AdminController::class, 'villes']);

        // Justificatifs
        Route::get('justificatifs',         [AdminController::class, 'tousLesJustificatifs']);
        Route::get('justificatifs/{id}',    [AdminController::class, 'getJustificatifsUtilisateur']);
        Route::patch('justificatifs/statut', [AdminController::class, 'changerStatutJustificatif']);

        // Alertes d'administration
        Route::get('alerts', [AdminController::class, 'getAdminAlerts']);

        // Contrats
        Route::get('contracts/pending',        [AdminController::class, 'pendingContracts']);
        Route::get('contracts',                [AdminController::class, 'allContracts']);
        Route::post('contracts/{id}/approve',  [AdminController::class, 'approveContract']);
        Route::post('contracts/{id}/reject',   [AdminController::class, 'rejectContract']);

        // Signalements
        Route::get('reports',        [ReportController::class, 'index']);
        Route::patch('reports/{id}', [ReportController::class, 'updateStatus']);

        Route::get('contracts/pending',        [AdminController::class, 'pendingContracts']);
        Route::post('contracts/{id}/approve',  [AdminController::class, 'approveContract']);
        Route::post('contracts/{id}/reject',   [AdminController::class, 'rejectContract']);

        Route::get('boxes', [BoxController::class, 'adminList']);
        Route::post('boxes', [BoxController::class, 'store']);
        Route::put('boxes/{id}', [BoxController::class, 'update']);
        Route::delete('boxes/{id}', [BoxController::class, 'destroy']);

        Route::get('users', [AdminController::class, 'getAllUsers']);
        Route::put('users/{id}/ban', [AdminController::class, 'toggleBan']);
    });

Route::prefix('api/prestataire')
    ->middleware('web')
    ->group(function () {
        Route::get('evaluations',     [PrestataireController::class, 'mesEvaluations']);
        Route::get('disponibilites',  [PrestataireController::class, 'mesDisponibilites']);
        Route::post('disponibilite',  [PrestataireController::class, 'ajouterDisponibilite']);
        Route::delete('disponibilite/{id}', [PrestataireController::class, 'supprimerDisponibilite']);
        Route::get('interventions',   [PrestataireController::class, 'mesInterventions']);
        Route::get('factures',        [PrestataireController::class, 'mesFactures']);
        Route::get('facture/{id}',    [PrestataireController::class, 'telechargerFacture']);
    });


Route::prefix('api')
    ->middleware(['web', 'auth.session'])
    ->group(function () {
        Route::post('handoff',             [HandoffController::class, 'store']);
        Route::post('handoff/{id}/accept', [HandoffController::class, 'accept']);
        Route::post('handoff/{id}/refuse', [HandoffController::class, 'refuse']);
        Route::post('handoff/{id}/cancel', [HandoffController::class, 'cancel']);
    });


Route::get('api/livreur/planning', [LivreurRouteController::class, 'planning'])
    ->middleware(['web', 'auth.session', 'role:Deliverer']);

Route::middleware(['web', 'auth.session', 'role:Admin,Seller,ServiceProvider'])
    ->prefix('api/contracts')
    ->group(function () {
        Route::get('/',         [ContractController::class, 'index']);
        Route::get('{id}',      [ContractController::class, 'show']);
        Route::post('/',        [ContractController::class, 'store']);
        Route::put('{id}',      [ContractController::class, 'update']);
        Route::delete('{id}',   [ContractController::class, 'destroy']);
        Route::post('{id}/renew', [ContractController::class, 'renew']);
    });

Route::prefix('api')
    ->middleware(['web', 'auth.session', 'role:Customer,Seller'])
    ->group(function () {
        Route::post('orders/{order}/reviews', [ReviewController::class, 'store']);
    });
Route::prefix('api')
    ->middleware(['web', 'auth.session'])
    ->group(function () {
        Route::get('reviews/deliverer/{id}', [ReviewController::class, 'indexForDeliverer']);
    });

Route::prefix('api/suivi')
    ->middleware(['web', 'auth.session', 'role:Deliverer'])
    ->group(function () {
        Route::get('/',   [SuiviController::class, 'index']);
        Route::post('/',  [SuiviController::class, 'store']);
    });

Route::prefix('api/admin')
    ->middleware(['web', 'auth.session', 'role:Admin'])
    ->group(function () {
        Route::get('reports',         [ReportController::class, 'index']);
        Route::patch('reports/{id}',  [ReportController::class, 'updateStatus']);
        Route::delete('reports/{id}', [ReportController::class, 'destroy']);
    });

Route::prefix('api/admin')
    ->middleware(['web', 'auth.session', 'role:Admin'])
    ->group(function () {
        Route::get('reports',       [ReportController::class, 'index']);
        Route::patch('reports/{id}', [ReportController::class, 'updateStatus']);
    });

Route::middleware(['web', 'auth.session'])
    ->prefix('api')
    ->group(function () {
        Route::get('profile',        [\App\Http\Controllers\ProfileController::class, 'show']);
        Route::post('profile/update', [\App\Http\Controllers\ProfileController::class, 'update']);
    });

// Routes du portefeuille pour livreurs et prestataires
Route::middleware(['web', 'auth.session'])
    ->prefix('api')
    ->group(function () {
        Route::get('wallet', [WalletController::class, 'show']);
        Route::post('wallet/withdraw', [WalletController::class, 'withdraw']);
        Route::get('wallet/received', [WalletController::class, 'received']);
        Route::get('wallet/withdrawals', [WalletController::class, 'listWithdrawals']);
    });

Route::middleware(['web', 'auth.session'])
    ->prefix('api')
    ->group(function () {
        Route::post('/checkout/seller', [StripeController::class, 'checkoutSeller']);
    });


Route::middleware(['web', 'auth.session'])
    ->prefix('api')
    ->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/download/{id}', [InvoiceController::class, 'download']);
    });

Route::middleware(['web', 'auth.session'])->prefix('api')->group(function () {
    Route::post('/negotiation/propose', [NegotiationController::class, 'propose']);
    Route::post('/negotiation/{id}/accept', [NegotiationController::class, 'accept']);
    Route::post('/negotiation/{id}/reject', [NegotiationController::class, 'reject']);
});

Route::prefix('api/admin')
    ->middleware(['web', 'auth.session'])
    ->group(function () {
        Route::get('reviews', [AdminReviewController::class, 'index']);
        Route::delete('reviews/{id}', [AdminReviewController::class, 'destroy']);
        Route::get('reviews/prestataires', [AdminReviewController::class, 'avisPrestataires']);
    });

    Route::prefix('api/annonce')
    ->middleware(['web', 'role:Seller,Customer'])
    ->group(function () {
        Route::post('estimate', [AnnonceController::class, 'estimate']);
    });

    Route::get('api/adresses', [AnnonceController::class, 'adresses'])
     ->middleware(['web', 'auth.session']);

    Route::prefix('api/qr')
        ->middleware(['web', 'auth.session'])
        ->group(function () {
            Route::post('validate', [QrCodeController::class, 'validateLivreur']);
            Route::get('generate', [QrCodeController::class, 'generateQrCode']);
            Route::post('confirm-delivery', [QrCodeController::class, 'confirmDelivery']);
        });

    Route::get('/nfc', [QrCodeController::class, 'handle']);

    Route::prefix('api/role-change')
        ->middleware(['web', 'auth.custom'])
        ->group(function () {
            Route::post('request', [RoleChangeController::class, 'requestRole']);
            Route::post('upload-justificatifs', [RoleChangeController::class, 'uploadJustificatifs']);
            Route::get('my-requests', [RoleChangeController::class, 'myRequests']);
            Route::patch('{id}/cancel', [RoleChangeController::class, 'cancelRequest']);
        });

    Route::prefix('api/admin/role-change')
        ->middleware(['web', 'auth.custom', 'role:Admin'])
        ->group(function () {
            Route::get('/', [RoleChangeController::class, 'adminIndex']);
            Route::patch('{id}/approve', [RoleChangeController::class, 'adminApprove']);
            Route::patch('{id}/reject', [RoleChangeController::class, 'adminReject']);
            Route::get('justificatif/{justificatifId}', [RoleChangeController::class, 'serveJustificatif']);
        });

    Route::get('api/storage/uploads/{filename}', function ($filename) {
        $path = storage_path('app/public/uploads/' . $filename);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        $file = new \SplFileInfo($path);
        $mimeType = match($file->getExtension()) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream'
        };
        
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600'
        ]);
    })->where('filename', '[^/]+');

    Route::get('storage/justificatifs/{userId}/{filename}', function ($userId, $filename) {
        $path = storage_path("app/public/justificatifs/{$userId}/" . $filename);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        $file = new \SplFileInfo($path);
        $mimeType = match($file->getExtension()) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream'
        };
        
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600'
        ]);
    })->where(['userId' => '[0-9]+', 'filename' => '[^/]+']);