<?php
namespace App\Validators;

use App\Table\PostTable;
use App\Validator;

class PostValidator {

    private $data;
    private $validator;

    public function __construct(array $data, PostTable $table)
    {
        $this->data = $data;

        $validator = new Validator($data);
        // Valide l'existence du titre
        $validator->rule('required', ['name', 'slug']);
        // valide la longueur du titre
        $validator->rule('lengthBetween', ['name', 'slug'], 10, 200);
        // Valide le champs slug
        $validator->rule('slug', 'slug');
        // On crée notre propre validator
        $validator->rule(function ($field, $value) use ($table) {
            return !$table->exists($field, $value);
        }, 'slug', 'Ce slug est déjà utilisé'); 
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