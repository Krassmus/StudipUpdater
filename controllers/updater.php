<?php

require_once 'app/controllers/plugin_controller.php';

class UpdaterController extends PluginController {

    public function index_action()
    {
        Navigation::activateItem("/tools/studipupdate");
        $is_not_writable = $this->notWritableFolders();
        if (count($is_not_writable)) {
            PageLayout::postMessage(
                MessageBox::info(
                    _("Der Webserver hat keine Dateiberechtigungen, um dieses Stud.IP zu updaten. Folgende Dateien bzw. Verzeichnisse sind nicht schreibfähig:"),
                    $is_not_writable
                )
            );
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

        $entries = scandir($dir);
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