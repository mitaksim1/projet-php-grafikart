<?php
namespace App\HTML;

use DateTime;

class Form {

    private $data;
    private $errors;

    public function __construct($data, array $errors)
    {
        $this->data = $data;
        $this->errors = $errors;
    }

    // Retourne le code HTML de l'input du formulaire
    public function input(string $key, string $label): string
    {
        $value = $this->getValue($key);
       
        return <<<HTML
            <div class="form-group">
                <label for="field{$key}">{$label}</label>
                <input type="text" id="field{$key}" class="{$this->getInputClass($key)}" name="{$key}" value="{$value}" required>
                {$this->getErrorFeedback($key)}
            </div>
HTML;
    }

    // Retourne le code HTML pour le textarea du formulaire
    public function textarea(string $key, string $label): string
    {
        $value = $this->getValue($key);
          
        return <<<HTML
            <div class="form-group">
                <label for="field{$key}">{$label}</label>
                <textarea type="text" id="field{$key}" class="{$this->getInputClass($key)}" name="{$key}" required>{$value}"</textarea>
                {$this->getErrorFeedback($key)}
            </div>
HTML;
    }

    // Prends la avleur de l'input du formulaire
    // Peut être null, parce qu'au moment de la création il sera vide
    private function getValue(string $key): ?string
    {
        // Si donnée passée en paramètre est un tableau
        if (is_array($this->data)) {
            // retourner la clé de ce tableau
            return $this->data[$key] ?? null;
        }
        // Dans le cas contraire, faire ce que suit 
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        // dd($method);
        $value = $this->data->$method();
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        return $value;
    }

    // Ajoute la classe 'is-invalid' pour afficher message d'erreur Boostrap
    private function getInputClass(string $key): string
    {
        $inputClass = 'form-control';
        if (isset($this->errors[$key])) {
            $inputClass .= ' is-invalid';
        }
        return $inputClass;
    }

    private function getErrorFeedback(string $key): string
    {
        if (isset($this->errors[$key])) {
            return '<div class="invalid-feedback">' . implode('<br>', $this->errors[$key]) . '</div>';
        }
        return '';
    } 
}
    
