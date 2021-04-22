<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(name="languages")
 */
class Language
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private UuidInterface $id;
    /**
     * @ORM\Column(type="string")
     */
    private string $name;
    /**
     * @ORM\Column(type="string")
     * TODO: Rename this property to locale
     */
    private string $shortcut;

    private function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public static function new(string $name, string $shortcut): self
    {
        $self = new self();
        $self->name = $name;
        $self->shortcut = $shortcut;

        return $self;
    }

    /**
     * @Groups({"language"})
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @Groups({"language"})
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @Groups({"language"})
     */
    public function getLocale(): string
    {
        return strtolower($this->getShortcut());
    }

    // TODO: Remove this method
    public function getShortcut(): string
    {
        return $this->shortcut;
    }

    public function equals(Language $language): bool
    {
        return $this->getId()->equals($language->getId());
    }
}
