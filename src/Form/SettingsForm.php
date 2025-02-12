<?php

namespace Matomo\Form;

use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\Event;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;

class SettingsForm extends Form
{
    use EventManagerAwareTrait;

    public function init()
    {
        $this->setOption('element_groups', [
            'option1' => 'Option 1: URL and Site ID (recommended)', // @translate
            'option2' => 'Option 2: Javascript tracking code', // @translate
            'general' => 'General settings', // @translate
        ]);

        $this->add([
            'name' => 'matomo_js_tracking_code',
            'type' => Textarea::class,
            'options' => [
                'element_group' => 'option2',
                'label' => 'Javascript tracking code', // @translate
                'info' => "Copy here the Javascript code given by Matomo. HTML tags will be automatically stripped.",  // @translate
            ],
            'attributes' => [
                'rows' => 10,
            ],
        ]);

        $this->add([
            'name' => 'matomo_url',
            'type' => Text::class,
            'options' => [
                'element_group' => 'option1',
                'label' => 'Matomo URL', // @translate
            ],
            'attributes' => [
                'placeholder' => 'https://matomo.example.com/',
            ],
        ]);

        $this->add([
            'name' => 'matomo_site_id',
            'type' => Text::class,
            'options' => [
                'element_group' => 'option1',
                'label' => 'Matomo Site ID', // @translate
            ],
        ]);

        $this->add([
            'name' => 'matomo_track_admin',
            'type' => Checkbox::class,
            'options' => [
                'element_group' => 'general',
                'label' => 'Track admin pages visits', // @translate
                'info' => 'By default, the javascript tracking code is not included on admin pages. If this option is enabled, admin pages are tracked as well', // @translate
            ],
        ]);

        $addEvent = new Event('form.add_elements', $this);
        $this->getEventManager()->triggerEvent($addEvent);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'matomo_js_tracking_code',
            'allow_empty' => true,
            'filters' => [
                ['name' => StripTags::class],
            ],
        ]);

        $filterEvent = new Event('form.add_input_filters', $this, ['inputFilter' => $inputFilter]);
        $this->getEventManager()->triggerEvent($filterEvent);
    }
}
