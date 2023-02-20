<?php

namespace RCore\Objects;

class User
{
    private int $id;
    private string $name;
    private string $email;
    private string $pictureURL;

    public function __construct(int $id, string $name, string $email, string $pictureURL)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->pictureURL = $pictureURL;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPictureURL(): string
    {
        return $this->pictureURL;
    }
}