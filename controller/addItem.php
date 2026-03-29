<?php

declare(strict_types=1);

namespace controller;

use App\Support\Validation\AnnonceInputValidator;
use model\Annonce;
use model\Annonceur;

class addItem
{
    public function addItemView($twig, array $menu, string $chemin, array $cat, array $dpt): void
    {
        $template = $twig->load('add.html.twig');
        echo $template->render([
            'breadcrumb' => $menu,
            'chemin' => $chemin,
            'categories' => $cat,
            'departements' => $dpt,
        ]);
    }

    public function addNewItem($twig, array $menu, string $chemin, array $allPostVars): void
    {
        date_default_timezone_set('Europe/Paris');
        $validator = new AnnonceInputValidator();
        $errors = $validator->validateForCreate($allPostVars);

        if (!empty($errors)) {
            $template = $twig->load('add-error.html.twig');
            echo $template->render([
                'breadcrumb' => $menu,
                'chemin' => $chemin,
                'errors' => $errors,
            ]);

            return;
        }

        $annonce = new Annonce();
            $annonceur = new Annonceur();

        $annonceur->email = htmlentities((string) ($allPostVars['email'] ?? ''));
        $annonceur->nom_annonceur = htmlentities((string) ($allPostVars['nom'] ?? ''));
        $annonceur->telephone = htmlentities((string) ($allPostVars['phone'] ?? ''));

        $annonce->ville = htmlentities((string) ($allPostVars['ville'] ?? ''));
        $annonce->id_departement = (int) ($allPostVars['departement'] ?? 0);
        $annonce->prix = htmlentities((string) ($allPostVars['price'] ?? ''));
        $annonce->mdp = password_hash((string) ($allPostVars['psw'] ?? ''), PASSWORD_DEFAULT);
        $annonce->titre = htmlentities((string) ($allPostVars['title'] ?? ''));
        $annonce->description = htmlentities((string) ($allPostVars['description'] ?? ''));
        $annonce->id_categorie = (int) ($allPostVars['categorie'] ?? 0);
        $annonce->date = date('Y-m-d');

        $annonceur->save();
        $annonceur->annonce()->save($annonce);

        $template = $twig->load('add-confirm.html.twig');
        echo $template->render(['breadcrumb' => $menu, 'chemin' => $chemin]);
    }
}
