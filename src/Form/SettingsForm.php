<?php

namespace Matomo\Form;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;

class SettingsForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'js_tracking_code',
            'type' => TextArea::class,
            'options' => [
                'label' => 'Javascript tracking code', // @translate
                'info' => "Copy here the Javascript code given by Matomo. HTML tags will be automatically stripped.",  // @translate
            ],
            'attributes' => [
                'rows' => 12,
            ],
        ]);

        $this->add([
            'name' => 'track_admin',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Track admin pages visits', // @translate
                'info' => 'By default, the javascript tracking code is not included on admin pages. If this option is enabled, admin pages are tracked as well', // @translate
            ],
        ]);
    }
}
