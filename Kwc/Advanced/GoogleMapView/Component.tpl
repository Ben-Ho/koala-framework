<div class="<?=$this->rootElementClass?><?php if ($this->optimizedMobileUI) { ?> optimizedMobileUI<?php } ?>">
<?php if($this->data->hasContent()) { ?>
    <div class="mobileOverlayOpen">
        <div class="innerMobileOverlay">
            <span class="tapToNav"><?=$this->data->trlKwf('Tap to navigate');?></span>
        </div>
    </div>

    <div class="mobileOverlayClose">
        <div class="innerMobileOverlay">
            <span class="tapToScroll"><?=$this->data->trlKwf('Close');?></span>
        </div>
    </div>

    <?php if($this->data->hasContent()) { ?>

        <?php if ($this->text && ($this->text instanceof Kwf_Component_Data)) { ?>
            <?php if ($this->hasContent($this->text)) { ?>
                <div class="text">
                    <?= $this->component($this->text); ?>
                </div>
            <?php } ?>
        <?php } else if ($this->text) { ?>
            <div class="text">
                <?= $this->text; ?>
            </div>
        <?php } ?>

        <input type="hidden" class="options" value="<?= htmlspecialchars(Zend_Json::encode($this->options)) ?>" />

        <?php /* height wird benötigt wenn gmap innerhalb von switchDisplay liegt*/ ?>
        <div class="container" style="height: <?= $this->height; ?>px;"></div>

        <?php if ($this->options['routing']) { ?>
            <form action="#" class="fromAddress">
                <input type="text" class="textBefore kwfUp-kwfClearOnFocus" placeholder="<?= $this->data->trlKwf('Place of departure: zip code, Town, Street'); ?>" />
                <button class="submitOn"><?= $this->data->trlKwf('Show Route') ?></button>
                <div class="kwfUp-clear"></div>
            </form>
        <?php } ?>

        <div class="mapDirSuggestParent">
            <b><?= $this->data->trlKwf('Suggestions') ?></b>
            <ul class="mapDirSuggest"></ul>
        </div>

        <div class="mapDir"></div>
    <?php } else { ?>
        <?=$this->placeholder['noCoordinates']?>
    <?php } ?>
<?php } ?>
</div>

