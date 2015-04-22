<div class="content">
    <h1>
        <? if ($this->isActivate) { ?>
            <?=trlKwf('Activate Useraccount')?>
        <? } else { ?>
            <?=trlKwf('Reset Password')?>
        <? } ?>
    </h1>
    <p>
        <?=trlKwf('Your email address:')?> <strong><?=$this->email?></strong>
    </p>

    <?=$this->errorsHtml?>

    <? if ($this->redirects) { ?>
        <p><?=trlKwf('Activate with:')?></p>
        <ul>
        <?php foreach ($this->redirects as $r) { ?>
            <li>
                <form method="GET" action="<?=htmlspecialchars($r['url'])?>">
                <input type="hidden" name="authMethod" value="<?=htmlspecialchars($r['authMethod'])?>" />
                <input type="hidden" name="redirect" value="<?=htmlspecialchars($r['redirect'])?>" />
                <?=$r['formOptions']?>
                <button>
                    <? if ($r['icon']) { ?>
                        <img src="<?=htmlspecialchars($r['icon'])?>" />
                    <? } else { ?>
                        <?=htmlspecialchars($r['name'])?>
                    <? } ?>
                </button>
                </form>
            </li>
        <?php } ?>
        </ul>
    <? } ?>
</div>
