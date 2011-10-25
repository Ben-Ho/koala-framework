<div class="<?=$this->cssClass?>">
    <h3>Name</h3>
    <?=$this->row->title . ' ' . $this->row->firstname . ' ' . $this->row->lastname?>

    <h3><?=trlKwf('Member since')?></h3>
    <?=$this->date($this->row->created)?>

    <? if (isset($this->row->last_login_web)) { ?>
        <h3><?=trlKwf('Latest online')?></h3>
        <?=$this->timestamp($this->row->last_login_web)?>
    <? } ?>

</div>