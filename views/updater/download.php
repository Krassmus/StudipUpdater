<form action="<?= PluginEngine::getLink($plugin, array(), "updater/download") ?>" method="post" enctype="multipart/form-data" style="text-align: center;">

    <? if ($service_release) : ?>
        <div>
            <?= \Studip\Button::create(sprintf(_("Service-Release %s herunterladen."), $service_release), "service_release") ?>
        </div>
    <? endif ?>

    <? if ($release) : ?>
        <div>
            <?= \Studip\Button::create(sprintf(_("Release %s herunterladen."), $release), "release") ?>
        </div>
    <? endif ?>


    <label style="cursor: pointer; display: inline-block; background-color: #e7ebf1; padding: 20px; border-radius: 20px; margin: 20px;">
        <input type="hidden" name="test" value="1">
        <input type="file" name="new_studip" style="display: none;">
        <?= Assets::img("icons/40/blue/upload", array('class' => "text-bottom")) ?>
        <div>
            <?= _("Andere Stud.IP-Version hochladen") ?>
        </div>
        <?= class_exists("\\Studip\\Button") ? \Studip\Button::create(_("hochladen")) : makebutton("hochladen") ?>
    </label>


</form>