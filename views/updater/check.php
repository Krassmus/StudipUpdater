<? if (!$release_notes) : ?>
    <?= MessageBox::error(_("Es wurde in der ZIP Datei kein Changelog gefunden. Wom�glich ist sie fehlerhaft oder kein echtes Stud.IP.")) ?>
<? else : ?>
    <?= MessageBox::success(_("Eine neue Version ist da!")) ?>
    <div id="release_notes">
        <h2><?= _("Changelog der hochgeladenen Version") ?></h2>
        <?= formatReady($release_notes) ?>
    </div>

    <? if (!count($is_not_writable)) : ?>
        <div>
            <h2><?= _("Was passiert beim Klick auf den Update-Ausf�hren Button?") ?></h2>
            <ul>
                <li><?= _("Ein neues Stud.IP-Fenster �ffnet sich (in einem neuen Reiter).") ?></li>
                <li><?= _("Die Stud.IP PHP-Programmdateien werden im Hintergrund ersetzt.") ?></li>
                <li><?= _("Dann fehlen aber noch die Migrationen in der Datenbank. In dem neuen Fenster wird Seite web_migrate.php dargestellt, auf der Sie die Datenbankmigration von Hand ansto�en sollen. Diese Migrationen k�nnen auch etwas l�nger dauern. Keine Bange, das ist okay so.") ?></li>
                <li><?= _("Schlie�en Sie jetzt das neue Fenster, sodass Sie wieder hier her zur�ckkehren.") ?></li>
            </ul>
            <form action="<?= PluginEngine::getLink($plugin, array(), "updater/execute") ?>" method="post" target="_blank">
                <?= \Studip\Button::create(_("Update ausf�hren!"), "execute_update", array('return window.confirm("'._("Wirklich das Update durchf�hren?").'");')) ?>
            </form>
        </div>
    <? endif ?>
<? endif ?>