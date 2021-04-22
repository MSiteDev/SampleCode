<?php

namespace App\Application\CommandHandler;

use App\Application\Command\RemoveParticipantCommand;
use App\Model\Exception\ParticipantNotFound;
use App\Model\Repository\ActivityLogs;
use App\Model\Repository\ChallengeFeedbackRepositoryInterface;
use App\Model\Repository\LiveSessions;
use App\Model\Repository\Participants;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final class RemoveParticipantCommandHandler implements CommandHandlerInterface
{
    private Participants $participants;
    private LiveSessions $liveSessions;
    private ChallengeFeedbackRepositoryInterface $feedbackRepository;
    private ActivityLogs $activityLogs;

    public function __construct(
        Participants $participants,
        LiveSessions $liveSessions,
        ChallengeFeedbackRepositoryInterface $feedbackRepository,
        ActivityLogs $activityLogs
    ) {
        $this->participants = $participants;
        $this->liveSessions = $liveSessions;
        $this->feedbackRepository = $feedbackRepository;
        $this->activityLogs = $activityLogs;
    }

    public function __invoke(RemoveParticipantCommand $command): void
    {
        try {
            $participant = $this->participants->getById($command->getParticipantId());

            foreach ($participant->getLiveSessions() as $liveSessionParticipant) {
                $liveSession = $liveSessionParticipant->getLiveSession();

                $liveSessionParticipant->isWaiting()
                    ? $liveSession->removeParticipantFromWaitingList($participant)
                    : $liveSession->removeParticipant($participant);

                $this->liveSessions->save($liveSession);
            }

            $this->feedbackRepository->removeFeedbacksFromParticipant($participant);
            $this->activityLogs->removeFromParticipant($participant);

            $this->participants->remove($participant);
        } catch (ParticipantNotFound) {
            throw new UnrecoverableMessageHandlingException();
        }
    }
}
