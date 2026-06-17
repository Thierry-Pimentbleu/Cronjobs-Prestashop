# Cron Task Manager for PrestaShop

A lightweight and efficient cron task manager module for PrestaShop 8.x.  
Schedule, monitor and run your automated tasks directly from the back office — no server access required.

## Features

- **Schedule any URL** on your shop domain (minute / hour / day / month / day of week)
- **One-shot tasks** — run once then auto-disable
- **Run now** — trigger any task manually from the back office
- **Execution log** — track the last runs with HTTP status, duration and response
- **Drag & drop reordering** of tasks
- **No log** option — exclude specific tasks from the execution log
- **Auto-update** — get notified in the back office when a new version is available and update in one click
- **Multi-language** — French, English, Spanish, German, Dutch, Italian, Portuguese

## Requirements

- PrestaShop 8.x
- PHP 7.4+
- A hosting plan that allows calling a URL every minute via cron (most shared hosts support this)

## Installation

1. Download the latest release ZIP from the [Releases](../../releases) page
2. In your PrestaShop back office, go to **Modules → Module Manager → Upload a module**
3. Upload the ZIP file
4. Click **Configure**

## How it works

Set up a single cron job on your hosting control panel that calls the dispatcher URL every minute:

```
* * * * * curl -s "https://yourshop.com/modules/pb_cronjobs/cron?token=YOUR_TOKEN"
```

The dispatcher URL and token are displayed on the module configuration page.  
The module then runs whichever tasks are scheduled for that minute.

## Auto-update

The module checks for new releases on GitHub every 12 hours.  
When an update is available, a notification appears in the PrestaShop back office with a direct link to the configuration page where you can update in one click.

## License

MIT — free to use, modify and redistribute. See [LICENSE](LICENSE).

---

## About the author

Developed by **Thierry** — PrestaShop specialist developer.  
I build custom modules, specific developments and bug fixes for PrestaShop stores.

🌐 [pimentbleu.fr](https://pimentbleu.fr)
