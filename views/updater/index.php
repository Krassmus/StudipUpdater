<form action="<?= PluginEngine::getLink($plugin, array(), "updater/check") ?>" method="post" enctype="multipart/form-data" style="text-align: center;">
    <label style="cursor: pointer; display: inline-block; background-color: #e7ebf1; padding: 20px; border-radius: 20px; margin: 20px;">
        <input type="hidden" name="test" value="1">
        <input type="file" name="new_studip" style="display: none;">
        <?= Assets::img("icons/40/blue/upload", array('class' => "text-bottom")) ?>
        <div>
            <?= _("ZIP mit neuer Stud.IP-Version hochladen") ?>
        </div>
        <?= \Studip\Button::create(_("hochladen")) ?>
    </label>
</form>