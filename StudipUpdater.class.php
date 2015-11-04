<?php


class StudipUpdater extends StudIPPlugin implements SystemPlugin {

    static protected $rssURL = "http://sourceforge.net/projects/studip/rss?path=/Stud.IP";

    public function __construct() {
        parent::__construct();
        if (!$GLOBALS['perm']->have_perm("root")) {
            return;
        }
        $tab = new Navigation(_("Stud.IP Update"), PluginEngine::getURL($this, array(), "updater/precheck"));
        Navigation::addItem("/tools/studipupdate", $tab);
        if ((stripos($_SERVER['REQUEST_URI'], "dispatch.php/start") !== false ) || (stripos($_SERVER['REQUEST_URI'], "index.php") !== false)) {
            $new_version = $this->getVersion(false);
            $new_service_release = $this->getVersion(true);
            if ($new_service_release) {
                $message = MessageBox::info(
                    sprintf(_("Service Release %s ist verfügbar. Bitte updaten Sie so schnell wie möglich."), $new_service_release),
                    array(
                        '<form action="'.PluginEngine::getLink($this, array(), "updater/download").'" method="post">'.\Studip\Button::create(_("Jetzt installieren"), 'service_release', array('value' => $new_service_release)).'</form>',
                    )
                );
                if (stripos($_SERVER['REQUEST_URI'], "index.php") !== false) {
                    PageLayout::addBodyElements($message);
                } else {
                    PageLayout::postMessage($message);
                }
            }
            if ($new_version) {
                $message = MessageBox::info(sprintf(
                    _("Neue Stud.IP Version %s ist verfügbar."), $new_version),
                    array(
                        '<form action="'.PluginEngine::getLink($this, array(), "updater/download").'" method="post">'.\Studip\Button::create(_("Jetzt installieren"), 'release', array('value' => $new_version)).'</form>',
                    )
                );
                if (stripos($_SERVER['REQUEST_URI'], "index.php") !== false) {
                    PageLayout::addBodyElements($message);
                } else {
                    PageLayout::postMessage($message);
                }
            }
        }
    }

    public function getVersion($service_release = true)
    {
        $versions = $this->getVersions();
        $new_version = false;
        $new_service_release = false;
        foreach ($versions as $number => $version) {
            $my_version = explode(".", $GLOBALS['SOFTWARE_VERSION']);
            $their_version = explode(".", $number);
            if ($new_version === false
                && (($my_version[0] !== $their_version[0])
                    || ($my_version[1] !== $their_version[1]))
                && version_compare($number, $GLOBALS['SOFTWARE_VERSION'], ">")) {
                $new_version = $number;
            }
            if (($new_service_release === false)
                && ($my_version[0] == $their_version[0])
                && ($my_version[1] == $their_version[1])
                && version_compare($number, $GLOBALS['SOFTWARE_VERSION'], ">")) {
                $new_service_release = $number;
            }
        }
        if ($service_release) {
            return $new_service_release;
        } else {
            return $new_version;
        }
    }

    protected function getVersions() {
        $versions = unserialize(StudipCacheFactory::getCache()->read("STUDIPUPDATER-sourceforgeversions"));
        if ($versions) {
            return $versions;
        } else {
            $rss = @file_get_contents(self::$rssURL);
            if ($rss) {
                $atom = DOMDocument::loadXML($rss);
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
                StudipCacheFactory::getCache()->write("STUDIPUPDATER-sourceforgeversions", serialize($versions), 60);
                return $versions;
            } else {
                return array();
            }
        }
    }

}