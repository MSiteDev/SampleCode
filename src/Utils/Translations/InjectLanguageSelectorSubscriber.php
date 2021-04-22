<?php

namespace App\Utils\Translations;

use App\Utils\Translations\Model\AbstractTranslatable;
use App\Utils\Translations\Selectors\ComposedLanguageSelector;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

final class InjectLanguageSelectorSubscriber implements EventSubscriber
{
    private ComposedLanguageSelector $selector;

    public function __construct(ComposedLanguageSelector $languageSelector)
    {
        $this->selector = $languageSelector;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
            Events::postPersist,
        ];
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $this->injectLanguageSelector($args->getObject());
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->injectLanguageSelector($args->getObject());
    }

    private function injectLanguageSelector(object $entity): void
    {
        if (!$entity instanceof AbstractTranslatable) {
            return;
        }

        $entity->setLanguageSelector($this->selector);
    }
}
