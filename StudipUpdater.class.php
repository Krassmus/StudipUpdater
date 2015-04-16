<?php

class StudipUpdater extends StudIPPlugin implements SystemPlugin {

    static protected $rssURL = "http://sourceforge.net/projects/studip/rss?path=/Stud.IP";

    public function __construct() {
        parent::__construct();
        if (!$GLOBALS['perm']->have_perm("root")) {
            return;
        }
        if (stripos($_SERVER['REQUEST_URI'], "dispatch.php/start") !== false) {
            $versions = $this->getVersions();
            $new_version = false;
            $new_service_release = false;
            foreach ($versions as $number => $version) {
                if ($new_version === false && version_compare($number, $GLOBALS['SOFTWARE_VERSION'], ">")) {
                    $new_version = $number;
                }
                if (($new_service_release === false)
                        && (substr($number, 0, -1) === substr($GLOBALS['SOFTWARE_VERSION'], 0, -1))
                        && version_compare($number, $GLOBALS['SOFTWARE_VERSION'], ">")) {
                    $new_service_release = $number;
                }
            }
            if ($new_service_release) {
                PageLayout::postMessage(MessageBox::info(sprintf(_("Service Release %s ist verfügbar. Bitte updaten Sie so schnell wie möglich."), $new_service_release)));
            }
            if ($new_version) {
                PageLayout::postMessage(MessageBox::info(sprintf(_("Neue Stud.IP Version %s ist verfügbar."), $new_version)));
            }
        }
    }

    protected function getVersions() {
        $atom = DOMDocument::loadXML(file_get_contents(self::$rssURL));
        $versions = array();
        foreach ($atom->getElementsByTagName("item") as $item) {
            $version_value = array();
            foreach ($item->childNodes as $child) {
                $version_value[$child->nodeName] = $child->nodeValue;
            }
            if (stripos($version_value['title'], ".zip") !== false) {

                preg_match("/studip\-([\d\.]*?)\.zip/", $version_value['title'], $matches);
                $version = $matches[1];
                if ($version) {
                    $versions[$version] = $version_value;
                }
            }
        }
        return $versions;
    }
}