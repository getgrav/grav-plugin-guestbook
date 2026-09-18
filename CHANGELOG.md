# v0.5.2
## 09/17/2026

1. [](#bugfix)
    * **[security] Approving and deleting messages now requires an authorized admin and a valid security token.** Moderation ran on a plain link with no check on who was asking, so a logged-in moderator could be tricked into approving or deleting messages by visiting another site. Thanks to @AlpetGexha
    * **[security] Guestbook messages are now shown as text rather than markup, on the site and in the admin.** A message containing HTML could run script in the browser of anyone reading the guestbook or moderating it — and anyone can post a message. Thanks to @AlpetGexha

# v0.5.1
## 05/01/2026

1. [](#improved)
    * Added 1.7|2.0 compatibility flags

# v0.5.0
## 01/24/2017

1. [](#new)
    * Added optional moderation capabilities [#7](https://github.com/getgrav/grav-plugin-guestbook/issues/7)
1. [](#bugfix)
    * Add support for Twig `Autoescape variables` mode
    * Fixed PHP 7.1 issue in moderation

# v0.4.0
## 10/19/2016

1. [](#improved)
    * Added german translation
    * Added romanian translation
1. [](#bugfix)
    * Fixed a french string

# v0.3.1
## 07/14/2016

1. [](#improved)
    * Translate some blueprint options

# v0.3.0
## 01/06/2016

1. [](#bugfix)
    * Correctly add the templates path

# v0.2.2
## 11/20/2015

1. [](#bugfix)
    * Only load the messages on guestbook pages, if the page exists

# v0.2.1
## 11/06/2015

1. [](#bugfix)
    * Show "Guestbook" in the available page templates

# v0.2.0
## 10/27/2015

1. [](#bugfix)
    * Fix loading guestbook messages on the first page load

# v0.1.0
## 10/16/2015

1. [](#new)
    * ChangeLog started...
