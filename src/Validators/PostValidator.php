<?php
namespace App\Validators;

use App\Table\PostTable;

class PostValidator extends AbstractValidator {

    public function __construct(array $data, PostTable $table, ?int $postId = null)
    {
        parent::__construct($data);
        // Valide l'existence du titre
        $this->validator->rule('required', ['name', 'slug']);
        // valide la longueur du titre
        $this->validator->rule('lengthBetween', ['name', 'slug'], 10, 200);
        // Valide le champs slug
        $this->validator->rule('slug', 'slug');
        // On crée notre propre validator
        $this->validator->rule(function ($field, $value) use ($table, $postId) {
            return !$table->exists($field, $value, $postId);
        }, ['slug', 'name'], 'Cette valeur est déjà utilisé'); 
    }
}