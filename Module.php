<?php

namespace Matomo;

use Omeka\Module\AbstractModule;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\View\Renderer\PhpRenderer;

class Module extends AbstractModule
{
    public function getConfigForm(PhpRenderer $renderer)
    {
        $serviceLocator = $this->getServiceLocator();
        $formElementManager = $serviceLocator->get('FormElementManager');
        $settings = $serviceLocator->get('Omeka\Settings');
        $form = $formElementManager->get(Form\SettingsForm::class);

        $form->setData([
            'js_tracking_code' => $settings->get('matomo_js_tracking_code'),
            'track_admin' => $settings->get('matomo_track_admin'),
        ]);

        return $renderer->formCollection($form, false);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $serviceLocator = $this->getServiceLocator();
        $formElementManager = $serviceLocator->get('FormElementManager');
        $settings = $serviceLocator->get('Omeka\Settings');
        $form = $formElementManager->get(Form\SettingsForm::class);

        $form->setData($controller->params()->fromPost());
        if (!$form->isValid()) {
            $controller->messenger()->addFormErrors($form);
            return false;
        }

        $formData = $form->getData();
        $js_tracking_code = trim(strip_tags($formData['js_tracking_code']));
        $settings->set('matomo_js_tracking_code', $js_tracking_code);
        $settings->set('matomo_track_admin', $formData['track_admin']);

        return true;
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach('*', 'view.layout', [$this, 'onViewLayout']);
    }

    public function onViewLayout(Event $event)
    {
        $serviceLocator = $this->getServiceLocator();
        $formElementManager = $serviceLocator->get('FormElementManager');
        $settings = $serviceLocator->get('Omeka\Settings');

        // Do nothing if the module is not configured
        $js_tracking_code = trim($settings->get('matomo_js_tracking_code', ''));
        if (!$js_tracking_code) {
            return;
        }

        $view = $event->getTarget();

        // If admin tracking is disabled and we are on an admin page, do nothing
        $track_admin = $settings->get('matomo_track_admin', false);
        if (!$track_admin) {
            $isAdmin = $view->params()->fromRoute('__ADMIN__', false);
            if ($isAdmin) {
                return;
            }
        }

        $view->headScript()->appendScript($js_tracking_code);
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }
}
