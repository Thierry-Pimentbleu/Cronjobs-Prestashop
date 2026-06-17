<?php
/**
 * Cron Task Manager — pb_cronjobs
 *
 * @author    Thierry POULAIN — PimentBleu
 * @copyright 2026 Thierry POULAIN
 * @license   MIT https://opensource.org/licenses/MIT
 * @link      https://www.pimentbleu.fr
 *
 * Développeur spécialisé PrestaShop — modules sur mesure,
 * développements spécifiques et corrections de bugs.
 * PrestaShop specialist developer — custom modules,
 * specific developments and bug fixes.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/PbCronJobsRunner.php';
require_once __DIR__ . '/upgrade/PbCronJobsUpdater.php';

class Pb_CronJobs extends Module
{
    const TOKEN = 'PB_CRONJOBS_TOKEN';
    const LOGS_KEEP = 2000;

    protected $errors = [];
    protected $successes = [];

    public function __construct()
    {
        $this->name = 'pb_cronjobs';
        $this->tab = 'administration';
        $this->version = '1.1.0'; // x-release-please-version
        $this->author = 'PimentBleu';
        $this->need_instance = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Cron task manager');
        $this->description = $this->l('Schedule and monitor your automated tasks.');
    }

    protected static $_file_cache = [];

    protected static $tr = [
        'fr' => [
            'Cron task manager'                                              => 'Gestionnaire de tâches cron',
            'Schedule and monitor your automated tasks.'                     => 'Planifiez et supervisez vos tâches automatisées.',
            'Description is required.'                                       => 'La description est obligatoire.',
            'Target URL is required.'                                        => 'L\'URL cible est obligatoire.',
            'The URL must be an absolute URL on your shop domain.'           => 'L\'URL doit être une URL absolue de votre boutique.',
            'Invalid minute value (0-59).'                                   => 'Minute invalide (0-59).',
            'Invalid hour value (0-23).'                                     => 'Heure invalide (0-23).',
            'Invalid day value (1-31).'                                      => 'Jour invalide (1-31).',
            'Invalid month value (1-12).'                                    => 'Mois invalide (1-12).',
            'Invalid day of week value.'                                     => 'Jour de semaine invalide.',
            'Task added successfully.'                                       => 'Tâche ajoutée avec succès.',
            'An error occurred while adding the task.'                       => 'Erreur lors de l\'ajout de la tâche.',
            'Task updated successfully.'                                     => 'Tâche mise à jour avec succès.',
            'An error occurred while updating the task.'                     => 'Erreur lors de la mise à jour.',
            'Task deleted.'                                                  => 'Tâche supprimée.',
            'Task not found.'                                                => 'Tâche introuvable.',
            '"%s" executed — HTTP %d in %dms'                               => '"%s" exécutée — HTTP %d en %dms',
            '"%s" could not be reached — %s'                                => '"%s" injoignable — %s',
            'Every minute'                                                   => 'Toutes les minutes',
            'Every hour'                                                     => 'Toutes les heures',
            'Every day'                                                      => 'Tous les jours',
            'Every month'                                                    => 'Tous les mois',
            'January'  => 'Janvier',  'February'  => 'Février',   'March'    => 'Mars',
            'April'    => 'Avril',    'May'       => 'Mai',        'June'     => 'Juin',
            'July'     => 'Juillet',  'August'    => 'Août',       'September'=> 'Septembre',
            'October'  => 'Octobre',  'November'  => 'Novembre',   'December' => 'Décembre',
            'Monday'                                                         => 'Lundi',
            'Tuesday'                                                        => 'Mardi',
            'Wednesday'                                                      => 'Mercredi',
            'Thursday'                                                       => 'Jeudi',
            'Friday'                                                         => 'Vendredi',
            'Saturday'                                                       => 'Samedi',
            'Sunday'                                                         => 'Dimanche',
            'Minute'                                                         => 'Minute',
            'Hour'                                                           => 'Heure',
            'Day'                                                            => 'Jour',
            'Month'                                                          => 'Mois',
            'Day of week'                                                    => 'Jour de la semaine',
            'Call the following URL every minute in your hosting control panel:' => 'Appelez l\'URL suivante toutes les minutes dans votre panneau d\'hébergement :',
            'Copy'                                                           => 'Copier',
            'Example with curl:'                                             => 'Exemple avec curl :',
            'Edit cron task'                                                 => 'Modifier la tâche cron',
            'New cron task'                                                  => 'Nouvelle tâche cron',
            'Description'                                                    => 'Description',
            'Target URL'                                                     => 'URL cible',
            'e.g. Mondial Relay status update'                              => 'ex. Mise à jour statut Mondial Relay',
            'Must be an absolute URL on your shop domain.'                  => 'Doit être une URL absolue de votre boutique.',
            'Schedule'                                                       => 'Planification',
            'Use the default "all" value to run at every interval.'         => 'Laissez "Tous/Toutes" pour exécuter à chaque intervalle.',
            'One shot'                                                       => 'Exécution unique',
            'Run once then auto-disable'                                     => 'Exécuter une fois puis se désactiver',
            'Active'                                                         => 'Actif',
            'Enable this task'                                               => 'Activer cette tâche',
            'No log'                                                         => 'Sans journal',
            'Do not record this task in the execution log'                   => 'Ne pas enregistrer cette tâche dans le journal d\'exécution',
            'Cancel'                                                         => 'Annuler',
            'Save changes'                                                   => 'Enregistrer',
            'Add task'                                                       => 'Ajouter la tâche',
            'Cron tasks'                                                     => 'Tâches cron',
            'Add new task'                                                   => 'Ajouter une tâche',
            'Order'                                                          => 'Ordre',
            'URL'                                                            => 'URL',
            'Last run'                                                       => 'Dernière exécution',
            'Never'                                                          => 'Jamais',
            'Toggle one shot'                                                => 'Basculer exécution unique',
            'Yes'                                                            => 'Oui',
            'Toggle active'                                                  => 'Basculer actif',
            'Edit'                                                           => 'Modifier',
            'Run now'                                                        => 'Exécuter',
            'Delete'                                                         => 'Supprimer',
            'No cron tasks yet. Add your first task.'                       => 'Aucune tâche cron. Ajoutez votre première tâche.',
            'Execution log'                                                  => 'Journal d\'exécution',
            'Last 30 runs'                                                   => '30 dernières exécutions',
            'Date'                                                           => 'Date',
            'Task'                                                           => 'Tâche',
            'HTTP'                                                           => 'HTTP',
            'Duration'                                                       => 'Durée',
            'Response'                                                       => 'Réponse',
            'Delete cron task'                                               => 'Supprimer la tâche cron',
            'Are you sure you want to delete this task?'                    => 'Êtes-vous sûr de vouloir supprimer cette tâche ?',
            'Module offered by'                                              => 'Module offert par',
            'Actions'                                                        => 'Actions',
            'All tasks'                                                      => 'Toutes les tâches',
            'Clear log'                                                      => 'Vider le journal',
            'Delete all log entries?'                                        => 'Supprimer toutes les entrées du journal ?',
            'Auto-purge URL (add as cron task, adjust days= as needed):'    => 'URL de purge automatique (ajouter comme tâche cron, modifier days= si besoin) :',
            'Execution log cleared.'                                         => 'Journal d\'exécution vidé.',
            'An update is available for Cron task manager'                   => 'Une mise à jour est disponible pour le Gestionnaire de tâches cron',
            'Go to update'                                                   => 'Voir la mise à jour',
            'Available version'                                              => 'Version disponible',
            'Installed version'                                              => 'Version installée',
            "What's new"                                                     => 'Nouveautés',
            'Update'                                                         => 'Mise à jour',
            'Run update'                                                     => 'Lancer la mise à jour',
            'Downloading update...'                                          => 'Téléchargement de la mise à jour...',
            'Updating files...'                                              => 'Mise à jour des fichiers...',
            'Updating database...'                                           => 'Mise à jour de la base de données...',
            'Update complete'                                                => 'Mise à jour terminée',
            'Module updated successfully.'                                   => 'Le module a été mis à jour avec succès.',
            'Update error:'                                                  => 'Erreur de mise à jour :',
            'Close and reload'                                               => 'Fermer et recharger',
            'Up to date'                                                     => 'À jour',
            'Check for updates'                                              => 'Vérifier les mises à jour',
        ],
        'es' => [
            'Cron task manager'                                              => 'Gestor de tareas cron',
            'Schedule and monitor your automated tasks.'                     => 'Planifique y supervise sus tareas automatizadas.',
            'Description is required.'                                       => 'La descripción es obligatoria.',
            'Target URL is required.'                                        => 'La URL de destino es obligatoria.',
            'The URL must be an absolute URL on your shop domain.'           => 'La URL debe ser una URL absoluta de su tienda.',
            'Invalid minute value (0-59).'                                   => 'Minuto inválido (0-59).',
            'Invalid hour value (0-23).'                                     => 'Hora inválida (0-23).',
            'Invalid day value (1-31).'                                      => 'Día inválido (1-31).',
            'Invalid month value (1-12).'                                    => 'Mes inválido (1-12).',
            'Invalid day of week value.'                                     => 'Día de la semana inválido.',
            'Task added successfully.'                                       => 'Tarea añadida correctamente.',
            'An error occurred while adding the task.'                       => 'Error al añadir la tarea.',
            'Task updated successfully.'                                     => 'Tarea actualizada correctamente.',
            'An error occurred while updating the task.'                     => 'Error al actualizar la tarea.',
            'Task deleted.'                                                  => 'Tarea eliminada.',
            'Task not found.'                                                => 'Tarea no encontrada.',
            '"%s" executed — HTTP %d in %dms'                               => '"%s" ejecutada — HTTP %d en %dms',
            '"%s" could not be reached — %s'                                => '"%s" inaccesible — %s',
            'Every minute'                                                   => 'Cada minuto',
            'Every hour'                                                     => 'Cada hora',
            'Every day'                                                      => 'Cada día',
            'Every month'                                                    => 'Cada mes',
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
            'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
            'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
            'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre',
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo',
            'Minute' => 'Minuto', 'Hour' => 'Hora', 'Day' => 'Día', 'Month' => 'Mes',
            'Day of week'                                                    => 'Día de la semana',
            'e.g. Mondial Relay status update'                              => 'p.ej. Actualización estado Mondial Relay',
            'Must be an absolute URL on your shop domain.'                  => 'Debe ser una URL absoluta de su tienda.',
            'Schedule'                                                       => 'Planificación',
            'Use the default "all" value to run at every interval.'         => 'Deje "Todos/Todas" para ejecutar en cada intervalo.',
            'Copy' => 'Copiar', 'Edit' => 'Editar', 'Delete' => 'Eliminar',
            'Cancel' => 'Cancelar', 'Save changes' => 'Guardar cambios',
            'Add task' => 'Añadir tarea', 'Add new task' => 'Añadir nueva tarea',
            'Cron tasks' => 'Tareas cron', 'One shot' => 'Ejecución única',
            'Run once then auto-disable' => 'Ejecutar una vez y desactivarse',
            'Active' => 'Activo', 'Enable this task' => 'Activar esta tarea',
            'No log' => 'Sin registro', 'Do not record this task in the execution log' => 'No registrar esta tarea en el registro de ejecución',
            'Last run' => 'Última ejecución', 'Never' => 'Nunca', 'Yes' => 'Sí',
            'Toggle one shot' => 'Alternar ejecución única', 'Toggle active' => 'Alternar activo',
            'Run now' => 'Ejecutar', 'Actions' => 'Acciones', 'Order' => 'Orden',
            'URL' => 'URL', 'Date' => 'Fecha', 'Task' => 'Tarea', 'HTTP' => 'HTTP',
            'Execution log' => 'Registro de ejecución', 'Last 30 runs' => 'Últimas 30 ejecuciones',
            'Duration' => 'Duración', 'Response' => 'Respuesta',
            'Delete cron task' => 'Eliminar tarea cron',
            'Are you sure you want to delete this task?' => '¿Está seguro de que desea eliminar esta tarea?',
            'Module offered by' => 'Módulo ofrecido por',
            'No cron tasks yet. Add your first task.' => 'Ninguna tarea cron. Añada su primera tarea.',
            'All tasks' => 'Todas las tareas',
            'Clear log' => 'Vaciar registro',
            'Delete all log entries?' => '¿Eliminar todas las entradas del registro?',
            'Auto-purge URL (add as cron task, adjust days= as needed):' => 'URL de purga automática (agregar como tarea cron, ajustar days= según necesidad):',
            'Execution log cleared.' => 'Registro de ejecución vaciado.',
            'An update is available for Cron task manager' => 'Hay una actualización disponible para el Gestor de tareas cron',
            'Go to update' => 'Ver actualización',
            'Available version' => 'Versión disponible', 'Installed version' => 'Versión instalada',
            "What's new" => 'Novedades', 'Update' => 'Actualización', 'Run update' => 'Lanzar actualización',
            'Downloading update...' => 'Descargando actualización...', 'Updating files...' => 'Actualizando archivos...',
            'Updating database...' => 'Actualizando base de datos...', 'Update complete' => 'Actualización completada',
            'Module updated successfully.' => 'El módulo se ha actualizado correctamente.',
            'Update error:' => 'Error de actualización:', 'Close and reload' => 'Cerrar y recargar',
            'Up to date' => 'Al día', 'Check for updates' => 'Buscar actualizaciones',
            'Call the following URL every minute in your hosting control panel:' => 'Llame a la siguiente URL cada minuto en su panel de hosting:',
            'Example with curl:' => 'Ejemplo con curl:',
            'Edit cron task' => 'Editar tarea cron', 'New cron task' => 'Nueva tarea cron',
            'Description' => 'Descripción', 'Target URL' => 'URL de destino',
        ],
        'de' => [
            'Cron task manager'                                              => 'Cron-Aufgaben-Manager',
            'Schedule and monitor your automated tasks.'                     => 'Planen und überwachen Sie Ihre automatisierten Aufgaben.',
            'Description is required.'                                       => 'Beschreibung ist erforderlich.',
            'Target URL is required.'                                        => 'Ziel-URL ist erforderlich.',
            'The URL must be an absolute URL on your shop domain.'           => 'Die URL muss eine absolute URL Ihrer Shop-Domain sein.',
            'Invalid minute value (0-59).'                                   => 'Ungültiger Minutenwert (0-59).',
            'Invalid hour value (0-23).'                                     => 'Ungültiger Stundenwert (0-23).',
            'Invalid day value (1-31).'                                      => 'Ungültiger Tageswert (1-31).',
            'Invalid month value (1-12).'                                    => 'Ungültiger Monatswert (1-12).',
            'Invalid day of week value.'                                     => 'Ungültiger Wochentag.',
            'Task added successfully.'                                       => 'Aufgabe erfolgreich hinzugefügt.',
            'An error occurred while adding the task.'                       => 'Fehler beim Hinzufügen der Aufgabe.',
            'Task updated successfully.'                                     => 'Aufgabe erfolgreich aktualisiert.',
            'An error occurred while updating the task.'                     => 'Fehler beim Aktualisieren der Aufgabe.',
            'Task deleted.'                                                  => 'Aufgabe gelöscht.',
            'Task not found.'                                                => 'Aufgabe nicht gefunden.',
            '"%s" executed — HTTP %d in %dms'                               => '"%s" ausgeführt — HTTP %d in %dms',
            '"%s" could not be reached — %s'                                => '"%s" nicht erreichbar — %s',
            'Every minute' => 'Jede Minute', 'Every hour' => 'Jede Stunde',
            'Every day' => 'Jeden Tag', 'Every month' => 'Jeden Monat',
            'January' => 'Januar', 'February' => 'Februar', 'March' => 'März',
            'April' => 'April', 'May' => 'Mai', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'August', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Dezember',
            'Monday' => 'Montag', 'Tuesday' => 'Dienstag', 'Wednesday' => 'Mittwoch',
            'Thursday' => 'Donnerstag', 'Friday' => 'Freitag', 'Saturday' => 'Samstag', 'Sunday' => 'Sonntag',
            'Minute' => 'Minute', 'Hour' => 'Stunde', 'Day' => 'Tag', 'Month' => 'Monat',
            'Day of week'                                                    => 'Wochentag',
            'Call the following URL every minute in your hosting control panel:' => 'Rufen Sie die folgende URL jede Minute in Ihrem Hosting-Panel auf:',
            'Copy' => 'Kopieren', 'Example with curl:' => 'Beispiel mit curl:',
            'Edit cron task' => 'Cron-Aufgabe bearbeiten', 'New cron task' => 'Neue Cron-Aufgabe',
            'Description' => 'Beschreibung', 'Target URL' => 'Ziel-URL',
            'e.g. Mondial Relay status update'                              => 'z.B. Mondial Relay Statusaktualisierung',
            'Must be an absolute URL on your shop domain.'                  => 'Muss eine absolute URL Ihrer Shop-Domain sein.',
            'Schedule' => 'Zeitplan',
            'Use the default "all" value to run at every interval.'         => 'Lassen Sie "Alle" für die Ausführung bei jedem Intervall.',
            'One shot' => 'Einmalig', 'Run once then auto-disable' => 'Einmal ausführen und deaktivieren',
            'Active' => 'Aktiv', 'Enable this task' => 'Diese Aufgabe aktivieren',
            'No log' => 'Kein Protokoll', 'Do not record this task in the execution log' => 'Diese Aufgabe nicht im Ausführungsprotokoll aufzeichnen',
            'Cancel' => 'Abbrechen', 'Save changes' => 'Änderungen speichern',
            'Add task' => 'Aufgabe hinzufügen', 'Add new task' => 'Neue Aufgabe hinzufügen',
            'Cron tasks' => 'Cron-Aufgaben', 'Order' => 'Reihenfolge', 'URL' => 'URL',
            'Last run' => 'Letzte Ausführung', 'Never' => 'Nie',
            'Toggle one shot' => 'Einmalig umschalten', 'Yes' => 'Ja',
            'Toggle active' => 'Aktiv umschalten', 'Actions' => 'Aktionen',
            'Edit' => 'Bearbeiten', 'Run now' => 'Jetzt ausführen', 'Delete' => 'Löschen',
            'No cron tasks yet. Add your first task.'                       => 'Noch keine Cron-Aufgaben. Erste Aufgabe hinzufügen.',
            'Execution log' => 'Ausführungsprotokoll', 'Last 30 runs' => 'Letzte 30 Ausführungen',
            'Date' => 'Datum', 'Task' => 'Aufgabe', 'HTTP' => 'HTTP',
            'Duration' => 'Dauer', 'Response' => 'Antwort',
            'Delete cron task' => 'Cron-Aufgabe löschen',
            'Are you sure you want to delete this task?'                    => 'Sind Sie sicher, dass Sie diese Aufgabe löschen möchten?',
            'Module offered by' => 'Modul angeboten von',
            'All tasks' => 'Alle Aufgaben',
            'Clear log' => 'Protokoll leeren',
            'Delete all log entries?' => 'Alle Protokolleinträge löschen?',
            'Auto-purge URL (add as cron task, adjust days= as needed):' => 'Auto-Bereinigung URL (als Cron-Aufgabe hinzufügen, days= anpassen):',
            'Execution log cleared.' => 'Ausführungsprotokoll geleert.',
            'An update is available for Cron task manager' => 'Ein Update ist für den Cron-Aufgaben-Manager verfügbar',
            'Go to update' => 'Zum Update', 'Available version' => 'Verfügbare Version',
            'Installed version' => 'Installierte Version', "What's new" => 'Neuigkeiten',
            'Update' => 'Update', 'Run update' => 'Update starten',
            'Downloading update...' => 'Update wird heruntergeladen...', 'Updating files...' => 'Dateien werden aktualisiert...',
            'Updating database...' => 'Datenbank wird aktualisiert...', 'Update complete' => 'Update abgeschlossen',
            'Module updated successfully.' => 'Das Modul wurde erfolgreich aktualisiert.',
            'Update error:' => 'Update-Fehler:', 'Close and reload' => 'Schließen und neu laden',
            'Up to date' => 'Aktuell', 'Check for updates' => 'Nach Updates suchen',
        ],
        'nl' => [
            'Cron task manager'                                              => 'Cron-taakbeheerder',
            'Schedule and monitor your automated tasks.'                     => 'Plan en monitor uw geautomatiseerde taken.',
            'Description is required.'                                       => 'Beschrijving is verplicht.',
            'Target URL is required.'                                        => 'Doel-URL is verplicht.',
            'The URL must be an absolute URL on your shop domain.'           => 'De URL moet een absolute URL van uw winkeldomein zijn.',
            'Invalid minute value (0-59).'                                   => 'Ongeldige minuutwaarde (0-59).',
            'Invalid hour value (0-23).'                                     => 'Ongeldige uurwaarde (0-23).',
            'Invalid day value (1-31).'                                      => 'Ongeldige dagwaarde (1-31).',
            'Invalid month value (1-12).'                                    => 'Ongeldige maandwaarde (1-12).',
            'Invalid day of week value.'                                     => 'Ongeldige weekdagwaarde.',
            'Task added successfully.'                                       => 'Taak succesvol toegevoegd.',
            'An error occurred while adding the task.'                       => 'Fout bij het toevoegen van de taak.',
            'Task updated successfully.'                                     => 'Taak succesvol bijgewerkt.',
            'An error occurred while updating the task.'                     => 'Fout bij het bijwerken van de taak.',
            'Task deleted.'                                                  => 'Taak verwijderd.',
            'Task not found.'                                                => 'Taak niet gevonden.',
            '"%s" executed — HTTP %d in %dms'                               => '"%s" uitgevoerd — HTTP %d in %dms',
            '"%s" could not be reached — %s'                                => '"%s" niet bereikbaar — %s',
            'Every minute' => 'Elke minuut', 'Every hour' => 'Elk uur',
            'Every day' => 'Elke dag', 'Every month' => 'Elke maand',
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maart',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Augustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'December',
            'Monday' => 'Maandag', 'Tuesday' => 'Dinsdag', 'Wednesday' => 'Woensdag',
            'Thursday' => 'Donderdag', 'Friday' => 'Vrijdag', 'Saturday' => 'Zaterdag', 'Sunday' => 'Zondag',
            'Minute' => 'Minuut', 'Hour' => 'Uur', 'Day' => 'Dag', 'Month' => 'Maand',
            'Day of week'                                                    => 'Dag van de week',
            'Call the following URL every minute in your hosting control panel:' => 'Roep de volgende URL elke minuut aan in uw hostingbeheer:',
            'Copy' => 'Kopiëren', 'Example with curl:' => 'Voorbeeld met curl:',
            'Edit cron task' => 'Cron-taak bewerken', 'New cron task' => 'Nieuwe cron-taak',
            'Description' => 'Beschrijving', 'Target URL' => 'Doel-URL',
            'e.g. Mondial Relay status update'                              => 'bijv. Mondial Relay statusupdate',
            'Must be an absolute URL on your shop domain.'                  => 'Moet een absolute URL van uw winkel zijn.',
            'Schedule' => 'Planning',
            'Use the default "all" value to run at every interval.'         => 'Laat "Alle" staan om bij elk interval uit te voeren.',
            'One shot' => 'Eenmalig', 'Run once then auto-disable' => 'Eenmalig uitvoeren en uitschakelen',
            'Active' => 'Actief', 'Enable this task' => 'Deze taak activeren',
            'No log' => 'Geen log', 'Do not record this task in the execution log' => 'Deze taak niet registreren in het uitvoeringslog',
            'Cancel' => 'Annuleren', 'Save changes' => 'Wijzigingen opslaan',
            'Add task' => 'Taak toevoegen', 'Add new task' => 'Nieuwe taak toevoegen',
            'Cron tasks' => 'Cron-taken', 'Order' => 'Volgorde', 'URL' => 'URL',
            'Last run' => 'Laatste uitvoering', 'Never' => 'Nooit',
            'Toggle one shot' => 'Eenmalig omschakelen', 'Yes' => 'Ja',
            'Toggle active' => 'Actief omschakelen', 'Actions' => 'Acties',
            'Edit' => 'Bewerken', 'Run now' => 'Nu uitvoeren', 'Delete' => 'Verwijderen',
            'No cron tasks yet. Add your first task.'                       => 'Nog geen cron-taken. Voeg uw eerste taak toe.',
            'Execution log' => 'Uitvoeringslog', 'Last 30 runs' => 'Laatste 30 uitvoeringen',
            'Date' => 'Datum', 'Task' => 'Taak', 'HTTP' => 'HTTP',
            'Duration' => 'Duur', 'Response' => 'Reactie',
            'Delete cron task' => 'Cron-taak verwijderen',
            'Are you sure you want to delete this task?'                    => 'Weet u zeker dat u deze taak wilt verwijderen?',
            'Module offered by' => 'Module aangeboden door',
            'All tasks' => 'Alle taken',
            'Clear log' => 'Log wissen',
            'Delete all log entries?' => 'Alle logvermeldingen verwijderen?',
            'Auto-purge URL (add as cron task, adjust days= as needed):' => 'Auto-zuivering URL (voeg toe als cron-taak, pas days= aan):',
            'Execution log cleared.' => 'Uitvoeringslog gewist.',
            'An update is available for Cron task manager' => 'Er is een update beschikbaar voor de Cron-taakbeheerder',
            'Go to update' => 'Naar update', 'Available version' => 'Beschikbare versie',
            'Installed version' => 'Geïnstalleerde versie', "What's new" => 'Nieuw',
            'Update' => 'Update', 'Run update' => 'Update starten',
            'Downloading update...' => 'Update downloaden...', 'Updating files...' => 'Bestanden bijwerken...',
            'Updating database...' => 'Database bijwerken...', 'Update complete' => 'Update voltooid',
            'Module updated successfully.' => 'De module is succesvol bijgewerkt.',
            'Update error:' => 'Updatefout:', 'Close and reload' => 'Sluiten en herladen',
            'Up to date' => 'Up-to-date', 'Check for updates' => 'Controleren op updates',
        ],
        'it' => [
            'Cron task manager'                                              => 'Gestione attività cron',
            'Schedule and monitor your automated tasks.'                     => 'Pianifica e monitora le tue attività automatizzate.',
            'Description is required.'                                       => 'La descrizione è obbligatoria.',
            'Target URL is required.'                                        => "L'URL di destinazione è obbligatoria.",
            'The URL must be an absolute URL on your shop domain.'           => "L'URL deve essere un URL assoluto del dominio del negozio.",
            'Invalid minute value (0-59).'                                   => 'Valore minuti non valido (0-59).',
            'Invalid hour value (0-23).'                                     => 'Valore ore non valido (0-23).',
            'Invalid day value (1-31).'                                      => 'Valore giorni non valido (1-31).',
            'Invalid month value (1-12).'                                    => 'Valore mese non valido (1-12).',
            'Invalid day of week value.'                                     => 'Giorno della settimana non valido.',
            'Task added successfully.'                                       => 'Attività aggiunta con successo.',
            'An error occurred while adding the task.'                       => "Errore durante l'aggiunta dell'attività.",
            'Task updated successfully.'                                     => 'Attività aggiornata con successo.',
            'An error occurred while updating the task.'                     => "Errore durante l'aggiornamento dell'attività.",
            'Task deleted.'                                                  => 'Attività eliminata.',
            'Task not found.'                                                => 'Attività non trovata.',
            '"%s" executed — HTTP %d in %dms'                               => '"%s" eseguita — HTTP %d in %dms',
            '"%s" could not be reached — %s'                                => '"%s" non raggiungibile — %s',
            'Every minute' => 'Ogni minuto', 'Every hour' => 'Ogni ora',
            'Every day' => 'Ogni giorno', 'Every month' => 'Ogni mese',
            'January' => 'Gennaio', 'February' => 'Febbraio', 'March' => 'Marzo',
            'April' => 'Aprile', 'May' => 'Maggio', 'June' => 'Giugno',
            'July' => 'Luglio', 'August' => 'Agosto', 'September' => 'Settembre',
            'October' => 'Ottobre', 'November' => 'Novembre', 'December' => 'Dicembre',
            'Monday' => 'Lunedì', 'Tuesday' => 'Martedì', 'Wednesday' => 'Mercoledì',
            'Thursday' => 'Giovedì', 'Friday' => 'Venerdì', 'Saturday' => 'Sabato', 'Sunday' => 'Domenica',
            'Minute' => 'Minuto', 'Hour' => 'Ora', 'Day' => 'Giorno', 'Month' => 'Mese',
            'Day of week'                                                    => 'Giorno della settimana',
            'Call the following URL every minute in your hosting control panel:' => 'Chiama il seguente URL ogni minuto nel pannello di hosting:',
            'Copy' => 'Copia', 'Example with curl:' => 'Esempio con curl:',
            'Edit cron task' => 'Modifica attività cron', 'New cron task' => 'Nuova attività cron',
            'Description' => 'Descrizione', 'Target URL' => 'URL di destinazione',
            'e.g. Mondial Relay status update'                              => 'es. Aggiornamento stato Mondial Relay',
            'Must be an absolute URL on your shop domain.'                  => 'Deve essere un URL assoluto del tuo negozio.',
            'Schedule' => 'Pianificazione',
            'Use the default "all" value to run at every interval.'         => 'Lascia "Tutti/Tutte" per eseguire ad ogni intervallo.',
            'One shot' => 'Esecuzione unica', 'Run once then auto-disable' => 'Esegui una volta poi disabilita',
            'Active' => 'Attivo', 'Enable this task' => 'Abilita questa attività',
            'No log' => 'Nessun registro', 'Do not record this task in the execution log' => 'Non registrare questa attività nel registro di esecuzione',
            'Cancel' => 'Annulla', 'Save changes' => 'Salva modifiche',
            'Add task' => 'Aggiungi attività', 'Add new task' => 'Aggiungi nuova attività',
            'Cron tasks' => 'Attività cron', 'Order' => 'Ordine', 'URL' => 'URL',
            'Last run' => 'Ultima esecuzione', 'Never' => 'Mai',
            'Toggle one shot' => 'Commuta esecuzione unica', 'Yes' => 'Sì',
            'Toggle active' => 'Commuta attivo', 'Actions' => 'Azioni',
            'Edit' => 'Modifica', 'Run now' => 'Esegui ora', 'Delete' => 'Elimina',
            'No cron tasks yet. Add your first task.'                       => 'Nessuna attività cron. Aggiungi la tua prima attività.',
            'Execution log' => 'Registro esecuzioni', 'Last 30 runs' => 'Ultime 30 esecuzioni',
            'Date' => 'Data', 'Task' => 'Attività', 'HTTP' => 'HTTP',
            'Duration' => 'Durata', 'Response' => 'Risposta',
            'Delete cron task' => 'Elimina attività cron',
            'Are you sure you want to delete this task?'                    => 'Sei sicuro di voler eliminare questa attività?',
            'Module offered by' => 'Modulo offerto da',
            'All tasks' => 'Tutte le attività',
            'Clear log' => 'Cancella registro',
            'Delete all log entries?' => 'Eliminare tutte le voci del registro?',
            'Auto-purge URL (add as cron task, adjust days= as needed):' => 'URL purga automatica (aggiungere come attività cron, adattare days=):',
            'Execution log cleared.' => 'Registro esecuzioni cancellato.',
            'An update is available for Cron task manager' => 'È disponibile un aggiornamento per la Gestione attività cron',
            'Go to update' => 'Vai all\'aggiornamento', 'Available version' => 'Versione disponibile',
            'Installed version' => 'Versione installata', "What's new" => 'Novità',
            'Update' => 'Aggiornamento', 'Run update' => 'Avvia aggiornamento',
            'Downloading update...' => 'Download aggiornamento...', 'Updating files...' => 'Aggiornamento file...',
            'Updating database...' => 'Aggiornamento database...', 'Update complete' => 'Aggiornamento completato',
            'Module updated successfully.' => 'Il modulo è stato aggiornato con successo.',
            'Update error:' => 'Errore aggiornamento:', 'Close and reload' => 'Chiudi e ricarica',
            'Up to date' => 'Aggiornato', 'Check for updates' => 'Cerca aggiornamenti',
        ],
        'pt' => [
            'Cron task manager'                                              => 'Gestor de tarefas cron',
            'Schedule and monitor your automated tasks.'                     => 'Agende e monitorize as suas tarefas automatizadas.',
            'Description is required.'                                       => 'A descrição é obrigatória.',
            'Target URL is required.'                                        => 'O URL de destino é obrigatório.',
            'The URL must be an absolute URL on your shop domain.'           => 'O URL deve ser um URL absoluto do seu domínio de loja.',
            'Invalid minute value (0-59).'                                   => 'Valor de minuto inválido (0-59).',
            'Invalid hour value (0-23).'                                     => 'Valor de hora inválido (0-23).',
            'Invalid day value (1-31).'                                      => 'Valor de dia inválido (1-31).',
            'Invalid month value (1-12).'                                    => 'Valor de mês inválido (1-12).',
            'Invalid day of week value.'                                     => 'Valor de dia da semana inválido.',
            'Task added successfully.'                                       => 'Tarefa adicionada com sucesso.',
            'An error occurred while adding the task.'                       => 'Ocorreu um erro ao adicionar a tarefa.',
            'Task updated successfully.'                                     => 'Tarefa atualizada com sucesso.',
            'An error occurred while updating the task.'                     => 'Ocorreu um erro ao atualizar a tarefa.',
            'Task deleted.'                                                  => 'Tarefa eliminada.',
            'Task not found.'                                                => 'Tarefa não encontrada.',
            '"%s" executed — HTTP %d in %dms'                               => '"%s" executada — HTTP %d em %dms',
            '"%s" could not be reached — %s'                                => '"%s" inacessível — %s',
            'Every minute' => 'Cada minuto', 'Every hour' => 'Cada hora',
            'Every day' => 'Cada dia', 'Every month' => 'Cada mês',
            'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
            'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
            'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
            'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro',
            'Monday' => 'Segunda', 'Tuesday' => 'Terça', 'Wednesday' => 'Quarta',
            'Thursday' => 'Quinta', 'Friday' => 'Sexta', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo',
            'Minute' => 'Minuto', 'Hour' => 'Hora', 'Day' => 'Dia', 'Month' => 'Mês',
            'Day of week'                                                    => 'Dia da semana',
            'Call the following URL every minute in your hosting control panel:' => 'Chame o seguinte URL a cada minuto no seu painel de alojamento:',
            'Copy' => 'Copiar', 'Example with curl:' => 'Exemplo com curl:',
            'Edit cron task' => 'Editar tarefa cron', 'New cron task' => 'Nova tarefa cron',
            'Description' => 'Descrição', 'Target URL' => 'URL de destino',
            'e.g. Mondial Relay status update'                              => 'ex. Atualização estado Mondial Relay',
            'Must be an absolute URL on your shop domain.'                  => 'Deve ser um URL absoluto da sua loja.',
            'Schedule' => 'Agendamento',
            'Use the default "all" value to run at every interval.'         => 'Deixe "Todos/Todas" para executar em cada intervalo.',
            'One shot' => 'Execução única', 'Run once then auto-disable' => 'Executar uma vez e desativar',
            'Active' => 'Ativo', 'Enable this task' => 'Ativar esta tarefa',
            'No log' => 'Sem registo', 'Do not record this task in the execution log' => 'Não registar esta tarefa no registo de execução',
            'Cancel' => 'Cancelar', 'Save changes' => 'Guardar alterações',
            'Add task' => 'Adicionar tarefa', 'Add new task' => 'Adicionar nova tarefa',
            'Cron tasks' => 'Tarefas cron', 'Order' => 'Ordem', 'URL' => 'URL',
            'Last run' => 'Última execução', 'Never' => 'Nunca',
            'Toggle one shot' => 'Alternar execução única', 'Yes' => 'Sim',
            'Toggle active' => 'Alternar ativo', 'Actions' => 'Ações',
            'Edit' => 'Editar', 'Run now' => 'Executar agora', 'Delete' => 'Eliminar',
            'No cron tasks yet. Add your first task.'                       => 'Nenhuma tarefa cron. Adicione a sua primeira tarefa.',
            'Execution log' => 'Registo de execuções', 'Last 30 runs' => 'Últimas 30 execuções',
            'Date' => 'Data', 'Task' => 'Tarefa', 'HTTP' => 'HTTP',
            'Duration' => 'Duração', 'Response' => 'Resposta',
            'Delete cron task' => 'Eliminar tarefa cron',
            'Are you sure you want to delete this task?'                    => 'Tem a certeza de que deseja eliminar esta tarefa?',
            'Module offered by' => 'Módulo oferecido por',
            'All tasks' => 'Todas as tarefas',
            'Clear log' => 'Limpar registo',
            'Delete all log entries?' => 'Eliminar todas as entradas do registo?',
            'Auto-purge URL (add as cron task, adjust days= as needed):' => 'URL de purga automática (adicionar como tarefa cron, ajustar days=):',
            'Execution log cleared.' => 'Registo de execuções limpo.',
            'An update is available for Cron task manager' => 'Há uma atualização disponível para o Gestor de tarefas cron',
            'Go to update' => 'Ver atualização', 'Available version' => 'Versão disponível',
            'Installed version' => 'Versão instalada', "What's new" => 'Novidades',
            'Update' => 'Atualização', 'Run update' => 'Iniciar atualização',
            'Downloading update...' => 'A transferir atualização...', 'Updating files...' => 'A atualizar ficheiros...',
            'Updating database...' => 'A atualizar base de dados...', 'Update complete' => 'Atualização concluída',
            'Module updated successfully.' => 'O módulo foi atualizado com sucesso.',
            'Update error:' => 'Erro de atualização:', 'Close and reload' => 'Fechar e recarregar',
            'Up to date' => 'Atualizado', 'Check for updates' => 'Verificar atualizações',
        ],
    ];

    public function l($string, $specific = false, $locale = null)
    {
        // 1. Système PS natif (Symfony catalog en PS 8.x)
        $result = parent::l($string, $specific, $locale);
        if ($result !== $string) {
            return $result;
        }

        $iso = (isset($this->context) && $this->context->language) ? $this->context->language->iso_code : 'en';
        if ($iso === 'en') {
            return $string;
        }

        // 2. Chargement direct du fichier translations/$iso.php
        if (!array_key_exists($iso, self::$_file_cache)) {
            self::$_file_cache[$iso] = [];
            $file = __DIR__ . '/translations/' . $iso . '.php';
            if (file_exists($file)) {
                $prev = isset($GLOBALS['_MODULE']) ? $GLOBALS['_MODULE'] : null;
                include $file;
                self::$_file_cache[$iso] = isset($GLOBALS['_MODULE']) ? (array)$GLOBALS['_MODULE'] : [];
                if ($prev !== null) {
                    $GLOBALS['_MODULE'] = $prev;
                } else {
                    unset($GLOBALS['_MODULE']);
                }
            }
        }
        if (!empty(self::$_file_cache[$iso])) {
            foreach (['pb_cronjobs', 'configure', ($specific ?: '')] as $src) {
                if ($src === '') {
                    continue;
                }
                $key = '<{pb_cronjobs}pb_cronjobs>' . $src . '_' . md5($string);
                if (isset(self::$_file_cache[$iso][$key])) {
                    return self::$_file_cache[$iso][$key];
                }
            }
        }

        // 3. Tableau embarqué (fallback garanti)
        return isset(self::$tr[$iso][$string]) ? self::$tr[$iso][$string] : $string;
    }

    public function install()
    {
        Configuration::updateGlobalValue(self::TOKEN, bin2hex(random_bytes(16)));
        return parent::install()
            && $this->installDb()
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayBackOfficeTop');
    }

    public function uninstall()
    {
        Configuration::deleteByName(self::TOKEN);
        return $this->uninstallDb() && parent::uninstall();
    }

    protected function installDb()
    {
        $db = Db::getInstance();
        return $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pb_cronjobs` (
                `id_pb_cronjob` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `description`   VARCHAR(255) NOT NULL DEFAULT \'\',
                `task`          TEXT NOT NULL,
                `minute`        SMALLINT(2) NOT NULL DEFAULT -1,
                `hour`          SMALLINT(2) NOT NULL DEFAULT -1,
                `day`           SMALLINT(2) NOT NULL DEFAULT -1,
                `month`         SMALLINT(2) NOT NULL DEFAULT -1,
                `day_of_week`   SMALLINT(1) NOT NULL DEFAULT -1,
                `one_shot`      TINYINT(1) NOT NULL DEFAULT 0,
                `active`        TINYINT(1) NOT NULL DEFAULT 1,
                `position`      SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
                `last_run`      DATETIME NULL DEFAULT NULL,
                `created_at`    DATETIME NOT NULL,
                PRIMARY KEY (`id_pb_cronjob`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8
        ') && $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pb_cronjobs_log` (
                `id_log`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_pb_cronjob` INT(10) UNSIGNED NOT NULL,
                `run_at`        DATETIME NOT NULL,
                `duration_ms`   INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `http_code`     SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
                `status`        ENUM(\'success\',\'error\') NOT NULL DEFAULT \'success\',
                `response`      TEXT NULL DEFAULT NULL,
                PRIMARY KEY (`id_log`),
                KEY `idx_cronjob` (`id_pb_cronjob`),
                KEY `idx_run_at` (`run_at`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8
        ');
    }

    protected function uninstallDb()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'pb_cronjobs_log`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'pb_cronjobs`');
        return true;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addJS('https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', false);
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
            $this->context->controller->addJS($this->_path . 'views/js/update.js');
            Media::addJsDef([
                'pbCronjobsUpdatePath' => $this->_path . 'upgrade/php/',
                'pbCronjobsI18n'       => [
                    'downloading'  => $this->l('Downloading update...'),
                    'updatingFiles' => $this->l('Updating files...'),
                    'updatingDb'   => $this->l('Updating database...'),
                    'done'         => $this->l('Update complete'),
                    'success'      => $this->l('Module updated successfully.'),
                    'updateError'  => $this->l('Update error:'),
                    'reload'       => $this->l('Close and reload'),
                ],
            ]);
        }
    }

    public function hookDisplayBackOfficeTop()
    {
        if (Tools::getValue('configure') == $this->name) {
            return '';
        }
        $updater = new PbCronJobsUpdater();
        if (!$updater->hasUpdate()) {
            return '';
        }
        $configUrl = $this->getConfigureLink();
        return '<div style="background:#fcf8e3; border:1px solid #f0ad4e; padding:8px 20px; margin-bottom:0; display:flex; align-items:center; gap:15px;">'
            . '<span style="color:#8a6d3b;"><strong>pb_cronjobs</strong> — ' . $this->l('An update is available for Cron task manager') . '</span>'
            . '<a href="' . htmlspecialchars($configUrl, ENT_QUOTES) . '" class="btn btn-warning btn-xs">' . $this->l('Go to update') . '</a>'
            . '</div>';
    }

    // ─── MIGRATION ──────────────────────────────────────────────────────────

    protected function migrateDb()
    {
        $db = Db::getInstance();

        if (!Configuration::getGlobalValue('PB_CRONJOBS_V2')) {
            $col = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pb_cronjobs` LIKE \'position\'');
            if (!$col) {
                $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pb_cronjobs` ADD `position` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `active`');
                $db->execute('UPDATE `' . _DB_PREFIX_ . 'pb_cronjobs` SET `position` = `id_pb_cronjob`');
            }
            Configuration::updateGlobalValue('PB_CRONJOBS_V2', 1);
        }

        if (!Configuration::getGlobalValue('PB_CRONJOBS_V3')) {
            $col = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pb_cronjobs` LIKE \'no_log\'');
            if (!$col) {
                $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pb_cronjobs` ADD `no_log` TINYINT(1) NOT NULL DEFAULT 0 AFTER `active`');
            }
            Configuration::updateGlobalValue('PB_CRONJOBS_V3', 1);
        }

        if (!Configuration::getGlobalValue('PB_CRONJOBS_V4')) {
            $this->registerHook('displayBackOfficeTop');
            Configuration::updateGlobalValue('PB_CRONJOBS_V4', 1);
        }
    }

    // ─── MAIN BO ENTRY POINT ────────────────────────────────────────────────

    public function getContent()
    {
        $this->migrateDb();

        $updater = new PbCronJobsUpdater();

        if (Tools::getValue('check_update')) {
            $updater->getLatestVersion(true);
            Tools::redirectAdmin($this->getConfigureLink());
        }

        $hasUpdate     = $updater->hasUpdate();
        $latestVersion = $hasUpdate ? $updater->getLatestVersion() : '';
        $changelog     = $hasUpdate ? $updater->getChangelog() : [];

        // AJAX: reorder
        if (Tools::getValue('ajax') == 1 && Tools::getValue('action') === 'pb_reorder') {
            $this->processReorderAjax();
        }

        $this->loadFlashFromCookie();

        if (Tools::isSubmit('submitAddPbCronJob')) {
            $this->processAdd();
        } elseif (Tools::isSubmit('submitUpdatePbCronJob')) {
            $this->processUpdate();
        } elseif (Tools::getValue('deletepbcronjob') && Tools::getValue('id_pb_cronjob')) {
            $this->processDelete((int)Tools::getValue('id_pb_cronjob'));
        } elseif (Tools::getValue('activepbcronjob') && Tools::getValue('id_pb_cronjob')) {
            $this->processToggle('active', (int)Tools::getValue('id_pb_cronjob'));
        } elseif (Tools::getValue('oneshotpbcronjob') && Tools::getValue('id_pb_cronjob')) {
            $this->processToggle('one_shot', (int)Tools::getValue('id_pb_cronjob'));
        } elseif (Tools::getValue('runpbcronjob') && Tools::getValue('id_pb_cronjob')) {
            $this->processRunNow((int)Tools::getValue('id_pb_cronjob'));
        } elseif (Tools::isSubmit('purge_pb_logs')) {
            $this->processPurgeLogs();
        }

        $this->context->smarty->assign([
            'pb_base_link'  => $this->getConfigureLink(),
            'pb_cron_url'   => $this->getCronUrl(),
            'pb_errors'     => $this->errors,
            'pb_successes'  => $this->successes,
            'pb_module_dir' => $this->_path,
            'pb_l'          => $this->getTranslations(),
            'pb_sort_url'   => '',
        ]);

        $updateSection = $this->renderUpdateSection($hasUpdate, $changelog, $latestVersion);

        if ((Tools::getValue('newpbcronjob') || Tools::getValue('submitAddPbCronJob')) && !empty($this->errors)) {
            return $updateSection . $this->renderForm();
        }
        if (Tools::getValue('updatepbcronjob') && Tools::getValue('id_pb_cronjob')) {
            $cron = $this->getCron((int)Tools::getValue('id_pb_cronjob'));
            if ($cron) {
                return $updateSection . $this->renderForm($cron);
            }
        }
        if (Tools::getValue('newpbcronjob')) {
            return $updateSection . $this->renderForm();
        }

        return $updateSection . $this->renderList();
    }

    // ─── FORM HANDLERS ──────────────────────────────────────────────────────

    protected function processReorderAjax()
    {
        $order = json_decode(Tools::getValue('order'), true);
        if (!is_array($order)) {
            die(json_encode(['success' => false]));
        }
        $ok = true;
        foreach ($order as $pos => $id) {
            $ok = $ok && Db::getInstance()->update(
                'pb_cronjobs',
                ['position' => (int)$pos + 1],
                '`id_pb_cronjob` = ' . (int)$id
            );
        }
        die(json_encode(['success' => $ok]));
    }

    protected function processAdd()
    {
        $data = $this->collectFormData();
        if (!$this->validateForm($data)) {
            return;
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['position']   = (int)Db::getInstance()->getValue(
            'SELECT COALESCE(MAX(`position`), 0) FROM `' . _DB_PREFIX_ . 'pb_cronjobs`'
        ) + 1;
        if (Db::getInstance()->insert('pb_cronjobs', $data)) {
            $this->successes[] = $this->l('Task added successfully.');
        } else {
            $this->errors[] = $this->l('An error occurred while adding the task.');
        }
    }

    protected function processUpdate()
    {
        $id = (int)Tools::getValue('id_pb_cronjob');
        if (!$id) {
            return;
        }
        $data = $this->collectFormData();
        if (!$this->validateForm($data)) {
            return;
        }
        if (Db::getInstance()->update('pb_cronjobs', $data, '`id_pb_cronjob` = ' . $id)) {
            $this->successes[] = $this->l('Task updated successfully.');
            Tools::redirectAdmin($this->getConfigureLink());
        } else {
            $this->errors[] = $this->l('An error occurred while updating the task.');
        }
    }

    protected function processDelete($id)
    {
        Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'pb_cronjobs_log` WHERE `id_pb_cronjob` = ' . $id
        );
        Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'pb_cronjobs` WHERE `id_pb_cronjob` = ' . $id
        );
        $this->successes[] = $this->l('Task deleted.');
        Tools::redirectAdmin($this->getConfigureLink());
    }

    protected function processToggle($field, $id)
    {
        $col = $field === 'active' ? 'active' : 'one_shot';
        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'pb_cronjobs`
             SET `' . $col . '` = IF(`' . $col . '` = 1, 0, 1)
             WHERE `id_pb_cronjob` = ' . $id
        );
        Tools::redirectAdmin($this->getConfigureLink());
    }

    protected function processRunNow($id)
    {
        $cron = $this->getCron($id);
        if (!$cron) {
            $this->saveFlashToCookie('error', $this->l('Task not found.'));
            Tools::redirectAdmin($this->getConfigureLink());
        }
        $runner = new PbCronJobsRunner();
        $result = $runner->executeTask($cron);

        $safeDesc = htmlspecialchars($cron['description'], ENT_QUOTES, 'UTF-8');

        if ($result['status'] === 'success') {
            $this->saveFlashToCookie('success', sprintf(
                $this->l('"%s" executed — HTTP %d in %dms'),
                $safeDesc,
                $result['http_code'],
                $result['duration_ms']
            ));
        } else {
            $this->saveFlashToCookie('error', sprintf(
                $this->l('"%s" could not be reached — %s'),
                $safeDesc,
                $result['error'] ?: 'no response'
            ));
        }
        Tools::redirectAdmin($this->getConfigureLink());
    }

    protected function processPurgeLogs()
    {
        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'pb_cronjobs_log`');
        $this->successes[] = $this->l('Execution log cleared.');
    }

    // ─── FORM DATA ──────────────────────────────────────────────────────────

    protected function collectFormData()
    {
        return [
            'description' => pSQL(Tools::getValue('description', '')),
            'task'        => pSQL(Tools::getValue('task', '')),
            'minute'      => (int)Tools::getValue('minute', -1),
            'hour'        => (int)Tools::getValue('hour', -1),
            'day'         => (int)Tools::getValue('day', -1),
            'month'       => (int)Tools::getValue('month', -1),
            'day_of_week' => (int)Tools::getValue('day_of_week', -1),
            'one_shot'    => (int)Tools::getValue('one_shot', 0),
            'active'      => (int)Tools::getValue('active', 1),
            'no_log'      => (int)Tools::getValue('no_log', 0),
        ];
    }

    protected function validateForm($data)
    {
        $ok = true;
        if (empty($data['description'])) {
            $this->errors[] = $this->l('Description is required.');
            $ok = false;
        }
        if (empty($data['task'])) {
            $this->errors[] = $this->l('Target URL is required.');
            $ok = false;
        } elseif (!$this->isTaskUrlValid(Tools::getValue('task'))) {
            $this->errors[] = $this->l('The URL must be an absolute URL on your shop domain.');
            $ok = false;
        }
        if ($data['minute'] != -1 && ($data['minute'] < 0 || $data['minute'] > 59)) {
            $this->errors[] = $this->l('Invalid minute value (0-59).');
            $ok = false;
        }
        if ($data['hour'] != -1 && ($data['hour'] < 0 || $data['hour'] > 23)) {
            $this->errors[] = $this->l('Invalid hour value (0-23).');
            $ok = false;
        }
        if ($data['day'] != -1 && ($data['day'] < 1 || $data['day'] > 31)) {
            $this->errors[] = $this->l('Invalid day value (1-31).');
            $ok = false;
        }
        if ($data['month'] != -1 && ($data['month'] < 1 || $data['month'] > 12)) {
            $this->errors[] = $this->l('Invalid month value (1-12).');
            $ok = false;
        }
        if ($data['day_of_week'] != -1 && ($data['day_of_week'] < 1 || $data['day_of_week'] > 7)) {
            $this->errors[] = $this->l('Invalid day of week value.');
            $ok = false;
        }
        return $ok;
    }

    protected function isTaskUrlValid($url)
    {
        if (empty($url)) {
            return false;
        }
        $shopUrl    = Tools::getShopDomain(true, true) . __PS_BASE_URI__;
        $shopUrlSsl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;
        return (strpos($url, $shopUrl) === 0 || strpos($url, $shopUrlSsl) === 0);
    }

    // ─── DATA FETCH ─────────────────────────────────────────────────────────

    protected function getCron($id)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'pb_cronjobs` WHERE `id_pb_cronjob` = ' . (int)$id
        );
    }

    protected function getCronsList()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'pb_cronjobs` ORDER BY `position` ASC, `id_pb_cronjob` ASC'
        );
        if (!$rows) {
            return [];
        }
        foreach ($rows as &$row) {
            $row['schedule'] = $this->formatSchedule($row);
            $row['task_display'] = Tools::strlen($row['task']) > 60
                ? Tools::substr($row['task'], 0, 60) . '…'
                : $row['task'];
        }
        return $rows;
    }

    protected function getRecentLogs()
    {
        return Db::getInstance()->executeS(
            'SELECT l.*, c.description
             FROM `' . _DB_PREFIX_ . 'pb_cronjobs_log` l
             LEFT JOIN `' . _DB_PREFIX_ . 'pb_cronjobs` c ON c.id_pb_cronjob = l.id_pb_cronjob
             ORDER BY l.id_log DESC'
        ) ?: [];
    }

    // ─── RENDER ─────────────────────────────────────────────────────────────

    protected function renderUpdateSection($hasUpdate, $changelog, $latestVersion)
    {
        $installedEsc = htmlspecialchars($this->version, ENT_QUOTES);
        $checkUrl     = htmlspecialchars($this->getConfigureLink() . '&check_update=1', ENT_QUOTES);

        if (!$hasUpdate) {
            return '<div class="alert alert-info" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:15px;">'
                . '<span><strong>pb_cronjobs</strong> &nbsp;v' . $installedEsc
                . ' &nbsp;<span style="color:#31708f;">&#10003; ' . $this->l('Up to date') . '</span></span>'
                . '<a href="' . $checkUrl . '" class="btn btn-default btn-xs">' . $this->l('Check for updates') . '</a>'
                . '</div>';
        }

        $changelogHtml = '';
        foreach ($changelog as $release) {
            $changelogHtml .= '<div style="margin-bottom:8px;">'
                . '<strong>v' . htmlspecialchars($release['version'], ENT_QUOTES) . '</strong>'
                . '<span style="color:#999; margin-left:8px; font-size:11px;">' . htmlspecialchars($release['date'], ENT_QUOTES) . '</span>'
                . '<div style="margin-top:4px; font-size:12px;">' . $this->parseReleaseBody($release['body']) . '</div>'
                . '</div>';
        }

        $btnUpdate  = htmlspecialchars($this->l('Run update'), ENT_QUOTES);
        $btnClose   = htmlspecialchars($this->l('Close'), ENT_QUOTES);
        $latestEsc  = htmlspecialchars($latestVersion, ENT_QUOTES);
        $installedEsc = htmlspecialchars($this->version, ENT_QUOTES);
        $whatsNew   = htmlspecialchars($this->l("What's new"), ENT_QUOTES);

        $banner = '<div class="alert alert-warning" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">'
            . '<div>'
            . '<strong>' . $this->l('An update is available for Cron task manager') . '</strong>'
            . '<span style="margin-left:15px; color:#666;">'
            . $this->l('Installed version') . ': ' . $installedEsc
            . ' &rarr; ' . $this->l('Available version') . ': v' . $latestEsc
            . '</span>'
            . '</div>'
            . '<button id="pbCronjobsBtnUpdate" class="btn btn-warning btn-sm">' . $btnUpdate . '</button>'
            . '</div>';

        $modal = '<div id="pbCronjobsUpdateModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.6); z-index:99999; align-items:center; justify-content:center;">'
            . '<div style="background:#fff; border-radius:6px; padding:30px; max-width:520px; width:90%; box-shadow:0 10px 40px rgba(0,0,0,.3);">'
            . '<h4 style="margin-top:0;">' . $this->l('Update') . ' v' . $latestEsc . '</h4>'
            . ($changelogHtml
                ? '<div style="max-height:180px; overflow-y:auto; background:#f8f9fa; padding:10px 15px; border-radius:4px; margin-bottom:20px; font-size:13px;">'
                  . '<strong>' . $whatsNew . '</strong><div style="margin-top:6px;">' . $changelogHtml . '</div>'
                  . '</div>'
                : '')
            . '<div style="margin-bottom:15px;">'
            . '<progress id="pbCronjobsProgressBar" max="100" value="0" style="width:100%; height:10px;"></progress>'
            . '<div style="display:flex; justify-content:space-between; margin-top:4px;">'
            . '<span id="pbCronjobsStepLabel" style="font-size:12px; color:#666;"></span>'
            . '<span id="pbCronjobsProgressNumber" style="font-size:12px; font-weight:bold;">0%</span>'
            . '</div>'
            . '</div>'
            . '<div id="pbCronjobsResult"></div>'
            . '<div style="text-align:right; margin-top:15px;">'
            . '<button id="pbCronjobsBtnClose" class="btn btn-default">' . $btnClose . '</button>'
            . '</div>'
            . '</div>'
            . '</div>';

        return $banner . $modal;
    }

    protected function parseReleaseBody($body)
    {
        $html    = '';
        $inList  = false;
        foreach (explode("\n", $body) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '**Full Changelog') === 0) {
                continue;
            }
            if (preg_match('/^#+\s*(.+)$/', $line, $m)) {
                if ($inList) {
                    $html   .= '</ul>';
                    $inList  = false;
                }
                $html .= '<strong style="display:block; margin-top:6px;">' . htmlspecialchars($m[1], ENT_QUOTES) . '</strong>';
            } elseif (preg_match('/^\*\s+(.+)$/', $line, $m)) {
                if (!$inList) {
                    $html  .= '<ul style="margin:4px 0 4px 16px; padding:0;">';
                    $inList = true;
                }
                $text  = preg_replace('/^(feat|fix|chore|refactor|docs|style|test)(\([^)]+\))?:\s*/i', '', $m[1]);
                $text  = preg_replace('/\s+by @\S+$/', '', $text);
                $html .= '<li style="margin:2px 0;">' . htmlspecialchars($text, ENT_QUOTES) . '</li>';
            }
        }
        if ($inList) {
            $html .= '</ul>';
        }
        return $html;
    }

    protected function renderList()
    {
        $this->context->smarty->assign([
            'pb_crons'      => $this->getCronsList(),
            'pb_logs'       => $this->getRecentLogs(),
            'pb_purge_url'  => $this->getPurgeLogsUrl(),
            'pb_mode'       => 'list',
            'pb_sort_url'   => $this->getConfigureLink() . '&ajax=1&action=pb_reorder',
        ]);
        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    protected function renderForm($cron = null)
    {
        $fields = [
            ['name' => 'minute',      'label' => $this->l('Minute'),       'opts' => $this->buildOptions(0, 59, $this->l('Every minute'))],
            ['name' => 'hour',        'label' => $this->l('Hour'),         'opts' => $this->buildOptions(0, 23, $this->l('Every hour'))],
            ['name' => 'day',         'label' => $this->l('Day'),          'opts' => $this->buildOptions(1, 31, $this->l('Every day'))],
            ['name' => 'month',       'label' => $this->l('Month'),        'opts' => $this->buildMonthOptions()],
            ['name' => 'day_of_week', 'label' => $this->l('Day of week'),  'opts' => $this->buildDowOptions()],
        ];
        foreach ($fields as &$f) {
            $f['current'] = $cron ? (int)$cron[$f['name']] : -1;
        }
        unset($f);

        $this->context->smarty->assign([
            'pb_mode'   => 'form',
            'pb_cron'   => $cron,
            'pb_fields' => $fields,
        ]);
        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    // ─── SELECT OPTIONS ─────────────────────────────────────────────────────

    protected function buildOptions($from, $to, $allLabel)
    {
        $opts = [['id' => -1, 'label' => $allLabel]];
        for ($i = $from; $i <= $to; $i++) {
            $opts[] = ['id' => $i, 'label' => str_pad($i, 2, '0', STR_PAD_LEFT)];
        }
        return $opts;
    }

    protected function buildMonthOptions()
    {
        $opts = [['id' => -1, 'label' => $this->l('Every month')]];
        $names = ['January','February','March','April','May','June',
                  'July','August','September','October','November','December'];
        foreach ($names as $i => $name) {
            $opts[] = ['id' => $i + 1, 'label' => $this->l($name)];
        }
        return $opts;
    }

    protected function buildDowOptions()
    {
        return [
            ['id' => -1, 'label' => $this->l('Every day')],
            ['id' => 1,  'label' => $this->l('Monday')],
            ['id' => 2,  'label' => $this->l('Tuesday')],
            ['id' => 3,  'label' => $this->l('Wednesday')],
            ['id' => 4,  'label' => $this->l('Thursday')],
            ['id' => 5,  'label' => $this->l('Friday')],
            ['id' => 6,  'label' => $this->l('Saturday')],
            ['id' => 7,  'label' => $this->l('Sunday')],
        ];
    }

    // ─── TRANSLATIONS ───────────────────────────────────────────────────────

    protected function getTranslations()
    {
        return [
            // Info panel
            'cron_task_manager' => $this->l('Cron task manager'),
            'call_url'          => $this->l('Call the following URL every minute in your hosting control panel:'),
            'copy'              => $this->l('Copy'),
            'example_curl'      => $this->l('Example with curl:'),
            // Form
            'edit_task'         => $this->l('Edit cron task'),
            'new_task'          => $this->l('New cron task'),
            'lbl_description'   => $this->l('Description'),
            'lbl_url'           => $this->l('Target URL'),
            'placeholder_desc'  => $this->l('e.g. Mondial Relay status update'),
            'url_help'          => $this->l('Must be an absolute URL on your shop domain.'),
            'lbl_schedule'      => $this->l('Schedule'),
            'schedule_help'     => $this->l('Use the default "all" value to run at every interval.'),
            'lbl_one_shot'      => $this->l('One shot'),
            'one_shot_help'     => $this->l('Run once then auto-disable'),
            'lbl_active'        => $this->l('Active'),
            'active_help'       => $this->l('Enable this task'),
            'lbl_no_log'        => $this->l('No log'),
            'no_log_help'       => $this->l('Do not record this task in the execution log'),
            'btn_cancel'        => $this->l('Cancel'),
            'btn_save'          => $this->l('Save changes'),
            'btn_add'           => $this->l('Add task'),
            // List columns
            'cron_tasks'        => $this->l('Cron tasks'),
            'add_new_task'      => $this->l('Add new task'),
            'col_order'         => $this->l('Order'),
            'col_description'   => $this->l('Description'),
            'col_url'           => $this->l('URL'),
            'col_schedule'      => $this->l('Schedule'),
            'col_last_run'      => $this->l('Last run'),
            'col_one_shot'      => $this->l('One shot'),
            'col_active'        => $this->l('Active'),
            'col_actions'       => $this->l('Actions'),
            'never'             => $this->l('Never'),
            'toggle_one_shot'   => $this->l('Toggle one shot'),
            'yes'               => $this->l('Yes'),
            'toggle_active'     => $this->l('Toggle active'),
            'btn_edit'          => $this->l('Edit'),
            'btn_run'           => $this->l('Run now'),
            'btn_delete'        => $this->l('Delete'),
            'no_tasks'          => $this->l('No cron tasks yet. Add your first task.'),
            // Logs
            'exec_log'          => $this->l('Execution log'),
            'all_tasks'         => $this->l('All tasks'),
            'btn_purge_logs'    => $this->l('Clear log'),
            'confirm_purge'     => $this->l('Delete all log entries?'),
            'auto_purge_help'   => $this->l('Auto-purge URL (add as cron task, adjust days= as needed):'),
            'col_date'          => $this->l('Date'),
            'col_task'          => $this->l('Task'),
            'col_http'          => $this->l('HTTP'),
            'col_duration'      => $this->l('Duration'),
            'col_response'      => $this->l('Response'),
            // Modal
            'modal_title'       => $this->l('Delete cron task'),
            'modal_confirm'     => $this->l('Are you sure you want to delete this task?'),
            // Footer
            'offered_by'        => $this->l('Module offered by'),
            // Update
            'update_available'  => $this->l('An update is available for Cron task manager'),
            'go_to_update'      => $this->l('Go to update'),
            'available_version' => $this->l('Available version'),
            'installed_version' => $this->l('Installed version'),
            'whats_new'         => $this->l("What's new"),
            'run_update'        => $this->l('Run update'),
            'downloading'       => $this->l('Downloading update...'),
            'updating_files'    => $this->l('Updating files...'),
            'updating_db'       => $this->l('Updating database...'),
            'update_complete'   => $this->l('Update complete'),
            'update_success'    => $this->l('Module updated successfully.'),
            'update_error'      => $this->l('Update error:'),
            'close_reload'      => $this->l('Close and reload'),
        ];
    }

    // ─── HELPERS ────────────────────────────────────────────────────────────

    protected function formatSchedule($cron)
    {
        $m   = (int)$cron['minute'];
        $h   = (int)$cron['hour'];
        $d   = (int)$cron['day'];
        $mo  = (int)$cron['month'];
        $dow = (int)$cron['day_of_week'];

        $parts = [
            $m   === -1 ? '*' : str_pad($m, 2, '0', STR_PAD_LEFT),
            $h   === -1 ? '*' : str_pad($h, 2, '0', STR_PAD_LEFT),
            $d   === -1 ? '*' : $d,
            $mo  === -1 ? '*' : $mo,
            $dow === -1 ? '*' : $dow,
        ];
        return implode(' ', $parts);
    }

    protected function getCronUrl()
    {
        return $this->context->link->getModuleLink(
            $this->name,
            'cron',
            ['token' => Configuration::getGlobalValue(self::TOKEN)]
        );
    }

    protected function getPurgeLogsUrl($days = 30)
    {
        return $this->context->link->getModuleLink(
            $this->name,
            'cron',
            ['token' => Configuration::getGlobalValue(self::TOKEN), 'action' => 'purge_logs', 'days' => $days]
        );
    }

    protected function getConfigureLink()
    {
        return $this->context->link->getAdminLink('AdminModules', true)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
    }

    protected function saveFlashToCookie($type, $message)
    {
        $this->context->cookie->{'pb_cronjobs_' . $type} = $message;
    }

    protected function loadFlashFromCookie()
    {
        foreach (['success', 'error'] as $type) {
            $key = 'pb_cronjobs_' . $type;
            if (!empty($this->context->cookie->$key)) {
                $val = htmlspecialchars_decode($this->context->cookie->$key, ENT_QUOTES);
                if ($type === 'success') {
                    $this->successes[] = $val;
                } else {
                    $this->errors[] = $val;
                }
                unset($this->context->cookie->$key);
            }
        }
    }
}
