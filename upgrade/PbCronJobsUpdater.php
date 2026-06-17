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

class PbCronJobsUpdater
{
    const OWNER          = 'Thierry-Pimentbleu';
    const REPO           = 'Cronjobs-Prestashop';
    const CHECK_INTERVAL = 43200; // 12 heures
    const CONF_LAST_CHECK = 'PB_CRONJOBS_LAST_CHECK';
    const CONF_LATEST_VER = 'PB_CRONJOBS_LATEST_VER';

    public function getInstalledVersion()
    {
        $version = Db::getInstance()->getValue(
            'SELECT `version` FROM `' . _DB_PREFIX_ . 'module` WHERE `name` = \'pb_cronjobs\''
        );
        return $version ?: '0.0.0';
    }

    public function getLatestVersion($force = false)
    {
        $lastCheck = (int)Configuration::getGlobalValue(self::CONF_LAST_CHECK);
        $cached    = Configuration::getGlobalValue(self::CONF_LATEST_VER);

        if (!$force && $cached && (time() - $lastCheck) < self::CHECK_INTERVAL) {
            return $cached;
        }

        $release = $this->fetchLatestRelease();
        if (!$release || empty($release['tag_name'])) {
            return $cached ?: $this->getInstalledVersion();
        }

        $tag     = $release['tag_name'];
        $version = (strpos($tag, 'v') === 0) ? substr($tag, 1) : $tag;

        Configuration::updateGlobalValue(self::CONF_LATEST_VER, $version);
        Configuration::updateGlobalValue(self::CONF_LAST_CHECK, time());

        return $version;
    }

    public function hasUpdate()
    {
        $latest    = $this->getLatestVersion();
        $installed = $this->getInstalledVersion();
        return version_compare($latest, $installed, '>');
    }

    public function getChangelog()
    {
        $releases = $this->fetchReleases();
        if (!$releases) {
            return [];
        }

        $installed = $this->getInstalledVersion();
        $result    = [];

        foreach ($releases as $release) {
            $tag = $release['tag_name'];
            $ver = (strpos($tag, 'v') === 0) ? substr($tag, 1) : $tag;
            if (version_compare($ver, $installed, '>')) {
                $result[] = [
                    'version' => $ver,
                    'date'    => substr($release['published_at'], 0, 10),
                    'body'    => isset($release['body']) ? $release['body'] : '',
                ];
            }
        }

        return $result;
    }

    public function downloadFiles()
    {
        $release = $this->fetchLatestRelease();
        if (!$release || empty($release['zipball_url'])) {
            return false;
        }

        $tmpZip = _PS_CORE_DIR_ . '/modules/pb_cronjobs/upgrade/temp.zip';
        $ch = curl_init($release['zipball_url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => ['User-Agent: pb_cronjobs-updater'],
            CURLOPT_TIMEOUT        => 60,
        ]);
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data || !file_put_contents($tmpZip, $data)) {
            return false;
        }

        $destDir = _PS_CORE_DIR_ . '/modules/pb_cronjobs/upgrade/download';
        if (is_dir($destDir)) {
            $this->deleteDirectory($destDir);
        }
        mkdir($destDir, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($tmpZip) !== true) {
            unlink($tmpZip);
            return false;
        }
        $zip->extractTo($destDir);
        $zip->close();
        unlink($tmpZip);

        // GitHub zips contiennent un sous-dossier — on remonte les fichiers
        foreach (array_diff(scandir($destDir), ['.', '..']) as $item) {
            if (is_dir($destDir . '/' . $item)) {
                $this->copyRecursive($destDir . '/' . $item, $destDir);
                $this->deleteDirectory($destDir . '/' . $item);
                break;
            }
        }

        return true;
    }

    public function updateFiles()
    {
        $src  = _PS_CORE_DIR_ . '/modules/pb_cronjobs/upgrade/download';
        $dest = _PS_CORE_DIR_ . '/modules/pb_cronjobs';
        if (!is_dir($src)) {
            return false;
        }
        $this->copyRecursive($src, $dest);
        return true;
    }

    public function updateDatabase()
    {
        $sqlDir    = _PS_CORE_DIR_ . '/modules/pb_cronjobs/upgrade/sql/';
        $installed = $this->getInstalledVersion();
        if (!is_dir($sqlDir)) {
            return true;
        }
        $db = Db::getInstance();
        foreach (glob($sqlDir . '*.sql') as $file) {
            $ver = pathinfo($file, PATHINFO_FILENAME);
            if (version_compare($ver, $installed, '>')) {
                $sql = str_replace('PREFIX_', _DB_PREFIX_, file_get_contents($file));
                foreach (preg_split("/;\s*[\r\n]+/", $sql) as $stmt) {
                    $stmt = trim($stmt);
                    if ($stmt && !$db->execute($stmt)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function clearCache()
    {
        Configuration::updateGlobalValue(self::CONF_LAST_CHECK, 0);
        Configuration::updateGlobalValue(self::CONF_LATEST_VER, '');
    }

    protected function fetchLatestRelease()
    {
        return $this->githubGet(
            'https://api.github.com/repos/' . self::OWNER . '/' . self::REPO . '/releases/latest'
        );
    }

    protected function fetchReleases()
    {
        return $this->githubGet(
            'https://api.github.com/repos/' . self::OWNER . '/' . self::REPO . '/releases'
        );
    }

    protected function githubGet($url)
    {
        if (!function_exists('curl_init')) {
            return null;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/vnd.github.v3+json',
                'User-Agent: pb_cronjobs-updater',
            ],
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $code !== 200) {
            return null;
        }

        return json_decode($response, true);
    }

    protected function copyRecursive($src, $dst)
    {
        if (!is_dir($src)) {
            return;
        }
        foreach (array_diff(scandir($src), ['.', '..']) as $item) {
            $s = $src . '/' . $item;
            $d = $dst . '/' . $item;
            if (is_dir($s)) {
                if (!is_dir($d)) {
                    mkdir($d, 0755, true);
                }
                $this->copyRecursive($s, $d);
            } else {
                copy($s, $d);
            }
        }
    }

    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (array_diff(scandir($dir), ['.', '..']) as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
