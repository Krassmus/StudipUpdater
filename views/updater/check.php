<? if (!$release_notes) : ?>
    <?= MessageBox::error(_("Es wurde in der ZIP Datei kein Changelog gefunden. Womöglich ist sie fehlerhaft oder kein echtes Stud.IP.")) ?>
<? else : ?>
    <?= MessageBox::success(_("Eine neue Version ist da!")) ?>
    <div id="release_notes">
        <h2><?= _("Changelog der hochgeladenen Version") ?></h2>
        <?= formatReady($release_notes) ?>
    </div>

    <? if (!count($is_not_writable)) : ?>
        <div>
            <h2><?= _("Was passiert beim Klick auf den Update-Ausführen Button?") ?></h2>
            <ul>
                <li><?= _("Die Stud.IP PHP-Programmdateien werden im Hintergrund ersetzt.") ?></li>
                <li><?= _("Ein neues Fenster öffnet sich (ein neuer Browser-Tab), wo Sie die Datenbank migrieren können.") ?></li>
                <li><?= _("Danach ist das System geupdated und brandneu!") ?></li>
                <li><?= _("Eventuell sind bei dem Update einige Plugins deaktiviert worden, die nicht kompatibel zu sein scheinen. Gehen Sie im Anschluss an das Update in die Pluginverwaltung und updaten Sie die betroffenen Plugins und aktivieren Sie diese wieder.") ?></li>
            </ul>
            <form action="<?= PluginEngine::getLink($plugin, array(), "updater/execute") ?>" method="post" target="_blank">
                <?= \Studip\Button::create(_("Update ausführen!"), "execute_update", array('return window.confirm("'._("Wirklich das Update durchführen?").'");')) ?>
            </form>

            <h2><?= _("Was kann man machen, falls etwas schief gehen sollte?") ?></h2>
            <ul>
                <li><?= _("Normalerweise sollte nichts schief gehen. Aber man sollte niemals nie sagen. Führen Sie am besten vor dem Update immer eine Sicherung des gesamten Systems durch.") ?></li>
                <li><?= sprintf(_("Wenn doch noch etwas schief gehen sollte, gehen Sie auf die Seite %sweb_migrate.php%s und führen Sie dort die Migrationen von Hand aus."), '<a href="'.URLHelper::getLink("web_migrate.php").'" target="_blank">', '</a>') ?></li>
                <li><?= _("Wenn das nicht möglich sein sollte oder es nichts hilft, melden Sie sich bei der Stud.IP-Community auf https://develop.studip.de und fragen dort nach Rat.") ?></li>
                <li><?= _("Letztenendes wird es auch helfen, eine vorher durchgeführte Sicherung wieder einzuspielen.") ?></li>
            </ul>
        </div>
    <? endif ?>
<? endif ?>