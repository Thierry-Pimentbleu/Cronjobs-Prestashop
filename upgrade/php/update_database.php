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

$context = Context::getContext();
if (!$context->employee || !$context->employee->isLoggedBack()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$updater = new PbCronJobsUpdater();
$ok      = $updater->updateDatabase();

if ($ok) {
    $updater->clearCache();
}

echo json_encode(['success' => $ok]);
