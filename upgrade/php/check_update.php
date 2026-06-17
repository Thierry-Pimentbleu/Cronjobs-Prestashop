<?php
/**
 * Cron Task Manager — pb_cronjobs
 *
 * @author    Thierry POULAIN — PimentBleu
 * @copyright 2026 Thierry POULAIN
 * @license   MIT https://opensource.org/licenses/MIT
 * @link      https://www.pimentbleu.fr
 */
require_once('../../../../config/config.inc.php');
require_once('../../pb_cronjobs.php');
require_once('../PbCronJobsUpdater.php');

header('Content-Type: application/json');

$expected = sha1(_COOKIE_KEY_ . 'pb_cronjobs_update' . date('Ymd'));
if (!isset($_POST['nonce']) || !hash_equals($expected, $_POST['nonce'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$updater = new PbCronJobsUpdater();
$force   = !empty($_POST['force']);

$latest    = $updater->getLatestVersion($force);
$installed = $updater->getInstalledVersion();
$hasUpdate = version_compare($latest, $installed, '>');
$changelog = $hasUpdate ? $updater->getChangelog() : [];

echo json_encode([
    'installed'  => $installed,
    'latest'     => $latest,
    'has_update' => $hasUpdate,
    'changelog'  => $changelog,
]);
