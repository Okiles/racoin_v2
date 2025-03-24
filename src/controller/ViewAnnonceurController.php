<?php

namespace controller;
use model\Annonce;
use model\Annonceur;
use model\Photo;

class ViewAnnonceurController {
    public function __construct() {}

    function afficherAnnonceur($twig, $menu, $chemin, $n, $cat) {
        $this->annonceur = Annonceur::find($n);

        // Check if the annonceur exists
        if (!$this->annonceur) {
            // Instead of echoing "404", return a proper error response or redirect
            header("HTTP/1.0 404 Not Found");
            echo "Annonceur not found.";
            return;
        }

        // Fetch annonces for this annonceur
        $annonces = Annonce::where('id_annonceur', '=', $n)->get()->map(function($a) use ($chemin) {
            // Retrieve the number of photos and URL for each annonce
            $a->nb_photo = Photo::where('id_annonce', '=', $a->id_annonce)->count();
            $a->url_photo = $this->getPhotoUrl($a->id_annonce, $chemin);
            return $a;
        });

        // Load and render the template
        $template = $twig->load("annonceur.html.twig");
        echo $template->render([
            'nom' => $this->annonceur,
            "chemin" => $chemin,
            "annonces" => $annonces,
            "categories" => $cat
        ]);
    }

    /**
     * Retrieve the URL for the first photo of an annonce or a default image.
     *
     * @param int $annonceId
     * @param string $chemin
     * @return string
     */
    private function getPhotoUrl($annonceId, $chemin) {
        // Retrieve the first photo URL or return the default image if none exists
        $photo = Photo::select('url_photo')->where('id_annonce', '=', $annonceId)->first();
        return $photo ? $photo->url_photo : $chemin . '/img/noimg.png';
    }
}
