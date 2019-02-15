<?php

namespace Drupal\custom_notification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

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

        $query->condition('status', [0, 1], 'IN');
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
            'options' => ['data' => t('Options'), 'colspan' => '4'],
        ];

        // Drop down menu for actions that apply to table rows that have been
        // selected using the provided checkboxes.
        $form['action'] = [
            '#type' => 'select',
            '#title' => $this
                ->t('Action'),
            '#options' => [
                '1' => $this
                    ->t('View'),
                '2' => $this
                    ->t('Publish '),
                '3' => $this
                    ->t('Unpublish'),
                '4' => $this
                    ->t('Delete'),
            ],
        ];

        // Build the Drupal Tableselect options array.
        $options = array();
        foreach ($entities as $key => $entity) {
            // Create view, delete, publish, unpublish links.
            $viewLink = Link::createFromRoute(
                'View',
                'entity.node.canonical',
                ['node' => $key]
            );

            $deleteLink = Link::createFromRoute(
                'Delete',
                'entity.node.delete_form',
                ['node' => $key]
            );

            $publishLink = Link::fromTextAndUrl(
                'Publish',
                Url::fromRoute(
                    'custom_notification.publish',
                    ['node' => $key]
                )
            );

            $unpublishLink = Link::fromTextAndUrl(
                'Unpublish',
                Url::fromRoute(
                    'custom_notification.unpublish',
                    ['node' => $key]
                )
            );

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
                    'options' => [$viewLink, $deleteLink, $publishLink,
                        $unpublishLink],
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

        // Submit button to submit actions that apply to selected table rows.
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Selected using checkboxes in table.
        $selectedNodeIds = $form_state->getValue('table');

        // Dropdown menu selection.
        $action = $form_state->getValue('action');

        switch ($action) {
            case 1:
                // Construct url string to trigger contextual filter for
                // notification view.
                $urlString = 'base:/selected-notifications/';

                $isFirst = true;
                foreach ($selectedNodeIds as $key => $nid) {
                    if ($isFirst) {
                        $urlString = $urlString . $nid;
                        $isFirst = false;
                    } else {
                        $urlString = $urlString . ' ' . $nid;
                    }
                }

                $url = \Drupal\Core\Url::fromUri($urlString,
                    ['absolute' => true]);

                return $form_state->setRedirectUrl($url);

            case 2:
                // Set published for all nodes selected using checkboxes.
                foreach ($selectedNodeIds as $nid) {
                    $node = \Drupal\node\Entity\Node::load($nid);
                    $node->setPublished(true);
                    $node->save();
                }
                break;

            case 3:
                // Set unpublished for all notifications selected using
                // checkboxes.
                foreach ($selectedNodeIds as $nid) {
                    $node = \Drupal\node\Entity\Node::load($nid);
                    $node->setPublished(false);
                    $node->save();
                }
                break;

            case 4:
                // Delete notifications selected using checkboxes.
                foreach ($selectedNodeIds as $nid) {
                    $entity = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
                    $entity->delete();
                }
                break;

            default:
                break;
        }

    }

}
