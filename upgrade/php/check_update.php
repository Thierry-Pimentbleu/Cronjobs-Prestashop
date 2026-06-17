<?php
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
