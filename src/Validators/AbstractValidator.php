<?php
namespace App\Validators;

use App\Validator;

abstract class AbstractValidator {

    protected $data;
    protected $validator;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->validator = new Validator($data);
       
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