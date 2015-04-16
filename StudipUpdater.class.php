<?php

class StudipUpdater extends StudIPPlugin implements SystemPlugin {

    static protected $rssURL = "http://sourceforge.net/projects/studip/rss?path=/Stud.IP";

    public function __construct() {
        parent::__construct();
        if (!$GLOBALS['perm']->have_perm("root")) {
            return;
        }
        if (stripos($_SERVER['REQUEST_URI'], "dispatch.php/start") !== false) {
            $this->getVersions();
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