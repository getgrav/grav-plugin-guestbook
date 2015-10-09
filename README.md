# Grav Guestbook Plugin

The **Guestbook Plugin** for [Grav](http://github.com/getgrav/grav) adds the ability to add a guestbook page that can receive visitor messages.

| IMPORTANT!!! This plugin is currently in development as is to be considered a **beta release**.  As such, use this in a production environment **at your own risk!**. More features will be added in the future.

# Installation

The Guestbook plugin is easy to install with GPM.

```
$ bin/gpm install guestbook
```

Or clone from GitHub and put in the `user/plugins/guestbook` folder.

# Usage

Create a page of type `guestbook`, by calling its file `guestbook.md`, or by changing its type via the Admin Plugin.

That's it!

The page will host user messages in the form of a typical guestbook. It will show the last 30 messages with the option to load more.

# Enable the Captcha anti-spam filter

To reduce spam in your comments, enable the Google Recaptcha integration we added. Copy the plugin's `guestbook.yaml` to `user/config/plugins/guestbook.yaml` and enable `use_captcha`. Also add the Google Recaptcha API keys to allow it to work correctly.

# Where are the messages stored?

In the `user/data/guestbook` folder. They're organized by language, so every language has a corresponding file.

# Visualize messages

You can view messages through the `Data Manager` Plugin.

# Email notifications

Upon receiving a comment, if `enable_email_notifications` is enabled, the Guestbook plugin will send an email to the `notifications_email_to` address set in the plugin options.
