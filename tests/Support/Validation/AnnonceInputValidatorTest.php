<?php

declare(strict_types=1);

namespace Tests\Support\Validation;

use App\Support\Validation\AnnonceInputValidator;
use PHPUnit\Framework\TestCase;

final class AnnonceInputValidatorTest extends TestCase
{
    public function testValidateForCreateReturnsErrorsWhenInputInvalid(): void
    {
        $validator = new AnnonceInputValidator();

        $errors = $validator->validateForCreate([
            'nom' => '',
            'email' => 'bad-email',
            'phone' => 'abc',
            'ville' => '',
            'departement' => 'x',
            'categorie' => 'x',
            'title' => '',
            'description' => '',
            'price' => 'abc',
            'psw' => 'a',
            'confirm-psw' => 'b',
        ]);

        self::assertNotEmpty($errors);
        self::assertContains('Veuillez entrer votre nom', $errors);
        self::assertContains('Veuillez entrer une adresse mail correcte', $errors);
        self::assertContains('Les mots de passe ne sont pas identiques', $errors);
    }

    public function testValidateForCreateAcceptsValidPayload(): void
    {
        $validator = new AnnonceInputValidator();

        $errors = $validator->validateForCreate([
            'nom' => 'Alice',
            'email' => 'alice@example.org',
            'phone' => '0612345678',
            'ville' => '75001',
            'departement' => '75',
            'categorie' => '1',
            'title' => 'Velo de route',
            'description' => 'Tres bon etat',
            'price' => '120',
            'psw' => 'secret',
            'confirm-psw' => 'secret',
        ]);

        self::assertSame([], $errors);
    }

    public function testValidateForUpdateDoesNotRequirePasswordConfirmation(): void
    {
        $validator = new AnnonceInputValidator();

        $errors = $validator->validateForUpdate([
            'nom' => 'Bob',
            'email' => 'bob@example.org',
            'phone' => '0610101010',
            'ville' => 'Lyon',
            'departement' => '69',
            'categorie' => '2',
            'title' => 'Canape',
            'description' => 'A retirer sur place',
            'price' => '300',
        ]);

        self::assertSame([], $errors);
    }
}
