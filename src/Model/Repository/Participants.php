<?php

namespace App\Model\Repository;

use App\Model\Entity\Participant;
use App\Model\Exception\ParticipantNotFound;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface Participants extends UserLoaderInterface, UserProviderInterface
{
    public function save(Participant $participant): void;

    /**
     * @return Participant[]
     */
    public function all(): array;

    /**
     * @throws ParticipantNotFound
     */
    public function getById(string $id): Participant;

    /** @throws ParticipantNotFound */
    public function findByRegistrationToken(string $registrationToken): ?Participant;

    /** @throws ParticipantNotFound */
    public function getByEmailAddress(string $email): Participant;

    public function remove(Participant $participant): void;

    public function findByResetToken(string $token): ?Participant;

    public function loadUserByUsername(string $username): ?Participant;
}
