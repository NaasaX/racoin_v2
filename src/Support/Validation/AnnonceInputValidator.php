<?php

declare(strict_types=1);

namespace App\Support\Validation;

final class AnnonceInputValidator
{
    /**
     * @return list<string>
     */
    public function validateForCreate(array $input): array
    {
        return $this->validate($input, true);
    }

    /**
     * @return list<string>
     */
    public function validateForUpdate(array $input): array
    {
        return $this->validate($input, false);
    }

    /**
     * @return list<string>
     */
    private function validate(array $input, bool $requirePasswordConfirmation): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $ville = trim((string) ($input['ville'] ?? ''));
        $departement = trim((string) ($input['departement'] ?? ''));
        $categorie = trim((string) ($input['categorie'] ?? ''));
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $price = trim((string) ($input['price'] ?? ''));
        $password = trim((string) ($input['psw'] ?? ''));
        $passwordConfirm = trim((string) ($input['confirm-psw'] ?? ''));

        $errors = [];

        if ($nom === '') {
            $errors[] = 'Veuillez entrer votre nom';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Veuillez entrer une adresse mail correcte';
        }
        if ($phone === '' || !is_numeric($phone)) {
            $errors[] = 'Veuillez entrer votre numero de telephone';
        }
        if ($ville === '') {
            $errors[] = 'Veuillez entrer votre ville';
        }
        if (!is_numeric($departement)) {
            $errors[] = 'Veuillez choisir un departement';
        }
        if (!is_numeric($categorie)) {
            $errors[] = 'Veuillez choisir une categorie';
        }
        if ($title === '') {
            $errors[] = 'Veuillez entrer un titre';
        }
        if ($description === '') {
            $errors[] = 'Veuillez entrer une description';
        }
        if ($price === '' || !is_numeric($price)) {
            $errors[] = 'Veuillez entrer un prix';
        }

        if ($requirePasswordConfirmation) {
            if ($password === '' || $passwordConfirm === '' || $password !== $passwordConfirm) {
                $errors[] = 'Les mots de passe ne sont pas identiques';
            }
        }

        return $errors;
    }
}
