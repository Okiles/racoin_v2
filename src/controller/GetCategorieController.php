<?php

namespace controller;

use model\Categorie;
use model\Annonce;
use model\Photo;
use model\Annonceur;

class GetCategorieController {

    protected $annonces = [];

    /**
     * Récupère toutes les catégories triées par nom.
     */
    public function getCategories() {
        return Categorie::orderBy('nom_categorie')->get()->toArray();
    }

    /**
     * Récupère les annonces d'une catégorie et leurs détails.
     */
    public function getCategorieContent($chemin, $n) {
        $annonces = Annonce::with(['Annonceur', 'Photos'])
            ->where('id_categorie', $n)
            ->orderByDesc('id_annonce')
            ->get();

        // Parcours des annonces pour enrichir les données
        $this->annonces = $annonces->map(function ($annonce) use ($chemin) {
            $annonce->nb_photo = $annonce->Photos->count();
            $annonce->url_photo = $annonce->nb_photo > 0
                ? $annonce->Photos->pluck('url_photo')->first()
                : $chemin . '/img/noimg.png';

            $annonce->nom_annonceur = $annonce->Annonceur->nom_annonceur ?? 'Inconnu';

            return $annonce;
        })->toArray();
    }

    /**
     * Affiche les annonces d'une catégorie.
     */
    public function displayCategorie($twig, $menu, $chemin, $cat, $n) {
        $template = $twig->load("index.html.twig");

        // Création du fil d'Ariane (breadcrumb)
        $menu = [
            ['href' => $chemin, 'text' => 'Accueil'],
            ['href' => $chemin . "/cat/" . $n, 'text' => Categorie::findOrFail($n)->nom_categorie]
        ];

        // Chargement des annonces pour la catégorie donnée
        $this->getCategorieContent($chemin, $n);

        echo $template->render([
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "categories" => $cat,
            "annonces" => $this->annonces
        ]);
    }
}
