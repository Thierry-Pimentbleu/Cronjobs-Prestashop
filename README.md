# Gestionnaire de tâches cron pour PrestaShop

Un module léger et efficace pour gérer vos tâches cron directement depuis le back office PrestaShop 8.x.  
Planifiez, surveillez et exécutez vos tâches automatisées sans accès serveur.

## Fonctionnalités

- **Planification d'URLs** de votre boutique (minute / heure / jour / mois / jour de la semaine)
- **Tâche unique** — s'exécute une fois puis se désactive automatiquement
- **Exécution manuelle** — déclenchez n'importe quelle tâche depuis le back office
- **Journal d'exécution** — historique des dernières exécutions avec statut HTTP, durée et réponse
- **Réorganisation par glisser-déposer** des tâches
- **Option sans journal** — excluez certaines tâches du journal d'exécution
- **Mise à jour automatique** — soyez notifié dans le back office dès qu'une nouvelle version est disponible et mettez à jour en un clic
- **Multi-langues** — Français, Anglais, Espagnol, Allemand, Néerlandais, Italien, Portugais

## Prérequis

- PrestaShop 8.x
- PHP 7.4+
- Un hébergement permettant d'appeler une URL toutes les minutes via cron

## Installation

1. Téléchargez le ZIP de la dernière version depuis la page [Releases](../../releases)
2. Dans votre back office PrestaShop, allez dans **Modules → Gestionnaire de modules → Envoyer un module**
3. Envoyez le fichier ZIP
4. Cliquez sur **Configurer**

## Fonctionnement

Configurez une seule tâche cron sur votre hébergement qui appelle l'URL du dispatcher chaque minute :

```
* * * * * curl -s "https://votreboutique.com/modules/pb_cronjobs/cron?token=VOTRE_TOKEN"
```

L'URL et le token sont affichés sur la page de configuration du module.  
Le module exécute ensuite les tâches planifiées pour cette minute.

## Mise à jour automatique

Le module vérifie les nouvelles versions sur GitHub toutes les 12 heures.  
Quand une mise à jour est disponible, une notification apparaît dans le back office PrestaShop avec un lien direct vers la page de configuration pour mettre à jour en un clic.

## Licence

MIT — libre d'utilisation, de modification et de redistribution. Voir [LICENSE](LICENSE).

---

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
- A hosting plan that allows calling a URL every minute via cron

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

## À propos de l'auteur / About the author

Développé par **Thierry POULAIN** — développeur spécialisé PrestaShop.  
Je réalise des modules sur mesure, des développements spécifiques et des corrections de bugs pour les boutiques PrestaShop.

Developed by **Thierry POULAIN** — PrestaShop specialist developer.  
I build custom modules, specific developments and bug fixes for PrestaShop stores.

🌐 [https://www.pimentbleu.fr](https://www.pimentbleu.fr)
