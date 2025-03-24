<?php

namespace controller;

use model\Annonce;
use model\Categorie;

class SearchController {

    private function generateMenu($chemin, $text) {
        return array(
            array('href' => $chemin, 'text' => 'Accueil'),
            array('href' => $chemin."/search", 'text' => $text)
        );
    }

    function show($twig, $menu, $chemin, $cat) {
        $template = $twig->load("search.html.twig");
        $menu = $this->generateMenu($chemin, 'Recherche');
        echo $template->render(array("breadcrumb" => $menu, "chemin" => $chemin, "categories" => $cat));
    }

    function research($array, $twig, $menu, $chemin, $cat) {
        $template = $twig->load("index.html.twig");
        $menu = $this->generateMenu($chemin, "Résultats de la recherche");

        // Sanitize and trim inputs
        $motclef = str_replace(' ', '', trim($array['motclef']));
        $codepostal = str_replace(' ', '', trim($array['codepostal']));
        $categorie = trim($array['categorie']);
        $prixMin = trim($array['prix-min']);
        $prixMax = trim($array['prix-max']);

        $query = Annonce::select();

        // If no search criteria, get all annonces
        if (empty($motclef) && empty($codepostal) && ($categorie === "Toutes catégories" || $categorie === "-----") && $prixMin === "Min" && ($prixMax === "Max" || $prixMax === "nolimit")) {
            $annonce = Annonce::all();
        } else {
            // Build the query based on search criteria
            if (!empty($motclef)) {
                $query->where('description', 'like', '%' . $motclef . '%');
            }

            if (!empty($codepostal)) {
                $query->where('ville', '=', $codepostal);
            }

            if ($categorie !== "Toutes catégories" && $categorie !== "-----") {
                $categ = Categorie::select('id_categorie')->where('id_categorie', '=', $categorie)->first();
                if ($categ) {
                    $query->where('id_categorie', '=', $categ->id_categorie);
                }
            }

            // Price filter
            if ($prixMin !== "Min" && $prixMax !== "Max") {
                if ($prixMax !== "nolimit") {
                    $query->whereBetween('prix', [$prixMin, $prixMax]);
                } else {
                    $query->where('prix', '>=', $prixMin);
                }
            } elseif ($prixMax !== "Max" && $prixMax !== "nolimit") {
                $query->where('prix', '<=', $prixMax);
            } elseif ($prixMin !== "Min") {
                $query->where('prix', '>=', $prixMin);
            }

            // Execute the query
            $annonce = $query->get();
        }

        echo $template->render(array("breadcrumb" => $menu, "chemin" => $chemin, "annonces" => $annonce, "categories" => $cat));
    }

}

?>
