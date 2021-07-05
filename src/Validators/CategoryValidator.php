<?php
namespace App\Validators;

use App\Table\CategoryTable;

class CategoryValidator extends AbstractValidator {

    public function __construct(array $data, CategoryTable $table, ?int $id = null)
    {
        parent::__construct($data);
        // Valide l'existence du titre
        $this->validator->rule('required', ['name', 'slug']);
        // valide la longueur du titre
        $this->validator->rule('lengthBetween', ['name', 'slug'], 10, 200);
        // Valide le champs slug
        $this->validator->rule('slug', 'slug');
        // On crée notre propre validator
        $this->validator->rule(function ($field, $value) use ($table, $id) {
            return !$table->exists($field, $value, $id);
        }, ['slug', 'name'], 'Cette valeur est déjà utilisé'); 
    }
}