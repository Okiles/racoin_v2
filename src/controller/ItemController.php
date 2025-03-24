<?php

namespace controller;
use AllowDynamicProperties;
use model\Annonce;
use model\Annonceur;
use model\Departement;
use model\Photo;
use model\Categorie;

#[AllowDynamicProperties]
class ItemController {
    
    public function __construct() {}

    // Affiche un item
    public function afficherItem($twig, $menu, $chemin, $n, $cat): void {
        $this->annonce = Annonce::with(['Annonceur', 'Departement', 'Photos'])->find($n);
        if (!$this->annonce) {
            echo "404"; // Si l'annonce n'existe pas
            return;
        }

        $menu = [
            ['href' => $chemin, 'text' => 'Accueil'],
            ['href' => $chemin . "/cat/" . $n, 'text' => Categorie::find($this->annonce->id_categorie)?->nom_categorie],
            ['href' => $chemin . "/item/" . $n, 'text' => $this->annonce->titre]
        ];

        $template = $twig->load("item.html.twig");
        echo $template->render([
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "annonceur" => $this->annonce->Annonceur,
            "dep" => $this->annonce->Departement->nom_departement,
            "photo" => $this->annonce->Photos,
            "categories" => $cat
        ]);
    }

    // Supprime un item (GET)
    public function supprimerItemGet($twig, $menu, $chemin, $n) {
        $this->annonce = Annonce::find($n);
        if (!$this->annonce) {
            echo "404"; // Si l'annonce n'existe pas
            return;
        }

        $template = $twig->load("delGet.html.twig");
        echo $template->render([
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce
        ]);
    }

    // Supprime un item (POST)
    public function supprimerItemPost($twig, $menu, $chemin, $n, $cat) {
        $this->annonce = Annonce::find($n);
        if (!$this->annonce || !password_verify($_POST["pass"], $this->annonce->mdp)) {
            echo "Erreur de mot de passe ou annonce non trouvée.";
            return;
        }

        // Suppression des photos et de l'annonce
        $this->annonce->Photos()->delete();
        $this->annonce->delete();

        $template = $twig->load("delPost.html.twig");
        echo $template->render([
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "pass" => true,
            "categories" => $cat
        ]);
    }

    // Modifie un item (GET)
    public function modifyGet($twig, $menu, $chemin, $id) {
        $this->annonce = Annonce::find($id);
        if (!$this->annonce) {
            echo "404"; // Si l'annonce n'existe pas
            return;
        }

        $template = $twig->load("modifyGet.html.twig");
        echo $template->render([
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce
        ]);
    }

    // Modifie un item (POST)
    public function modifyPost($twig, $menu, $chemin, $n, $cat, $dpt) {
        $this->annonce = Annonce::find($n);
        if (!$this->annonce || !password_verify($_POST["pass"], $this->annonce->mdp)) {
            echo "Erreur de mot de passe.";
            return;
        }

        // Validation des entrées
        $errors = $this->validateAnnonce($_POST);
        if (!empty($errors)) {
            $template = $twig->load("add-error.html.twig");
            echo $template->render([
                "breadcrumb" => $menu,
                "chemin" => $chemin,
                "errors" => $errors
            ]);
            return;
        }

        // Mise à jour de l'annonce
        $this->updateAnnonce($n, $_POST);

        // Affichage de la confirmation
        $template = $twig->load("modif-confirm.html.twig");
        echo $template->render([
            "breadcrumb" => $menu,
            "chemin" => $chemin
        ]);
    }

    // Validation des données du formulaire
    private function validateAnnonce($data) {
        $errors = [];
        
        if (empty($data['nom'])) $errors['nameAdvertiser'] = 'Veuillez entrer votre nom';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['emailAdvertiser'] = 'Adresse mail invalide';
        if (empty($data['phone']) || !is_numeric($data['phone'])) $errors['phoneAdvertiser'] = 'Numéro de téléphone invalide';
        if (empty($data['ville'])) $errors['villeAdvertiser'] = 'Ville manquante';
        if (empty($data['departement']) || !is_numeric($data['departement'])) $errors['departmentAdvertiser'] = 'Département invalide';
        if (empty($data['categorie']) || !is_numeric($data['categorie'])) $errors['categorieAdvertiser'] = 'Catégorie invalide';
        if (empty($data['title'])) $errors['titleAdvertiser'] = 'Titre manquant';
        if (empty($data['description'])) $errors['descriptionAdvertiser'] = 'Description manquante';
        if (empty($data['price']) || !is_numeric($data['price'])) $errors['priceAdvertiser'] = 'Prix invalide';
        
        return $errors;
    }

    // Mise à jour de l'annonce
    private function updateAnnonce($n, $data) {
        $this->annonce = Annonce::find($n);
        $this->annonce->fill([
            'titre' => htmlentities($data['title']),
            'description' => htmlentities($data['description']),
            'prix' => htmlentities($data['price']),
            'ville' => htmlentities($data['ville']),
            'id_departement' => $data['departement'],
            'id_categorie' => $data['categorie'],
            'mdp' => password_hash($data['psw'], PASSWORD_DEFAULT),
            'date' => date('Y-m-d')
        ]);
        $this->annonce->save();

        // Mise à jour de l'annonceur
        $this->annonce->Annonceur->fill([
            'email' => htmlentities($data['email']),
            'nom_annonceur' => htmlentities($data['nom']),
            'telephone' => htmlentities($data['phone'])
        ]);
        $this->annonce->Annonceur->save();
    }
}
