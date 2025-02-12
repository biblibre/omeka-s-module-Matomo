<?php
namespace Matomo\Service\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Matomo\Form\SettingsForm;

class SettingsFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $eventManager = $services->get('EventManager');

        $form = new SettingsForm(null, $options ?? []);
        $form->setEventManager($eventManager);

        return $form;
    }
}
