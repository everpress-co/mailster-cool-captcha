# Mailster Cool Captcha

Contributors: everpress, mailster, xaverb  
Tags: mailster, captcha, newsletter, forms, spam  
Requires at least: 6.0  
Tested up to: 7.1  
Stable tag: 1.4.0  
License: GPLv2 or later

Add a self-hosted image captcha to Mailster newsletter signup forms—block bot subscriptions without Google API keys.

## Description

**Mailster Cool Captcha** adds an image-based text captcha to [Mailster](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Cool+Captcha) subscription forms. Visitors type the characters shown in the image before Mailster accepts the signup, which helps reduce automated newsletter spam on your WordPress site.

Use it when you want captcha protection on Mailster forms without connecting to Google reCAPTCHA or other third-party verification services.

> This plugin requires the [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Cool+Captcha) **2.3** or newer. Captcha images are generated on your server (PHP GD); no external captcha API is required.

### Why use Cool Captcha with Mailster?

- **Self-hosted captcha** — Image challenges are created on your site; submissions are checked against a server-side session value.
- **Per-form control** — Choose which Mailster subscription forms require a captcha.
- **Custom labels and errors** — Set the field label and the message shown when verification fails.
- **Tune the challenge** — Adjust image size, format (JPEG/PNG), quality, blur, strike-through line, word length, wave distortion, rotation, and English or Spanish word lists.
- **Optional skip for logged-in users** — Disable the captcha for visitors who are already signed in to WordPress.
- **Works with GDPR fields** — Field order respects Mailster GDPR checkbox placement when enabled.
- **Click to refresh** — Subscribers can reload the captcha image if it is hard to read.

### How it works

1. Install and activate **Mailster Cool Captcha** alongside Mailster.
2. Go to **Newsletter → Settings → Cool Captcha**.
3. Select the subscription forms that should show a captcha and adjust appearance options.
4. On submit, Mailster only accepts the signup when the entered text matches the captcha stored for that form.

Prefer Google reCAPTCHA instead? You may also try [Mailster reCaptcha for Forms](https://wordpress.org/plugins/mailster-recaptcha/). Addon overview: [Mailster on WordPress.org](https://wordpress.org/plugins/mailster/).

## Installation

1. Upload the entire `mailster-cool-captcha` folder to the `/wp-content/plugins/` directory, or install from the WordPress plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Make sure [Mailster](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Cool+Captcha) **2.3** or newer is installed and active.
4. Go to **Newsletter → Settings → Cool Captcha**, select the forms to protect, and save your settings.
5. Open a protected form on the front end and confirm the captcha image and input field appear before subscribing.

## Frequently Asked Questions

### Does this plugin work without Mailster?

No. Mailster Cool Captcha is an addon for the [Mailster Newsletter Plugin](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Cool+Captcha) and requires Mailster **2.3** or newer.

### How is this different from Mailster reCaptcha?

**Cool Captcha** shows a distorted image and text field on your site and validates the answer on your server. **[Mailster reCaptcha](https://wordpress.org/plugins/mailster-recaptcha/)** uses Google reCAPTCHA v3 and requires Google site and secret keys. Choose Cool Captcha if you want a self-hosted challenge without Google.

### Does it send data to a third-party captcha service?

No. The captcha image is generated on your WordPress server. Verification compares the visitor’s input to a value stored in the PHP session for that form—no external captcha API call is made.

### What PHP extensions are required?

The bundled image generator uses PHP’s GD functions (for example `imagecreatetruecolor`). Your hosting environment must have GD enabled for captcha images to display.

### Can I turn off the captcha for logged-in users?

Yes. Enable **Disable for logged in users** on the Cool Captcha settings screen.

### Which Mailster forms can use the captcha?

Enable captcha per form on **Newsletter → Settings → Cool Captcha** by selecting the forms in the **Forms** list. Only checked forms show the challenge.

### Where can I get support?

For Mailster and this addon, see [mailster.co](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Cool+Captcha).

## Screenshots

### 1. Option Interface

![Option Interface](https://ps.w.org/mailster-cool-captcha/assets/screenshot-1.png)

## Changelog

### 1.4.0

- fixed: captcha image broken on PHP 8.1+ due to deprecation notices in the image generator

### 1.3.1

- updated tested up to and required version

### 1.3

- escaping of html string in the admin

### 1.2

- fixed: Captcha is now below email address on unsubscribe page

### 1.1

- removed deprecated functions
- fixed position if GDPR check box is enabled.

### 1.0

- initial release
