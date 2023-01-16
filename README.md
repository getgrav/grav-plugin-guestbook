# Guestbook Plugin

The **Guestbook** plugin is an extension for [Grav CMS](https://github.com/getgrav/grav). It adds the ability to add a guestbook page that can receive visitor messages and is based on flex-objects.

## Installation

Installing the Guestbook plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](https://learn.getgrav.org/cli-console/grav-cli-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install guestbook

This will install the Guestbook plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/guestbook`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `guestbook`. You can find these files on [GitHub](https://github.com/pikim/grav-plugin-guestbook) or via [GetGrav.org](https://getgrav.org/downloads/plugins).

You should now have all the plugin files under

    /your/site/grav/user/plugins/guestbook
	
> NOTE: This plugin is a modular component for Grav which may require other plugins to operate, please see its [blueprints.yaml-file on GitHub](https://github.com/pikim/grav-plugin-guestbook/blob/main/blueprints.yaml).

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/guestbook/guestbook.yaml` to `user/config/plugins/guestbook.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
moderation: true
```

Note that if you use the Admin Plugin, a file with your configuration named guestbook.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

## Usage

Create a page of type `guestbook`, by calling its file `guestbook.md`, or by changing its type via the Admin Plugin.

Now fill that page with the form options. The default example page is:

```
title: Guestbook

form:
    name: guestbook
    fields:
        author:
          label: Name
          placeholder: Enter your name
          type: text
          autofocus: on
          autocomplete: on
          validate:
            required: true
        email:
          label: Email
          placeholder: Enter your email address
          type: email
          validate:
            required: true
        text:
          label: Message
          placeholder: Enter your message
          type: textarea
          validate:
            required: true
        date:
          type: hidden
          process:
            fillWithCurrentDateTime: true
        g-recaptcha-response:
          label: Captcha
          type: captcha
          recaptcha_site_key: 2jj21oiej23ioej23iojeoi32jeoi3
          recaptcha_not_validated: 'Captcha not valid!'
          validate:
            required: true

    buttons:
      - type: submit
        value: Submit
      - type: reset
        value: Reset

    process:
      captcha:
        recaptcha_secret: ej32uej3u2ijeiu32jeiu3jeuj32ui
      # store as json
#      jsonAddGuestbookEntry:
#        operation: add
      save:
        operation: add
        filename: ../flex-objects/guestbook.yaml
        body: "{% include 'forms/guestbook.yaml.twig' %}"
      email:
        subject: "[Site Guestbook] {{ form.value.name|e }}"
        body: "{% include 'forms/data.html.twig' %}"
      message: Thank you for writing your message!
---

# Add message

### Enter your message:
```

The page will host user messages in the form of a typical guestbook.

### Enable the Captcha anti-spam filter

To reduce spam in your comments, enable the Google Recaptcha integration we added. In the Guestbook page markdown file, add the Google Recaptcha Site Key and the Recaptcha Secret.

### Email notifications

Upon receiving a message, if you set the `process.email` option in the page yaml, the Email Plugin is tasked with sending an email with the details.

## Where are the messages stored?

By default, in the `user/data/flex-objects/guestbook.yaml` folder. This can be changed in `user/plugins/guestbook/blueprints/flex-objects/guestbook.yaml` and `guestbook.md`.

## Visualize messages

You can view and accept messages through the `Guestbook` section in the Admin Plugin.

## Credits

Thanks to the authors of Grav CMS and the inital guestbook plugin.

## To Do

- [ ] add message pagination using the pagination plugin
- [ ] add custom classes if necessary. See and revert commit `d756e7cf5c015102c787b8b46667ee0521a7c2f0`
- [ ] add flex-objects layout templates if necessary. Just storing them in `user/plugins/guestbook/templates/flex/guestbook/collection/default.html.twig` and `user/plugins/guestbook/templates/flex/guestbook/object/default.html.twig` doesn't work and storing them to `user/plugins/flex-objects/templates/flex/guestbook/...` is very ugly
