<?php

namespace Drupal\custom_notification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Node;
use Drupal\Core\Url;

/**
 * Defines HelloController class.
 */
class PublishNodeController extends ControllerBase
{

    /**
     * Sets node parameter passed in route to published.
     * Redirects back to notification manager when complete.
     */
    public function content()
    {
        $nid = \Drupal::routeMatch()->getParameters('node');

        // Get node passed in route and set to published.
        $node = \Drupal\node\Entity\Node::load($nid);
        $node->setPublished(true);
        $node->save();

        // Route to notification manager.
        $url = Url::fromRoute('custom_notification.manager');

        return $this->redirect($url->getRouteName());
    }

}
