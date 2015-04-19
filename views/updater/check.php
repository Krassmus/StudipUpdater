<? if (!$release_notes) : ?>
    <?= MessageBox::error(_("Es wurde in der ZIP Datei kein Changelog gefunden. Womöglich ist sie fehlerhaft oder kein echtes Stud.IP.")) ?>
<? else : ?>
    <?= MessageBox::success(_("Upload erfolgreich!")) ?>
    <div id="release_notes">
        <h2><?= _("Changelog der hochgeladenen Version") ?></h2>
        <?= formatReady($release_notes) ?>
    </div>

    <div>
        <h2><?= _("Was passiert beim Klick auf den Update-Ausführen Button?") ?></h2>
        <ul>
            <li><?= _("Die Stud.IP PHP-Programmdateien werden ersetzt.") ?></li>
            <li><?= _("Dann fehlen aber noch die Migrationen in der Datenbank. Sie werden sofort auf der Seite web_migrate.php landen, wo Sie die Datenbankmigration von Hand anstoßen werden. Diese Migrationen können auch etwas länger dauern. Keine Bange, das ist okay so.") ?></li>
            <li><?= _("Danach ist Ihr System geupdated!") ?></li>
        </ul>
        <form action="<?= PluginEngine::getLink($plugin, array(), "updater/execute") ?>" method="post">
            <?= \Studip\Button::create(_("Update ausführen!"), "execute_update", array('return window.confirm("'._("Wirklich das Update durchführen?").'");')) ?>
        </form>
    </div>
<? endif ?>