<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait BannedWordsChecker
{
    // Liste de mots interdits (à enrichir si besoin)
    private $badWords = [
        'pédophile', 'pedophile', 'prostitution', 'nude', 'porn', 'sexe', 'escort', 'camgirl', 'cam boy', 'camgirl', 'cam boy',
        'inceste', 'zoophile',               'bestialité', 'bestiality', 'child porn', 'childporn', 'fuck', 'shit', 'bitch', 'pute', 'salope',
        'escort', 'escort girl', 'escort boy', 'xxx', 'anal', 'blowjob', 'handjob', 'cum', 'sperme', 'masturbation',
        'orgie', 'gangbang', 'bdsm', 'bondage', 'fellation', 'pénétration', 'viol', 'rape', 'abuse', 'abuser', 'molester',
        'incest', 'zoophilia', 'zoophile', 'zoophilie', 'bestiality', 'bestialité', 'mineur', 'minors',
        'minor', 
        'escort', 'escorte', 'escorting', 'prostitute', 'prostituée', 'prostitue', 'prostituer', 'prostitution', 'prostitutions',
        'naked', 'nudité', 'nudite', 'nudity', 'nude', 'nudes', 'sextape', 'sex tape', 'sex', 'sexual', 'sexuel', 'sexuelle',
        'sexuelles', 'sexuels', 'pornographie', 'pornography', 'porno', 'porn', 'pornographique', 'pornographiques', 'hardcore',
        'softcore', 'orgasm', 'orgasme', 'ejaculation', 'éjaculation', 'ejaculate', 'éjaculer', 'ejaculer', 'clitoris', 'vagin',
        'penis', 'pénis', 'bite', 'queue', 'verge', 'couilles', 'testicules', 'seins', 'nichons', 'tétons', 'téton', 'fesses',
        'cul', 'anus', 'sodomie', 'sodomize', 'sodomiser', 'sodomized', 'sodomisée', 'sodomisées', 'sodomisés', 'sodomizing',
        'sodomisation', 'sodomisations', 'sodomite', 'sodomites', 'sodomit', 'sodom', 'sodomy', 'sodomies', 'sodomizing',
        'sodomized', 'sodomizing', 'sodomizes', 'sodomiser', 'sodomisée', 'sodomisées', 'sodomisés', 'sodomisation',
        'sodomisations', 'sodomite', 'sodomites', 'sodomit', 'sodom', 'sodomy', 'sodomies', 'sodomizing', 'sodomized',
        'sodomizing', 'sodomizes', 'sodomiser', 'sodomisée', 'sodomisées', 'sodomisés', 'sodomisation', 'sodomisations',
        'sodomite', 'sodomites', 'sodomit', 'sodom', 'sodomy', 'sodomies', 'sodomizing', 'sodomized', 'sodomizing', 'sodomizes',
    ];

    /**
     * Vérifie si le contenu contient des mots interdits
     */
    protected function checkBannedWords($content, $userId = null)
    {
        if (empty($content)) return false;
        
        $content = strtolower($content);
        
        foreach ($this->badWords as $word) {
            if (strpos($content, $word) !== false) {
                // Bannir l'utilisateur si un userId est fourni
                if ($userId) {
                    DB::table('users')->where('user_id', $userId)->update(['banned' => 1]);

                    // Refuser toutes ses prestations (si un champ 'status' existe)
                    if (Schema::hasColumn('service', 'status')) {
                        DB::table('service')->where('user_id', $userId)->update(['status' => 'refusé']);
                    }
                    // Refuser toutes ses propositions de types de prestations
                    if (Schema::hasColumn('proposition_de_prestations', 'statut')) {
                        DB::table('proposition_de_prestations')->where('user_id', $userId)->update(['statut' => 'Refusé']);
                    }

                    // Créer une alerte admin
                    DB::table('admin_alerts')->insert([
                        'user_id'     => $userId,
                        'description' => $content,
                        'mot_incrimine' => $word,
                        'created_at'  => now(),
                    ]);
                }
                
                return [
                    'banned' => true,
                    'word' => $word,
                    'message' => 'Votre compte a été banni pour non-respect des règles.'
                ];
            }
        }
        
        return false;
    }

    /**
     * Vérifie plusieurs champs pour des mots interdits
     */
    protected function checkMultipleFields($fields, $userId = null)
    {
        foreach ($fields as $content) {
            $checkResult = $this->checkBannedWords($content, $userId);
            if ($checkResult) {
                return $checkResult;
            }
        }
        
        return false;
    }
} 