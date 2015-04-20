<?php

require_once 'app/controllers/plugin_controller.php';

class UpdaterController extends PluginController {

    public function index_action()
    {
        Navigation::activateItem("/tools/studipupdate");
        $this->is_not_writable = $this->notWritableFolders();
        if (count($this->is_not_writable)) {
            PageLayout::postMessage(
                MessageBox::info(
                    _("Der Webserver hat keine Dateiberechtigungen, um dieses Stud.IP zu updaten. Folgende Dateien bzw. Verzeichnisse sind nicht schreibfähig:"),
                    $this->is_not_writable
                )
            );
        }
        $max_size = min(self::parse_size(ini_get('post_max_size')), self::parse_size(ini_get('upload_max_filesize')));
        if ($max_size < 30 * 1024 * 1024) {
            $max_size = floor($max_size / (1024 * 1024));
            if ($max_size < 20) {
                PageLayout::postMessage(MessageBox::error(sprintf(_("Es dürfen nur %s MB hochgeladen werden. Das ist eventuell zuwenig, um das Update einzuspielen."), $max_size)));
            } else {
                PageLayout::postMessage(MessageBox::info(sprintf(_("Es dürfen nur %s MB hochgeladen werden. Das ist vermutlich zuwenig, um das Update einzuspielen."), $max_size)));
            }
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

    public function check_action()
    {
        Navigation::activateItem("/tools/studipupdate");
        $dir = $GLOBALS['TMP_PATH']."/studip_update_version";

        if (Request::isPost() && $_FILES['new_studip']) {
            //aufräumen
            if (file_exists($dir)) {
                @rmdirr($dir);
            }
            @unlink($dir.".zip");
            copy($_FILES['new_studip']['tmp_name'], $dir.".zip");
            mkdir($dir);
            unzip_file($dir.".zip", $dir);
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
        $old_release_notes = @file_get_contents($GLOBALS['ABSOLUTE_PATH_STUDIP']."/../ChangeLog");
        if (strpos($this->release_notes, $old_release_notes) !== false) {
            $this->release_notes = substr($this->release_notes, 0, strpos($this->release_notes, $old_release_notes));
        }
    }

    public function execute_action()
    {
        if (Request::isPost()) {
            //nun geht es los
            $studip_dir = $GLOBALS['ABSOLUTE_PATH_STUDIP']."/../ChangeLog";
            $tmp_studip = $GLOBALS['TMP_PATH']."/studip_update_version";
            $already_copied = array(".", "..");

            //config
            foreach (scandir($tmp_studip."/config") as $file) {
                if (!in_array($file, array(".", "..", "config.inc.php", "config_local.inc.php"))) {
                    copy($tmp_studip."/config/".$file, $studip_dir."/config/".$file);
                }
            }
            $already_copied[] = "config";

            //public
            $already_copied_public = array(".", "..", "pictures");
            @rmdirr($studip_dir."/public/plugins_packages/core");
            copy($tmp_studip."/public/plugins_packages/core", $studip_dir."/public/plugins_packages/core");
            $already_copied_public[] = "plugins_packages";
            $already_copied_public[] = ".htaccess";
            foreach (scandir($studip_dir."/public") as $file) {
                if (!in_array($file, $already_copied_public)) {
                    @rmdirr($studip_dir."/public/".$file);
                }
            }
            foreach (scandir($tmp_studip."/public") as $file) {
                if (!in_array($file, $already_copied_public)) {
                    copy($tmp_studip."/public/".$file, $studip_dir."/public/".$file);
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
                    copy($tmp_studip."/".$file, $studip_dir."/".$file);
                }
            }

            PageLayout::postMessage(MessageBox::success(_("Programmdateien erfolgreich geupdated.")));
            header("Location: web_migrate.php");
        }
    }

    protected function notWritableFolders()
    {
        $dir = $GLOBALS['ABSOLUTE_PATH_STUDIP']."/..";
        $is_not_writable = array();
        foreach (scandir($dir) as $file) {
            if ($file !== ".." && !is_writable($dir."/".$file)) {
                $is_not_writable[] = $file;
            }
            if ($file !== ".." && $file !== "." && is_dir($dir."/".$file)) {
                $unnecessary_subfiles = array("config/config.inc.php", "config/config_local.inc.php", "public/.htaccess");
                foreach (scandir($dir."/".$file) as $subfile) {

                    if ($subfile !== ".." && $subfile !== "." && !in_array($file."/".$subfile, $unnecessary_subfiles)
                            && !is_writable($dir."/".$file."/".$subfile)) {
                        $is_not_writable[] = $file."/".$subfile;
                    }
                }
            }
        }
        return $is_not_writable;
    }

}