<?php

namespace App\Entity;

use App\Repository\SereneResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SereneResultRepository::class)]
class SereneResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?array $sentence = null;

    #[ORM\Column(nullable: true)]
    private ?array $ia_answer = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?UserForm $id_user_form = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSentence(): ?array
    {
        return $this->sentence;
    }

    public function setSentence(?array $sentence): static
    {
        $this->sentence = $sentence;

        return $this;
    }

    public function getIaAnswer(): ?array
    {
        return $this->ia_answer;
    }

    public function setIaAnswer(?array $ia_answer): static
    {
        $this->ia_answer = $ia_answer;

        return $this;
    }

    public function getIdUserForm(): ?UserForm
    {
        return $this->id_user_form;
    }

    public function setIdUserForm(?UserForm $id_user_form): static
    {
        $this->id_user_form = $id_user_form;

        return $this;
    }
}
