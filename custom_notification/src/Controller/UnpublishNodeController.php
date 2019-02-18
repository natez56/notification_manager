<?php

namespace Drupal\custom_notification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Node;

/**
 * Defines HelloController class.
 */
class UnpublishNodeController extends ControllerBase
{

    /**
     * Sets node parameter passed in route to published.
     * Redirects back to notification manager when complete.
     */
    public function content()
    {
        $nid = \Drupal::routeMatch()->getParameters();
        $nid = $nid->get('node');

        // Get node passed in route and set to published.
        $node = \Drupal\node\Entity\Node::load($nid);
        $node->setPublished(false);
        $node->save();

        // Route to notification manager.
        return $this->redirect('custom_notification.manager');
    }

}
