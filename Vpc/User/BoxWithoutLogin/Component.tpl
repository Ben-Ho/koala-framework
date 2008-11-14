<div class="<?=$this->cssClass?>">
<? if ($this->authedUser) { ?>
    <div class="account">
        <ul>
            <li>
                <?=$this->authedUser->email?>
            </li>
            <li>
                <?=$this->componentLink($this->myProfile, trlVps('My Profile'))?>
            </li>
            <? foreach ($this->links as $l) { ?>
                <li><?=$this->componentLink($l)?></li>
            <? } ?>
            <li class="logout"><a href="?logout"><?=trlVps('Logout')?></a></li>
        </ul>
    </div>
<? } else { ?>
    <ul>
        <li><?=$this->componentLink($this->login, trlVps('Login'))?><?=$this->linkPostfix?></li>
        <li class="register"><?=$this->componentLink($this->register, trlVps('Register'))?><?=$this->linkPostfix?></li>
        <? if ($this->lostPassword) { ?>
        <li><?=$this->componentLink($this->lostPassword, trlVps('Lost password'))?><?=$this->linkPostfix?></li>
        <? } ?>
    </ul>
<? } ?>
</div>
