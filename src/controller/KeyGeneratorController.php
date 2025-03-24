<?php

namespace controller;

use model\ApiKey;

class KeyGeneratorController {

    private function generateMenu($chemin) {
        return array(
            array('href' => $chemin, 'text' => 'Accueil'),
            array('href' => $chemin."/search", 'text' => "Recherche")
        );
    }

    function show($twig, $menu, $chemin, $cat) {
        $template = $twig->load("key-generator.html.twig");
        $menu = $this->generateMenu($chemin);
        echo $template->render(array("breadcrumb" => $menu, "chemin" => $chemin, "categories" => $cat));
    }

    function generateKey($twig, $menu, $chemin, $cat, $nom) {
        // Remove spaces from name
        $nospace_nom = str_replace(' ', '', $nom);

        // Early return if the name is empty
        if ($nospace_nom === '') {
            $template = $twig->load("key-generator-error.html.twig");
            $menu = $this->generateMenu($chemin);
            echo $template->render(array("breadcrumb" => $menu, "chemin" => $chemin, "categories" => $cat));
            return;
        }

        // Generate unique key and add it to the database
        $key = uniqid();
        $apikey = new ApiKey();
        $apikey->id_apikey = $key;
        $apikey->name_key = htmlentities($nom);
        $apikey->save();

        // Render result template
        $template = $twig->load("key-generator-result.html.twig");
        $menu = $this->generateMenu($chemin);
        echo $template->render(array("breadcrumb" => $menu, "chemin" => $chemin, "categories" => $cat, "key" => $key));
    }

}

?>
