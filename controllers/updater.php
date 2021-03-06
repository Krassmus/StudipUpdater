<?php

class UpdaterController extends PluginController {

    public function index_action()
    {
        Navigation::activateItem("/tools/studipupdate");
        $this->is_not_writable = $this->notWritableFolders();
        if (count($this->is_not_writable)) {
            $this->postMessage(
                MessageBox::info(
                    _("Der Webserver hat keine Dateiberechtigungen, um dieses Stud.IP zu updaten. Folgende Dateien bzw. Verzeichnisse sind nicht schreibfähig:"),
                    $this->is_not_writable
                )
            );
        }
        $max_size = min(self::parse_size(ini_get('post_max_size')), self::parse_size(ini_get('upload_max_filesize')));
        if ($max_size < 120 * 1024 * 1024) {
            $max_size = floor($max_size / (1024 * 1024));
            if ($max_size < 20) {
                $this->postMessage(MessageBox::error(sprintf(_("Es dürfen nur %s MB hochgeladen werden. Das ist eventuell zuwenig, um das Update einzuspielen."), $max_size)));
            } else {
                $this->postMessage(MessageBox::info(sprintf(_("Es dürfen nur %s MB hochgeladen werden. Das ist vermutlich zuwenig, um das Update einzuspielen."), $max_size)));
            }
        }
    }

    protected function postMessage($message)
    {
        if (method_exists("PageLayout", "postMessage")) {
            PageLayout::postMessage($message);
        } else {
            $this->messages[] = $message;
        }
    }

    static public function parse_size($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else {
            return round($size);
        }
    }

    public function precheck_action()
    {
        Navigation::activateItem("/tools/studipupdate");
        $redirect = true;
        $this->is_not_writable = $this->notWritableFolders();
        if (count($this->is_not_writable)) {
            $this->postMessage(
                MessageBox::info(
                    _("Der Webserver hat keine Dateiberechtigungen, um dieses Stud.IP zu updaten. Folgende Dateien bzw. Verzeichnisse sind nicht schreibfähig:"),
                    $this->is_not_writable
                )
            );
            $redirect = false;
        }
        $max_size = min(self::parse_size(ini_get('post_max_size')), self::parse_size(ini_get('upload_max_filesize')));
        if ($max_size < 120 * 1024 * 1024) {
            $max_size = floor($max_size / (1024 * 1024));
            if ($max_size < 20) {
                $this->postMessage(MessageBox::error(sprintf(_("Es dürfen nur %s MB hochgeladen werden. Das ist eventuell zuwenig, um das Update einzuspielen."), $max_size)));
            } else {
                $this->postMessage(MessageBox::info(sprintf(_("Es dürfen nur %s MB hochgeladen werden. Das ist vermutlich zuwenig, um das Update einzuspielen."), $max_size)));
            }
            $redirect = false;
        }
        if ($redirect) {
            $this->redirect("updater/download");
        }
    }

    public function download_action()
    {
        Navigation::activateItem("/tools/studipupdate");
        $this->service_release = $this->plugin->getVersion(true);
        $this->release = $this->plugin->getVersion(false);
        if (Request::isPost()) {
            $dir = $GLOBALS['TMP_PATH']."/studip_update_version";
            $zip = $GLOBALS['TMP_PATH']."/studip_update_version.zip";

            //aufräumen
            if (file_exists($dir)) {
                @rmdirr($dir);
            }
            @unlink($zip);

            if (Request::submitted("service_release")) {
                $url = "https://develop.studip.de/studip/plugins.php/studipdownloader/download/stable-".$this->service_release;
                file_put_contents($zip, file_get_contents($url));
            }
            if (Request::submitted("release")) {
                $url = "https://develop.studip.de/studip/plugins.php/studipdownloader/download/stable-".$this->release;
                file_put_contents($zip, file_get_contents($url));
            }
            if ($_FILES['new_studip'] && $_FILES['new_studip']['tmp_name']) {
                copy($_FILES['new_studip']['tmp_name'], $zip);
            }

            //Ordner erstellen und wieder aufräumen:
            mkdir($dir);
            \Studip\ZipArchive::extractToPath($zip, $dir);
            @unlink($zip);

            $this->redirect("updater/check");
        }
    }

