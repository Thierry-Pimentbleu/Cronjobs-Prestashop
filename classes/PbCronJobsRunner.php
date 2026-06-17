<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PbCronJobsRunner
{
    const LOGS_KEEP = 2000;

    /**
     * Run all active cron jobs that match the current time.
     */
    public function runAll()
    {
        $crons = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'pb_cronjobs` WHERE `active` = 1'
        );
        if (!$crons) {
            return;
        }
        foreach ($crons as $cron) {
            if ($this->shouldBeExecuted($cron)) {
                $this->executeTask($cron);
            }
        }
    }

    /**
     * Check if a cron job should run at the current minute.
     * Each field is independent: -1 means "every" (wildcard).
     * day_of_week: 1=Monday … 7=Sunday (matches PHP date('N')).
     */
    public function shouldBeExecuted(array $cron)
    {
        if ((int)$cron['minute']      !== -1 && (int)date('i') !== (int)$cron['minute'])      return false;
        if ((int)$cron['hour']        !== -1 && (int)date('H') !== (int)$cron['hour'])        return false;
        if ((int)$cron['day']         !== -1 && (int)date('j') !== (int)$cron['day'])         return false;
        if ((int)$cron['month']       !== -1 && (int)date('n') !== (int)$cron['month'])       return false;
        if ((int)$cron['day_of_week'] !== -1 && (int)date('N') !== (int)$cron['day_of_week']) return false;
        return true;
    }

    /**
     * Execute a single cron task via cURL, log the result.
     *
     * @return array{status: string, http_code: int, duration_ms: int, error: string}
     */
    public function executeTask(array $cron)
    {
        $url   = $cron['task'];
        $start = microtime(true);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'PbCronJobs/1.0 PrestaShop',
        ]);

        $body     = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        $duration = (int)round((microtime(true) - $start) * 1000);
        $status   = (!$curlErr && $httpCode > 0) ? 'success' : 'error';
        $response = $curlErr ?: mb_substr((string)$body, 0, 500);

        if (empty($cron['no_log'])) {
            $this->saveLog($cron['id_pb_cronjob'], $duration, $httpCode, $status, $response);
            $this->purgeLogs($cron['id_pb_cronjob']);
        }
        $this->updateCron($cron['id_pb_cronjob'], (bool)$cron['one_shot']);

        return [
            'status'      => $status,
            'http_code'   => $httpCode,
            'duration_ms' => $duration,
            'error'       => $curlErr,
        ];
    }

    protected function saveLog($id, $duration, $httpCode, $status, $response)
    {
        Db::getInstance()->insert('pb_cronjobs_log', [
            'id_pb_cronjob' => (int)$id,
            'run_at'        => date('Y-m-d H:i:s'),
            'duration_ms'   => (int)$duration,
            'http_code'     => $httpCode ?: null,
            'status'        => pSQL($status),
            'response'      => pSQL($response),
        ]);
    }

    protected function updateCron($id, $oneShot)
    {
        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'pb_cronjobs`
             SET `last_run` = NOW()' . ($oneShot ? ', `active` = 0' : '') . '
             WHERE `id_pb_cronjob` = ' . (int)$id
        );
    }

    protected function purgeLogs($id)
    {
        Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'pb_cronjobs_log`
             WHERE `id_pb_cronjob` = ' . (int)$id . '
             AND `id_log` NOT IN (
                 SELECT `id_log` FROM (
                     SELECT `id_log` FROM `' . _DB_PREFIX_ . 'pb_cronjobs_log`
                     WHERE `id_pb_cronjob` = ' . (int)$id . '
                     ORDER BY `id_log` DESC
                     LIMIT ' . self::LOGS_KEEP . '
                 ) AS `keep`
             )'
        );
    }
}
