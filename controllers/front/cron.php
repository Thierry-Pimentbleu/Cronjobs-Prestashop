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

require_once __DIR__ . '/../../classes/PbCronJobsRunner.php';

class Pb_CronJobsCronModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $token = Tools::getValue('token');
        if ($token !== Configuration::getGlobalValue(Pb_CronJobs::TOKEN)) {
            header('HTTP/1.1 403 Forbidden');
            die('Invalid token');
        }

        $action = Tools::getValue('action');
        if ($action === 'purge_logs') {
            $days = max(1, (int)Tools::getValue('days', 30));
            Db::getInstance()->execute(
                'DELETE FROM `' . _DB_PREFIX_ . 'pb_cronjobs_log`
                 WHERE `run_at` < DATE_SUB(NOW(), INTERVAL ' . (int)$days . ' DAY)'
            );
            die(json_encode(['ok' => true, 'days' => $days]));
        }

        $this->sendCallbackAndFlush();

        $runner = new PbCronJobsRunner();
        $runner->runAll();

        die();
    }

    /**
     * Send the HTTP response immediately so the cron caller does not wait
     * for all tasks to complete before receiving a reply.
     */
    protected function sendCallbackAndFlush()
    {
        ignore_user_abort(true);
        @set_time_limit(0);

        ob_start();
        echo 'pb_cronjobs_ok';
        header('Connection: close');
        header('Content-Length: ' . ob_get_length());
        ob_end_flush();
        flush();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
}
