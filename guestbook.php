<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;

class GuestbookPlugin extends Plugin
{
    public $features = [
        'blueprints' => 1000,
    ];

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized'                       => ['onPluginsInitialized', 0],
            'onGetPageTemplates'                         => ['onGetPageTemplates', 0]
        ];
    }

    /**
     */
    public function onPluginsInitialized()
    {
        $this->enable([
            'onPageInitialized'   => ['onPageInitialized', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0]
        ]);
    }

    /**
     * Add page template types.
     */
    public function onGetPageTemplates(Event $event)
    {
        /** @var Types $types */
        $types = $event->types;
        $types->scanTemplates('plugins://guestbook/templates');
    }

    /**
     * Initialize configuration
     */
    public function onPageInitialized()
    {
        /** @var Page $page */
        $page = $this->grav['page'];
    }

    /**
     * Add templates directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }
}
