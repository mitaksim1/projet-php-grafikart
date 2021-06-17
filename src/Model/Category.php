<?php
namespace App\Model;

class Category {

    private $id;

    private $slug;

    private $name;

    private $post_id;

    private $post;

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

    public function getPostId(): ?int
    {
        return $this->post_id;
    }

    public function setPost(Post $post) 
    {
        return $this->post = $post;
    }
}