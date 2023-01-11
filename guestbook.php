<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\File\File;
use RocketTheme\Toolbox\Event\Event;
use Symfony\Component\Yaml\Yaml;

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
            'onGetPageTemplates'                         => ['onGetPageTemplates', 0],
            'onFormProcessed'                            => ['onFormProcessed', 10]
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
     * Initialize configuration
     */
    public function onPageInitialized()
    {
        /** @var Page $page */
        $page = $this->grav['page'];

        if ($page->template() == 'guestbook') {
            // Call this here to get the messages on the page load
            $this->fetchMessages();
        }
    }

    /**
     * Add templates directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
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
     * Handle form processing instructions.
     *
     * @param Event $event
     */
    public function onFormProcessed(Event $event)
    {
        if (!$this->active) {
            return;
        }

        /** @var Form $form */
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        switch ($action) {
            case 'jsonAddGuestbookEntry':
                $operation = $params['operation'] ?? 'create';

                if ($operation === 'add') {
                    /** @var Flex */
                    $flex = $this->grav['flex'];
                    /** @var FlexDirectory */
                    $dir = $flex->getDirectory('guestbook');

                    /** @var FlexObjectInterface */
                    $object = $dir->createObject([
                        'author' => $form->data['author'],
                        'text' => $form->data['message'],
                        'email' => $form->data['email'],
                        'date' => $form->data['date'],
                        'uuid' => $this->gen_uuid(),
                        'moderated' => 0,
                    ]);
                    $object->save();
                }
                break;
        }

        //Call this here to get the messages updated after the form is processed
        $this->fetchMessages();
    }

    /**
     * Fetch the page messages.
     */
    public function fetchMessages()
    {
        $page = $this->grav['uri']->param('page');
        $messages = $this->getMessages($page);
        if (!isset($messages->messages)){
            if ($page > 0) {
                echo json_encode($messages);
                exit();
            }

            $this->grav['twig']->guestbookMessages = $messages;

        } else {
            $moderated = [];
            foreach ($messages->messages as $value) {
                if ($this->isModerated($value)) {
                    $moderated[] = $value;
                }
            }
            $messages->messages = $moderated;
            if ($page > 0) {
                echo json_encode($messages);
                exit();
            }

            $this->grav['twig']->guestbookMessages = $messages;
        }
    }

    public function isModerated($message)
    {
        if (!$this->grav['config']->get('plugins.guestbook.moderation')) {
            $message['moderated'] = 1;
            return true;
        }

        if (!isset($message['moderated'])) {
            $message['moderated'] = 0;

            return $this->isModerated($message);
        } elseif ($message['moderated'] == 0) {
            return false;
        } elseif ($message['moderated'] == 1) {
            return true;
        }

        return false;
    }

    //Source: http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
    private function gen_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

    private function approveAll()
    {
        $filename = DATA_DIR . 'guestbook/' . $this->grav['config']->get('plugins.guestbook.filename');
        $file = File::instance($filename);

        if (!$file->content()) {
            // Item not found
            return;
        }

        $messages = Yaml::parse($file->content());
        $checked = [];
        $updated = false;
        foreach ($messages as $value) {
            if (!isset($value['moderated'])) {
                $value['moderated'] = 1;
                if (!$updated) {
                    $updated = true;
                }
            } elseif ($value['moderated'] == 0) {
                $value['moderated'] = 1;
                if (!$updated) {
                    $updated = true;
                }
            }
            $checked[] = $value;
        }
        if ($updated) {
            $messages = $checked;
            $yaml = Yaml::dump($messages);
            file_put_contents($filename, $yaml);
        }
    }

    private function getMessages($page = 0)
    {
        $itemsPerPage = 5;

        $lang = $this->grav['language']->getActive();
        $filename = DATA_DIR . 'guestbook/' . $this->grav['config']->get('plugins.guestbook.filename');
        $file = File::instance($filename);

        if (!$file->content()) {
            //Item not found
            return;
        }

        $messages = Yaml::parse($file->content());
        $checked = [];
        $updated = false;
        $legacy_check = 0;
        foreach ($messages as $value) {
            if (!isset($value['uuid'])) {
                $value['uuid'] = $this->gen_uuid();
                if (!$updated) {
                    $updated = true;
                }
            }
            if (!isset($value['moderated'])) {
                $legacy_check = $legacy_check + 1;
            }
            $checked[] = $value;
        }
        if ($updated) {
            $messages = $checked;
            $yaml = Yaml::dump($messages);
            file_put_contents($filename, $yaml);
        }
        if ($legacy_check == count($messages) && $legacy_check != 0) {
            $this->approveAll();

            return $this->getMessages($page);
        }
        $c = count($messages);
        $page_count = round($c / $itemsPerPage);
        $totalAvailable = count($messages);
        if ($page != "all") {
            $messages = array_slice($messages, $page * $itemsPerPage, $itemsPerPage);
        }
        $totalRetrieved = count($messages);

        return (object)[
            "messages"       => $messages,
            "page"           => intval($page) + 1,
            "itemsPerPage"   => $itemsPerPage,
            "totalAvailable" => $totalAvailable,
            "totalRetrieved" => $totalRetrieved,
            "totalPages"     => $page_count
        ];
    }
}
