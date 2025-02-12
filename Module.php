<?php

namespace Matomo;

use Omeka\Module\AbstractModule;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\View\Renderer\PhpRenderer;
use Matomo\Form\SettingsForm;

class Module extends AbstractModule
{
    public function getConfigForm(PhpRenderer $renderer)
    {
        $serviceLocator = $this->getServiceLocator();
        $formElementManager = $serviceLocator->get('FormElementManager');
        $settings = $serviceLocator->get('Omeka\Settings');
        $form = $formElementManager->get(SettingsForm::class);

        $data = [];
        foreach ($form as $element) {
            $name = $element->getName();
            if (null !== ($value = $settings->get($name))) {
                $data[$name] = $value;
            }
        }
        $form->setData($data);

        return $renderer->partial('matomo/config-form', ['form' => $form]);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $serviceLocator = $this->getServiceLocator();
        $formElementManager = $serviceLocator->get('FormElementManager');
        $settings = $serviceLocator->get('Omeka\Settings');
        $form = $formElementManager->get(SettingsForm::class);

        $form->setData($controller->params()->fromPost());
        if (!$form->isValid()) {
            $controller->messenger()->addFormErrors($form);
            return false;
        }

        $formData = $form->getData();
        foreach ($form as $element) {
            $name = $element->getName();
            if (str_starts_with($name, 'matomo_') && isset($formData[$name])) {
                $settings->set($name, $formData[$name]);
            }
        }

        return true;
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach('*', 'view.layout', [$this, 'onViewLayout']);
    }

    public function onViewLayout(Event $event)
    {
        $serviceLocator = $this->getServiceLocator();
        $settings = $serviceLocator->get('Omeka\Settings');

        $view = $event->getTarget();

        // If admin tracking is disabled and we are on an admin page, do nothing
        $track_admin = $settings->get('matomo_track_admin', false);
        if (!$track_admin) {
            $isAdmin = $view->params()->fromRoute('__ADMIN__', false);
            if ($isAdmin) {
                return;
            }
        }

        $js_tracking_code = trim($settings->get('matomo_js_tracking_code', ''));
        if (!$js_tracking_code) {
            $js_tracking_code = trim($view->partial('matomo/js-tracking-code'));
        }

        // Do nothing if the module is not configured
        if (!$js_tracking_code) {
            return;
        }

        $js_tracking_code = strip_tags($js_tracking_code);

        $view->headScript()->appendScript($js_tracking_code);
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }
}
