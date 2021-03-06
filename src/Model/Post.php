<?php
namespace App\Model;

use App\Helpers\Text;
use DateTime;

class Post {

    private $id;

    private $slug;

    private $name;

    private $content;

    private $created_at;

    private $categories = [];

    // Récupère la propriété name
    public function getName(): ?string
    {
        return $this->name;
    }

    // Permet de modifier le name
    public function setName(string $name): ?self
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): ?self
    {
        $this->content = $content;

        return $this;
    }

    public function getFormattedContent(): ?string {
        return nl2br(e($this->content));
    } 

    // Retourne l'extrait du contenu
    public function getExcerpt(): ?string
    {
        if ($this->content === null) {
            return null;
        }
        return nl2br(htmlentities(Text::excerpt($this->content, 60)));
    }

    // Converti la date reçue en DateTime
    public function getCreatedAt(): DateTime
    {
        return new DateTime($this->created_at);
    }

    public function setCreatedAt(string $date): self
    {
        $this->created_at = $date;

        return $this;
    }

    // Récupère le slug
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    // Récupère l'id
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function addCategory(Category $category): void
    {
        $this->categories[] = $category;
        // Pour cette catégorie on va appeler la méthode setPost() et sauvegarder l'article associé
        $category->setPost($this);
    }
}