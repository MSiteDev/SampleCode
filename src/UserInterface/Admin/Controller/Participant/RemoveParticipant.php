<?php

namespace App\UserInterface\Admin\Controller\Participant;

use App\Application\Command\RemoveParticipantCommand;
use App\Model\Entity\Participant;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RemoveParticipant
{
    private UrlGeneratorInterface $urlGenerator;
    private MessageBusInterface $commandBus;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        MessageBusInterface $commandBus
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->commandBus = $commandBus;
    }

    public function __invoke(Participant $participant): Response
    {
        $this->commandBus->dispatch(new RemoveParticipantCommand($participant));

        return new RedirectResponse($this->urlGenerator->generate('getParticipants'));
    }
}
