<?php

namespace Drupal\custom_notification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Constructs a table to manage notifications.
 */
class NotificationsManager extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'notifications_manager_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        // Content type created ahead of time in Drupal site builder.
        $entityType = 'notification';
        // Content status. True means this query will grab published content.
        $isPublished = true;

        $query = \Drupal::entityQuery('node');

        $query->condition('status', $isPublished);
        $query->condition('type', $entityType);

        $entityIds = $query->execute();

        // Gets an array of notification objects.
        $entities = (
            \Drupal::entityTypeManager()->getStorage('node')
                ->loadMultiple($entityIds)
        );

        $header = [
            'title' => $this
                ->t('Title'),
            'created' => $this
                ->t('Created Date'),
            'options' => $this
                ->t('Options'),
        ];

        // Build the Drupal Tableselect options array.
        $options = array();
        foreach ($entities as $key => $entity) {
            // The string 'created' is a key in the entity attribute array.
            // notification[created] = the notification created date.
            // It must be accessed with a get method because the entity's
            // values are protected variables.
            $createdDate = $entity->get('created')->value;

            // Modify the 'M D Y h i' string to change date format.
            // For instance 'M D' will display month and day.
            $formattedDate = (
                \Drupal::service('date.formatter')
                    ->format($createdDate, 'custom', 'M D Y h i')
            );

            // Add key value pair to options array. The $key variable is
            // the node id of the notification touched on this iteration.
            $options += array(
                $key => [
                    'title' => $entity->get('title')->value,
                    'created' => $formattedDate,
                    'options' => 'view, delete, publish, unpublish',
                ],
            );
        }

        $form['table'] = array(
            '#type' => 'tableselect',
            '#header' => $header,
            '#options' => $options,
            '#empty' => $this
                ->t('No users found'),
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        drupal_set_message("ok");
    }

}
