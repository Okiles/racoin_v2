<?php

namespace controller;

use model\Annonce;
use model\Photo;
use model\Annonceur;

class IndexController
{
    protected $annonces = [];

    /**
     * Affiche toutes les annonces sur la page d'accueil
     */
    public function displayAllAnnonce($twig, $menu, $chemin, $cat)
    {
        $template = $twig->load("index.html.twig");

        // Création du menu
        $menu = [
            ['href' => $chemin, 'text' => 'Accueil']
        ];

        // Récupération des annonces
        $this->getAll($chemin);

        // Rendu du template avec les données
        echo $template->render([
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat,
            "annonces"   => $this->annonces
        ]);
    }

    /**
     * Récupère toutes les annonces et leur associe les informations supplémentaires
     */
    public function getAll($chemin)
    {
        // Récupère les annonces avec leurs relations 'Annonceur' et 'Photos'
        $tmp = Annonce::with(['Annonceur', 'Photos'])
            ->orderBy('id_annonce', 'desc')
            ->take(12)
            ->get();

        // Transformation des annonces pour ajouter les données nécessaires
        $this->annonces = $tmp->map(function ($t) use ($chemin) {
            // Nombre de photos et URL de la première photo
            $t->nb_photo = $t->Photos->count();
            $t->url_photo = $t->nb_photo > 0
                ? $t->Photos->pluck('url_photo')->first()
                : $chemin . '/img/noimg.png';

            // Nom de l'annonceur
            $t->nom_annonceur = $t->Annonceur->nom_annonceur ?? 'Inconnu';

            return $t;
        })->toArray();
    }
}
