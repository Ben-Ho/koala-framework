<div class="<?=$this->cssClass?>">
<? if($this->data->hasContent()) { ?>

    <? if ($this->text) { ?>
        <div class="text">
        <? if ($this->text instanceof Vps_Component_Data) { ?>
        <?php echo $this->component($this->text) ?>
        <? } else { ?>
        <?php echo $this->text ?>
        <? } ?>
        <br />
        </div>
    <? } ?>

    <input type="hidden" class="options" value="<?= str_replace("\"", "'", Zend_Json::encode($this->options)) ?>" />

    <? /* height wird benötigt wenn gmap innerhalb von switchDisplay liegt*/ ?>
    <div class="container" style="height: <?= $this->height; ?>px;"></div>

    <? if ($this->options['routing']) { ?>
        <form action="#" class="fromAddress printHidden">
            <input type="text" class="textBefore vpsClearOnFocus" value="<?= trlVps('Place of departure: zip code, Town, Street'); ?>" />
            <input type="submit" value="<?= trlVps('Show Route') ?>" class="submitOn"/>
        </form>
    <? } ?>

    <div class="mapDirSuggestParent">
        <b><?= trlVps('Suggestions') ?></b>
        <ul class="mapDirSuggest"></ul>
    </div>

    <div class="mapDir"></div>
<? } else { ?>
    <?=$this->placeholder['noCoordinates']?>
<? } ?>
</div>