    public function check_action()
    {
        Navigation::activateItem("/tools/studipupdate");
        $dir = $GLOBALS['TMP_PATH']."/studip_update_version";

        $this->is_not_writable = $this->notWritableFolders();
        if (count($this->is_not_writable)) {
            $this->postMessage(
                MessageBox::info(
                    _("Der Webserver hat keine Dateiberechtigungen, um dieses Stud.IP zu updaten. Folgende Dateien bzw. Verzeichnisse sind nicht schreibfähig:"),
                    $this->is_not_writable
                )
            );
        }

        if (count($_POST) && $_FILES['new_studip']) {
            //aufräumen
            if (file_exists($dir)) {
                @rmdirr($dir);
            }
            @unlink($dir.".zip");
            copy($_FILES['new_studip']['tmp_name'], $dir.".zip");
            mkdir($dir);
            \Studip\ZipArchive::extractToPath($dir.".zip", $dir);
            @unlink($dir.".zip");
        }

        $entries = (array) @scandir($dir);
        if (count($entries) === 3) {
            foreach ($entries as $entry) {
                if ($entry !== "." && $entry !== "..") {
                    $dir .= "/".$entry;
                }
            }
        }

        $this->release_notes = @file_get_contents($dir."/ChangeLog");
        $old_release_notes = file_get_contents($GLOBALS['ABSOLUTE_PATH_STUDIP']."/../ChangeLog");
        if (strpos($this->release_notes, $old_release_notes) !== false) {
            $this->release_notes = substr($this->release_notes, 0, strpos($this->release_notes, $old_release_notes));
        }

    }

    public function execute_action()
    {
        if (Request::isPost()) {
            //nun geht es los
            set_time_limit(0);
            $studip_dir = $GLOBALS['ABSOLUTE_PATH_STUDIP']."/..";
            $tmp_studip = $GLOBALS['TMP_PATH']."/studip_update_version";
            $already_copied = array(".", "..");

            $entries = (array) @scandir($tmp_studip);
            if (count($entries) === 3) {
                foreach ($entries as $entry) {
                    if ($entry !== "." && $entry !== "..") {
                        $tmp_studip .= "/".$entry;
                    }
                }
            }

            //config
            foreach (scandir($tmp_studip."/config") as $file) {
                if (!in_array($file, array(".", "..", "config.inc.php", "config_local.inc.php"))) {
                    copy($tmp_studip."/config/".$file, $studip_dir."/config/".$file);
                }
            }
            $already_copied[] = "config";

            //public
            $already_copied_public = array(".", "..", "pictures");
            if (file_exists($tmp_studip."/public/plugins_packages/core")) {
                @rmdirr($studip_dir."/public/plugins_packages/core");
                $this->copy($tmp_studip."/public/plugins_packages/core", $studip_dir."/public/plugins_packages/core");
            }
            $already_copied_public[] = "plugins_packages";
            $already_copied_public[] = ".htaccess";
            foreach (scandir($studip_dir."/public") as $file) {
                if (!in_array($file, $already_copied_public)) {
                    @rmdirr($studip_dir."/public/".$file);
                }
            }
            foreach (scandir($tmp_studip."/public") as $file) {
                if (!in_array($file, $already_copied_public)) {
                    @rmdirr($studip_dir."/public/".$file);
                    $this->copy($tmp_studip."/public/".$file, $studip_dir."/public/".$file);
                }
            }
            $already_copied[] = "public";

            //data
            $already_copied[] = "data";

            //everything else
            foreach (scandir($studip_dir) as $file) {
                if (!in_array($file, $already_copied)) {
                    @rmdirr($studip_dir."/public/".$file);
                }
            }
            foreach (scandir($tmp_studip) as $file) {
                if (!in_array($file, $already_copied)) {
                    @rmdirr($studip_dir."/".$file);
                    $this->copy($tmp_studip."/".$file, $studip_dir."/".$file);
                }
            }

            $this->postMessage(MessageBox::success(_("Programmdateien erfolgreich geupdated.")));
            header("Location: ".URLHelper::getURL("web_migrate.php"));
        }
        $this->render_nothing();
    }

    protected function notWritableFolders()
    {
        $dir = $GLOBALS['ABSOLUTE_PATH_STUDIP']."/..";
        $is_not_writable = array();
        $unnecessary_files = array(
            "config/config.inc.php", "config/config_local.inc.php", "public/.htaccess"
        );
        foreach (scandir($dir) as $file) {
            if ($file !== ".." && !is_writable($dir."/".$file) && !in_array($file, $unnecessary_files)) {
                $is_not_writable[] = $file;
            }
            if ($file !== ".." && $file !== "." && is_dir($dir."/".$file)) {
                foreach (scandir($dir."/".$file) as $subfile) {

                    if ($subfile !== ".." && $subfile !== "." && !in_array($file."/".$subfile, $unnecessary_files)
                        && !is_writable($dir."/".$file."/".$subfile)) {
                        $is_not_writable[] = $file."/".$subfile;
                    }
                }
            }
        }
        return $is_not_writable;
    }

    protected function copy($src, $dst) {
        if (is_file($src)) {
            return copy($src, $dst);
        } else {
            $dir = opendir($src);
            @mkdir($dst);
            while(false !== ($file = readdir($dir))) {
                if (($file !== '.') && ($file !== '..')) {
                    $this->copy($src."/".$file, $dst."/" .$file);
                }
            }
            closedir($dir);
        }
    }

}