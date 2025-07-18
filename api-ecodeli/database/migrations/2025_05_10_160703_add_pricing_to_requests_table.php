<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPricingToRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            // 1) Type d'annonce : merchant ou client
            $table->enum('type', ['merchant','client'])
                  ->default('client')
                  ->after('user_id');

            // 2) Caractéristiques pour le calcul du prix
            $table->float('poids')->nullable()->after('destination_code');
            $table->float('longueur')->nullable();
            $table->float('largeur')->nullable();
            $table->float('hauteur')->nullable();
            $table->float('distance')->nullable();

            // 3) Prix stockés en centimes
            $table->integer('prix_cents')
                  ->nullable()
                  ->after('distance');
            $table->integer('prix_negocie_cents')
                  ->nullable()
                  ->after('prix_cents');
        });
    }

    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'poids','longueur','largeur','hauteur','distance',
                'prix_cents','prix_negocie_cents'
            ]);
        });
    }
}
