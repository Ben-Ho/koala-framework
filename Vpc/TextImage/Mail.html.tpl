<table width="100%" cellspacing="0" cellpadding="0">
    <? if($this->position=='left') { ?>
        <tr>
            <? if ($this->image) { ?>
                <td><?=$this->component($this->image)?></td>
                <td width="5">&nbsp;</td>
            <? } ?>
            <td valign="<?= $this->mailImageVAlign?>"><?=$this->component($this->text)?></td>
        </tr>
    <? } else { ?>
        <tr>
            <td valign="top"><?=$this->component($this->text)?></td>
            <? if ($this->image) { ?>
                <td width="5">&nbsp;</td>
                <td align="right" valign="<?= $this->mailImageVAlign?>"><?=$this->component($this->image)?></td>
            <? } ?>
        </tr>
    <? } ?>
</table>
