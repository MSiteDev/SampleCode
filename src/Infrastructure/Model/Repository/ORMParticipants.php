<?php

namespace App\Infrastructure\Model\Repository;

use App\Model\Entity\Participant;
use App\Model\EventExtractor\ParticipantEventExtractor;
use App\Model\Exception\ParticipantNotFound;
use App\Model\Repository\Participants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class ORMParticipants extends ServiceEntityRepository implements Participants
{
    private ParticipantEventExtractor $eventExtractor;
    private MessageBusInterface $eventBus;

    public function __construct(
        ManagerRegistry $registry,
        ParticipantEventExtractor $eventExtractor,
        MessageBusInterface $eventBus
    ) {
        parent::__construct($registry, Participant::class);
        $this->eventExtractor = $eventExtractor;
        $this->eventBus = $eventBus;
    }

    public function save(Participant $participant): void
    {
        $this->_em->persist($participant);
        $this->_em->flush();

        foreach ($this->eventExtractor->extractAndClear($participant) as $event) {
            $this->eventBus->dispatch($event);
        }
    }

    public function all(): array
    {
        return $this->_em
            ->createQueryBuilder()
            ->select('p')
            ->from(Participant::class, 'p')
            ->getQuery()
            ->getResult();
    }

    public function getById(string $id): Participant
    {
        try {
            return $this->_em
                ->createQueryBuilder()
                ->select('p')
                ->from(Participant::class, 'p')
                ->where('p.id = :participantId')
                ->setParameter('participantId', $id, 'uuid')
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            throw new ParticipantNotFound($id);
        }
    }

    public function findByRegistrationToken(string $registrationToken): ?Participant
    {
        return $this->_em
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->innerJoin(
                'participant.registrationTokens',
                'registration_tokens',
                'WITH',
                'registration_tokens.id = :token'
            )
            ->setParameter('token', $registrationToken)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getByEmailAddress(string $email): Participant
    {
        try {
            return $this->_em
                ->createQueryBuilder()
                ->select('p')
                ->from(Participant::class, 'p')
                ->where('p.email = :email')
                ->setParameter('email', $email)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            throw new ParticipantNotFound($email);
        }
    }

    public function remove(Participant $participant): void
    {
        $this->_em->remove($participant);
        $this->_em->flush();
    }

    public function loadUserByUsername(string $email): ?Participant
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb
            ->select('p')
            ->from(Participant::class, 'p')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('LOWER(p.email)', 'LOWER(:email)'),
                    $qb->expr()->isNotNull('p.activatedAt')
                )
            )
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByResetToken(string $token): ?Participant
    {
        return $this->_em->createQueryBuilder()
            ->select('user')
            ->from(Participant::class, 'user')
            ->where('user.resetToken = :token')
            ->andWhere('user.resetToken <> \'\'')
            ->andWhere('user.resetToken IS NOT NULL')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass(string $class)
    {
        return $class === Participant::class;
    }
}
