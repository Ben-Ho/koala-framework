<?= $this->data->trlKwf("Hello")?>,<br /><br />
<?=$this->data->trlKwf("a post has been reported for the following reason:") ?><br />
<hr />
<i><?= nl2br(htmlspecialchars($this->reason)) ?></i><br />
<hr />
<br />
<a href="<?= $this->url ?>"><?= $this->url ?></a><br />
<?= $this->data->trlKwf("Open the post or read it directly:") ?><br />
<hr />
<i><?= $this->htmlContent ?></i><br />
<hr /><br />
