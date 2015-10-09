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
    protected $route = 'guestbook';

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
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

            $this->addGuestbookEntryURL = $this->config->get('plugins.guestbook.addGuestbookEntryURL', '/add-guestbook-entry');

            if ($this->addGuestbookEntryURL && $this->addGuestbookEntryURL == $this->grav['uri']->path()) {
                $this->enable([
                    'onPagesInitialized' => ['addGuestbookEntry', 0]
                ]);
            } else {
                $this->grav['twig']->guestbookMessages = $this->getMessages();
            }
        }
    }

    public function addGuestbookEntry()
    {
        $post = !empty($_POST) ? $_POST : [];

        $lang = filter_var(urldecode($post['lang']), FILTER_SANITIZE_STRING);
        $text = filter_var(urldecode($post['text']), FILTER_SANITIZE_STRING);
        $name = filter_var(urldecode($post['name']), FILTER_SANITIZE_STRING);
        $email = filter_var(urldecode($post['email']), FILTER_SANITIZE_STRING);

        if ($this->config->get('plugins.guestbook.use_captcha')) {
            //Validate the captcha
            $recaptchaResponse = filter_var(urldecode($post['recaptchaResponse']), FILTER_SANITIZE_STRING);

            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=';
            $url .= $this->config->get('plugins.guestbook.recatpcha_secret');
            $url .= '&response=' . $recaptchaResponse;
            $response = json_decode(file_get_contents($url), true);

            if ($response['success'] == false) {
                throw new \RuntimeException('Error validating the Captcha');
            }
        }

        $lang = $this->grav['language']->getActive();
        $filename = DATA_DIR . 'guestbook/' . ($lang ? '/' . $lang : '') . 'messages.yaml';
        $file = File::instance($filename);

        $message = [
            'text' => $text,
            'date' => gmdate('D, d M Y H:i:s', time()),
            'author' => $name,
            'email' => $email
        ];

        if (file_exists($filename)) {
            $data = Yaml::parse($file->content());
            if (count($data) > 0) {
                array_unshift($data, $message);
            } else {
                $data[] = $message;
            }
        } else {
            $data[] = $message;
        }

        $file->save(Yaml::dump($data));

        if (isset($this->grav['Email']) && $this->grav['config']->get('plugins.guestbook.enable_email_notifications')) {
            $this->sendEmailNotification(array(
                'message' => array(
                    'text' => $text,
                    'date' => gmdate('D, d M Y H:i:s', time()),
                    'author' => $name,
                    'email' => $email
                )
            ));
        }

        exit();
    }

    private function sendEmailNotification($entry) {
        /** @var Language $l */
        $l = $this->grav['language'];

        $sitename = $this->grav['config']->get('site.title', 'Website');
        $from = $this->grav['config']->get('plugins.email.from', 'noreply@getgrav.org');
        $to = $this->grav['config']->get('plugins.email.email');

        $subject = $l->translate(['PLUGIN_GUESTBOOK.NEW_ENTRY_EMAIL_SUBJECT', $sitename]);
        $content = $l->translate(['PLUGIN_GUESTBOOK.NEW_ENTRY_EMAIL_BODY', $sitename, $entry['message']['text'], $entry['message']['author'], $entry['message']['email']]);

        $twig = $this->grav['twig'];
        $body = $twig->processTemplate('email/base.html.twig', ['content' => $content]);

        $message = $this->grav['Email']->message($subject, $body, 'text/html')
            ->setFrom($from)
            ->setTo($to);

        $sent = $this->grav['Email']->send($message);
    }

    private function getMessages($page = 0) {
        $number = 30;

        $lang = $this->grav['language']->getActive();
        $filename = DATA_DIR . 'guestbook/' . ($lang ? '/' . $lang : '') . 'messages.yaml';
        $file = File::instance($filename);

        if (!$file->content()) {
            //Item not found
            return;
        }

        $messages = Yaml::parse($file->content());

        $totalAvailable = count($messages);
        $messages = array_slice($messages, $page * $number, $number);
        $totalRetrieved = count($messages);
        $hasMore = false;

        if ($totalAvailable > $totalRetrieved) {
            $hasMore = true;
        }

        return (object)array(
            "messages" => $messages,
            "page" => $page,
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
     * Add plugin templates path
     */
    public function onTwigAdminTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/admin/templates';
    }
}
