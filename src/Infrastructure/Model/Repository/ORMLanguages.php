<?php

namespace App\Infrastructure\Model\Repository;

use App\Model\Entity\Language;
use App\Model\Exception\LanguageNotFound;
use App\Model\Repository\Languages;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;

final class ORMLanguages implements Languages
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function all(): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('language')
            ->from(Language::class, 'language')
            ->orderBy('language.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getById(string $id): Language
    {
        try {
            return $this->entityManager
                ->createQueryBuilder()
                ->select('language')
                ->from(Language::class, 'language')
                ->where('language.id = :id')
                ->setParameter('id', $id, 'uuid')
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            throw new LanguageNotFound($id);
        }
    }

    public function getByShortcut(string $shortcut): Language
    {
        try {
            return $this->entityManager
                ->createQueryBuilder()
                ->select('language')
                ->from(Language::class, 'language')
                ->where('LOWER(language.shortcut) = LOWER(:shortcut)')
                ->setParameter('shortcut', $shortcut)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            throw new LanguageNotFound($shortcut);
        }
    }
}
