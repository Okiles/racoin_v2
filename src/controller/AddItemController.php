<?php

namespace controller;

use model\Annonce;
use model\Annonceur;

class AddItemController
{
    function addItemView($twig, $menu, $chemin, $cat, $dpt)
    {
        echo $twig->load("add.html.twig")->render([
            "breadcrumb"   => $menu,
            "chemin"       => $chemin,
            "categories"   => $cat,
            "departements" => $dpt
        ]);
    }

    private function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    function addNewItem($twig, $menu, $chemin, $allPostVars)
    {
        date_default_timezone_set('Europe/Paris');

        // Récupération et nettoyage des données du formulaire
        $fields = ['nom', 'email', 'phone', 'ville', 'departement', 'categorie', 'title', 'description', 'price', 'psw', 'confirm-psw'];
        $data = array_map(fn($field) => trim($_POST[$field] ?? ''), $fields);

        // Initialisation des erreurs
        $errors = [
            'nameAdvertiser'        => empty($data['nom']) ? 'Veuillez entrer votre nom' : '',
            'emailAdvertiser'       => !$this->isEmail($data['email']) ? 'Veuillez entrer une adresse mail correcte' : '',
            'phoneAdvertiser'       => empty($data['phone']) || !preg_match('/^\d{10}$/', $data['phone']) ? 'Veuillez entrer un numéro de téléphone valide' : '',
            'villeAdvertiser'       => empty($data['ville']) ? 'Veuillez entrer votre ville' : '',
            'departmentAdvertiser'  => !ctype_digit($data['departement']) ? 'Veuillez choisir un département valide' : '',
            'categorieAdvertiser'   => !ctype_digit($data['categorie']) ? 'Veuillez choisir une catégorie valide' : '',
            'titleAdvertiser'       => empty($data['title']) ? 'Veuillez entrer un titre' : '',
            'descriptionAdvertiser' => empty($data['description']) ? 'Veuillez entrer une description' : '',
            'priceAdvertiser'       => empty($data['price']) || !is_numeric($data['price']) ? 'Veuillez entrer un prix valide' : '',
            'passwordAdvertiser'    => empty($data['psw']) || empty($data['confirm-psw']) || $data['psw'] !== $data['confirm-psw'] ? 'Les mots de passe ne sont pas identiques' : '',
        ];

        // Filtrage des erreurs vides
        $errors = array_filter($errors);

        if (!empty($errors)) {
            echo $twig->load("add-error.html.twig")->render([
                "breadcrumb" => $menu,
                "chemin"     => $chemin,
                "errors"     => $errors
            ]);
            return;
        }

        // Création des objets Annonceur et Annonce
        $annonceur = new Annonceur([
            'email'         => htmlspecialchars($data['email']),
            'nom_annonceur' => htmlspecialchars($data['nom']),
            'telephone'     => htmlspecialchars($data['phone'])
        ]);

        $annonce = new Annonce([
            'ville'          => htmlspecialchars($data['ville']),
            'id_departement' => (int) $data['departement'],
            'prix'           => htmlspecialchars($data['price']),
            'mdp'            => password_hash($data['psw'], PASSWORD_DEFAULT),
            'titre'          => htmlspecialchars($data['title']),
            'description'    => htmlspecialchars($data['description']),
            'id_categorie'   => (int) $data['categorie'],
            'date'           => date('Y-m-d')
        ]);

        // Sauvegarde en base de données
        $annonceur->save();
        $annonceur->annonce()->save($annonce);

        echo $twig->load("add-confirm.html.twig")->render([
            "breadcrumb" => $menu,
            "chemin"     => $chemin
        ]);
    }
}
