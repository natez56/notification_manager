<?php

namespace Drupal\custom_notification\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for notification system.
 */
class NotificationForm extends ConfigFormBase
{
    /** @var string Config settings */
    const SETTINGS = 'custom_notification.settings.yml';

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'my_notification_form';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        // magic. it's all magic
        return [
            static::SETTINGS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        // References the SETTINGS const variable which is the file name
        // of the settings file at:
        // {your module}/config/install/{your module}.settings.yml
        $config = $this->config(static::SETTINGS);

        $form['enable'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable Notifications'),
            '#return_value' => true,
            '#default_value' => $config->get('checkbox'),
        ];

        // The date populating '#default_value is stored in the settings.yml
        // file as a string so it needs to be converted to a new
        // DrupalDateTime object.
        $form['start_time'] = [
            '#type' => 'datetime',
            '#title' => $this->t('Start'),
            '#default_value' => new DrupalDateTime($config->get('start')),
        ];

        $form['end_time'] = [
            '#type' => 'datetime',
            '#title' => $this->t('End'),
            '#default_value' => new DrupalDateTime($config->get('end')),
        ];

        // Passes results to the standard buildForm function.
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        // TODO validate start date is not before end date.
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Store form settings. Converts datetime objects to strings because
        // datetime objects can't be stored as configuration values.
        $this->configFactory->getEditable(static::SETTINGS)
            ->set('checkbox', $form_state->getValue('enable'))
            ->set('start', $form_state->getValue('start_time')->__toString())
            ->set('end', $form_state->getValue('end_time')->__toString())

            ->save();

        parent::submitForm($form, $form_state);
    }
}
