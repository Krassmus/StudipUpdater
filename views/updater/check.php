<? if (!$release_notes) : ?>
    <?= MessageBox::error(_("Es wurde in der ZIP Datei kein Changelog gefunden. Womöglich ist sie fehlerhaft oder kein echtes Stud.IP.")) ?>
<? else : ?>
    <?= MessageBox::success(_("Upload erfolgreich!")) ?>
    <div id="release_notes">
        <h2><?= _("Changelog der hochgeladenen Version") ?></h2>
        <?= formatReady($release_notes) ?>
    </div>
<? endif ?>