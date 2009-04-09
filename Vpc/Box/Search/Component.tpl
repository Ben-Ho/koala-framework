<div class="<?=$this->cssClass?>">
    <form action="<?= $this->searchPageUrl; ?>" method="get" autocomplete="off">
        <input type="hidden" class="ajaxUrl" value="<?= $this->ajaxUrl; ?>" />
        <input type="text" name="<?=$this->queryParam?>" class="searchField vpsClearOnFocus"
            value="<?= $this->placeholder['clearOnFocus']; ?>" autocomplete="off" />
        <input type="hidden" class="submitParam" name="<?=$this->submitParam?>" value="submit" autocomplete="off" />
        <button type="submit" class="submit"><?= $this->placeholder['searchButton']; ?></button>
    </form>
    <div class="searchResult">
        <?= $this->placeholder['initialResultText']; ?>
    </div>
</div>
