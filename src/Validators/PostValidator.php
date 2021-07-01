<?php
namespace App\Validators;

use App\Validator;

class PostValidator {

    private $data;
    private $validator;

    public function __construct(array $data)
    {
        $this->data = $data;

        $validator = new Validator($data);
        // Valide l'existence du titre
        $validator->rule('required', ['name', 'slug']);
        // valide la longueur du titre
        $validator->rule('lengthBetween', ['name', 'slug'], 10, 200);
        $this->validator = $validator;
    }

    // Valide si les données sont valides
    public function validate(): bool
    {
        return $this->validator->validate();
    }

    // Récupère les erreurs
    public function errors(): array
    {
        return $this->validator->errors();
    }
}