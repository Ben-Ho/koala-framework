<div class="vpsListSwitch <?=$this->cssClass?>">
    <input type="hidden" class="options" value="<?= str_replace("\"", "'", Zend_Json::encode($this->options)) ?>" />
    <div class="listSwitchLargeWrapper">
        <div class="listSwitchLargeContent">
            <? foreach ($this->children as $child) {
                // diese ausgabe ist nur um flackern zu unterbinden. könnte
                // auch entfernt werden, da das bild sowieso vom javascript
                // nochmal gesetzt wird.
            ?>
                <?= $this->component($child->getChildComponent('-large'));
                break; ?>
            <? } ?>
        </div>
        <a href="#" class="listSwitchPrevious"><?=$this->placeholder['prev'];?></a>
        <a href="#" class="listSwitchNext"><?=$this->placeholder['next'];?></a>
        <div class="clear"></div>
    </div>

    <div class="listSwitchPreviewWrapper <?=$this->previewCssClass?>">
        <? $i = 0; ?>
        <? foreach ($this->children as $child) { ?>
            <?
                $class = '';
                if ($i == 0) $class .= 'vpcFirst ';
                if ($i == count($this->children)-1) $class .= 'vpcLast ';
                $class = trim($class);
                $i++;
            ?>
            <div class="listSwitchItem <?= $class; ?>">
                <a href="#" class="previewLink"><?=$this->component($child);?></a>
                <div class="largeContent"><?= $this->component($child->getChildComponent('-large')); ?></div>
            </div>
        <? } ?>
        <div class="clear"></div>
    </div>
</div>
