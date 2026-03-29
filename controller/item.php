<?php

declare(strict_types=1);

namespace controller;

use App\Support\Validation\AnnonceInputValidator;
use AllowDynamicProperties;
use model\Annonce;
use model\Annonceur;
use model\Departement;
use model\Photo;
use model\Categorie;

#[AllowDynamicProperties] class item {
    public function __construct(){
    }
    function afficherItem($twig, $menu, $chemin, $n, $cat): void
    {

        $this->annonce = Annonce::find($n);
        if(!isset($this->annonce)){
            echo "404";
            return;
        }

        $menu = array(
            array('href' => $chemin,
                'text' => 'Acceuil'),
            array('href' => $chemin."/cat/".$n,
                'text' => Categorie::find($this->annonce->id_categorie)?->nom_categorie),
            array('href' => $chemin."/item/".$n,
            'text' => $this->annonce->titre)
        );

        $this->annonceur = Annonceur::find($this->annonce->id_annonceur);
        $this->departement = Departement::find($this->annonce->id_departement );
        $this->photo = Photo::where('id_annonce', '=', $n)->get();
        $template = $twig->load("item.html.twig");
        echo $template->render(array("breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "annonceur" => $this->annonceur,
            "dep" => $this->departement->nom_departement,
            "photo" => $this->photo,
            "categories" => $cat));
    }

    function supprimerItemGet($twig, $menu, $chemin,$n){
        $this->annonce = Annonce::find($n);
        if(!isset($this->annonce)){
            echo "404";
            return;
        }
        $template = $twig->load("delGet.html.twig");
        echo $template->render(array("breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce));
    }


    function supprimerItemPost($twig, $menu, $chemin, $n, $cat){
        $this->annonce = Annonce::find($n);
        $reponse = false;
        if(password_verify((string) ($_POST['pass'] ?? ''), $this->annonce->mdp)){
            $reponse = true;
            photo::where('id_annonce', '=', $n)->delete();
            $this->annonce->delete();

        }

        $template = $twig->load("delPost.html.twig");
        echo $template->render(array("breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "pass" => $reponse,
            "categories" => $cat));
    }

    function modifyGet($twig, $menu, $chemin, $id){
        $this->annonce = Annonce::find($id);
        if(!isset($this->annonce)){
            echo "404";
            return;
        }
        $template = $twig->load("modifyGet.html.twig");
        echo $template->render(array("breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce));
    }

    function modifyPost($twig, $menu, $chemin, $n, $cat, $dpt){
        $this->annonce = Annonce::find($n);
        $this->annonceur = Annonceur::find($this->annonce->id_annonceur);
        $this->categItem = Categorie::find($this->annonce->id_categorie)->nom_categorie;
        $this->dptItem = Departement::find($this->annonce->id_departement)->nom_departement;

        $reponse = false;
        if(password_verify((string) ($_POST['pass'] ?? ''), $this->annonce->mdp)){
            $reponse = true;

        }

        $template = $twig->load("modifyPost.html.twig");
        echo $template->render(array("breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "annonceur" => $this->annonceur,
            "pass" => $reponse,
            "categories" => $cat,
            "departements" => $dpt,
            "dptItem" => $this->dptItem,
            "categItem" => $this->categItem));
    }

    function edit($twig, $menu, $chemin, $allPostVars, $id){
        date_default_timezone_set('Europe/Paris');

        $validator = new AnnonceInputValidator();
        $errors = $validator->validateForUpdate($allPostVars);

        if (!empty($errors)) {
            $template = $twig->load('add-error.html.twig');
            echo $template->render([
                'breadcrumb' => $menu,
                'chemin' => $chemin,
                'errors' => $errors,
            ]);

            return;
        }

        $this->annonce = Annonce::find($id);
        $idannonceur = $this->annonce->id_annonceur;
        $this->annonceur = Annonceur::find($idannonceur);

        $this->annonceur->email = htmlentities((string) ($allPostVars['email'] ?? ''));
        $this->annonceur->nom_annonceur = htmlentities((string) ($allPostVars['nom'] ?? ''));
        $this->annonceur->telephone = htmlentities((string) ($allPostVars['phone'] ?? ''));

        $this->annonce->ville = htmlentities((string) ($allPostVars['ville'] ?? ''));
        $this->annonce->id_departement = (int) ($allPostVars['departement'] ?? 0);
        $this->annonce->prix = htmlentities((string) ($allPostVars['price'] ?? ''));
        $this->annonce->titre = htmlentities((string) ($allPostVars['title'] ?? ''));
        $this->annonce->description = htmlentities((string) ($allPostVars['description'] ?? ''));
        $this->annonce->id_categorie = (int) ($allPostVars['categorie'] ?? 0);
        $this->annonce->date = date('Y-m-d');

        if (!empty($allPostVars['psw'])) {
            $this->annonce->mdp = password_hash((string) $allPostVars['psw'], PASSWORD_DEFAULT);
        }

        $this->annonceur->save();
        $this->annonceur->annonce()->save($this->annonce);

        $template = $twig->load('modif-confirm.html.twig');
        echo $template->render(['breadcrumb' => $menu, 'chemin' => $chemin]);
    }
}
