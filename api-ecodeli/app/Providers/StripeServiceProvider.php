<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;
use Stripe\StripeClient;

/**
 * StripeServiceProvider
 *
 * Fournit et configure le client Stripe pour toute l'application.
 * - Enregistre un singleton de StripeClient dans le conteneur de services.
 * - Définit la clé secrète pour les appels à l'API Stripe.
 *
 * Usage dans vos classes :
 *   public function __construct(StripeClient $stripe) { ... }
 */
class StripeServiceProvider extends ServiceProvider
{
    /**
     * Indique si la chargeur de services est différé.
     * Ici false car on l'utilise dès le boot.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register services.
     *
     * Bind StripeClient comme singleton pour injection de dépendance.
     */
    public function register()
    {
        $this->app->singleton(StripeClient::class, function ($app) {
            // Récupère la clé secrète depuis config/stripe.php
            $secret = config('services.stripe.secret');

            // Instancie le client Stripe avec la clé
            return new StripeClient($secret);
        });
    }

    /**
     * Bootstrap services.
     *
     * Configure globalement Stripe (clé par défaut).
     */
    public function boot()
    {
        // Définit la clé secrète pour les appels statiques (ex : Stripe::setApiKey())
        Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [StripeClient::class];
    }
}
