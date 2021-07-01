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

    private function getValue(string $key): string
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
    
