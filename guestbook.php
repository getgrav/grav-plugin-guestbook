<?php
namespace Grav\Plugin;

use Grav\Common\GPM\GPM;
use Grav\Common\Grav;
use Grav\Common\Page\Page;
use Grav\Common\Page\Pages;
use Grav\Common\Plugin;
use Grav\Common\Filesystem\RecursiveFolderFilterIterator;
use Grav\Common\User\User;
use RocketTheme\Toolbox\File\File;
use RocketTheme\Toolbox\Event\Event;
use Symfony\Component\Yaml\Yaml;

class GuestbookPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onFormProcessed' => ['onFormProcessed', 10],
            'onDataTypeExcludeFromDataManagerPluginHook' => ['onDataTypeExcludeFromDataManagerPluginHook', 0],

        ];
    }

    /**
     */
    public function onPluginsInitialized()
    {
        if (!$this->isAdmin()) {
            $this->enable([
                'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            ]);
        }
    }


    /**
     * Handle form processing instructions.
     *
     * @param Event $event
     */
    public function onFormProcessed(Event $event)
    {
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        if (!$this->active) {
            return;
        }

        $page = $this->grav['uri']->param('page');
        $messages = $this->getMessages($page);

        if ($page > 0) {
            echo json_encode($messages);
            exit();
        }

        $this->grav['twig']->guestbookMessages = $messages;
    }

    private function getMessages($page = 0) {
        $itemsPerPage = 5;

        $lang = $this->grav['language']->getActive();
        $filename = DATA_DIR . 'guestbook/' . $this->grav['config']->get('plugins.guestbook.filename');
        $file = File::instance($filename);

        if (!$file->content()) {
            //Item not found
            return;
        }

        $messages = Yaml::parse($file->content());

        $totalAvailable = count($messages);
        $messages = array_slice($messages, $page * $itemsPerPage, $itemsPerPage);
        $totalRetrieved = count($messages);

        return (object)array(
            "messages" => $messages,
            "page" => $page,
            "itemsPerPage" => $itemsPerPage,
            "totalAvailable" => $totalAvailable,
            "totalRetrieved" => $totalRetrieved
        );
    }

    /**
     * Add templates directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Exclude comments from the Data Manager plugin
     */
    public function onDataTypeExcludeFromDataManagerPluginHook()
    {
        $this->grav['admin']->dataTypesExcludedFromDataManagerPlugin[] = 'guestbook';
    }
}
