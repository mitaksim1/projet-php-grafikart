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

    // Récupère le slug
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    // Récupère l'id
    public function getId(): ?int
    {
        return $this->id;
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