# Grav Guestbook Plugin

The **Guestbook Plugin** for [Grav](http://github.com/getgrav/grav) adds the ability to add a guestbook page that can receive visitor messages.

# Installation

The Guestbook plugin is easy to install with GPM.

```
$ bin/gpm install guestbook
```

Or clone from GitHub and put in the `user/plugins/guestbook` folder.

# Usage

Create a page of type `guestbook`, by calling its file `guestbook.md`, or by changing its type via the Admin Plugin.

Now fill that page with the form options. The default example page is:


```
---
title: Guestbook

form:
    name: guestbook
    fields:

        - name: author
          label: Name
          placeholder: Enter your name
          autofocus: on
          autocomplete: on
          type: text
          validate:
            required: true

        - name: email
          label: Email
          placeholder: Enter your email address
          type: email
          validate:
            required: true

        - name: text
          label: Message
          placeholder: Enter your message
          type: textarea
          validate:
            required: true

        - name: date
          type: hidden
          process:
            fillWithCurrentDateTime: true

        - name: g-recaptcha-response
          label: Captcha
          type: captcha
          recatpcha_site_key: 2jj21oiej23ioej23iojeoi32jeoi3
          recaptcha_not_validated: 'Captcha not valid!'
          validate:
            required: true
          process:
            ignore: true

    buttons:
        - type: submit
          value: Submit

    process:
        - captcha:
            recatpcha_secret: ej32uej3u2ijeiu32jeiu3jeuj32ui
        - email:
            subject: "[Site Guestbook] {{ form.value.name|e }}"
            body: "{% include 'forms/data.html.twig' %}"
        - save:
            filename: messages.yaml
            operation: 'add'
        - message: Thank you for writing your message!
---

# Add message

### Enter your message:
```

The page will host user messages in the form of a typical guestbook. It will show the last 30 messages with the option to load more.

# Enable the Captcha anti-spam filter

To reduce spam in your comments, enable the Google Recaptcha integration we added. In the Guestbook page markdown file, add the Google Recaptcha Site Key and the Recaptcha Secret.

# Where are the messages stored?

By default, in the `user/data/guestbook` folder.

# Visualize messages

You can view messages through the `Data Manager` Plugin.

# Email notifications

Upon receiving a message, if you set the `process.email` option in the page yaml, the Email Plugin is tasked with sending an email with the details.