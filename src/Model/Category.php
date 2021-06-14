<?php
namespace App\Model;

class Category {

    private $id;

    private $slug;

    private $name;

    public function getId(): ?int 
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;

    }

    public function getName(): ?string
    {
        return $this->name;

    }

}