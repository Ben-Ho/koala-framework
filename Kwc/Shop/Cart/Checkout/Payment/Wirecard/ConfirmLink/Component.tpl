<div class="<?=$this->cssClass?>">
    <input type="hidden" value="<?=htmlspecialchars(Zend_Json::encode($this->options))?>" />
    <?=$this->wirecardButton?>
    <div class="process"></div>
</div>
