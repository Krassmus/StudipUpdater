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
            <li><?= _("Ein neues Stud.IP-Fenster öffnet sich (in einem neuen Reiter).") ?></li>
            <li><?= _("Die Stud.IP PHP-Programmdateien werden im Hintergrund ersetzt.") ?></li>
            <li><?= _("Dann fehlen aber noch die Migrationen in der Datenbank. In dem neuen Fenster wird Seite web_migrate.php dargestellt, auf der Sie die Datenbankmigration von Hand anstoßen sollen. Diese Migrationen können auch etwas länger dauern. Keine Bange, das ist okay so.") ?></li>
            <li><?= _("Schließen Sie jetzt das neue Fenster, sodass Sie wieder hier her zurückkehren.") ?></li>
        </ul>
        <form action="<?= PluginEngine::getLink($plugin, array(), "updater/execute") ?>" method="post" target="_blank">
            <?= \Studip\Button::create(_("Update ausführen!"), "execute_update", array('return window.confirm("'._("Wirklich das Update durchführen?").'");')) ?>
        </form>
    </div>
<? endif ?>