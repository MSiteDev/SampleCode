<?php

namespace App\Framework\DependencyInjection\Compiler\EventListeners;

use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EventListenersCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds(EventListenerInterface::class);

        foreach (array_keys($taggedServices) as $eventListener) {
            $definition = $container->getDefinition($eventListener);

            /** @var class-string $class */
            $class = $definition->getClass();

            foreach ((new ReflectionClass($class))->getMethods() as $method) {
                foreach ($method->getAttributes() as $attribute) {
                    if (!is_a($attribute->getName(), EventListenerTag::class, true)) {
                        continue;
                    }

                    /** @var EventListenerTag $attributeInstance */
                    $attributeInstance = $attribute->newInstance();

                    $definition->addTag(
                        'kernel.event_listener',
                        [
                            'method' => $method->getName(),
                            'event' => $attributeInstance->getEvent(),
                            'priority' => $attributeInstance->getPriority(),
                        ]
                    );
                }
            }
        }
    }
}
