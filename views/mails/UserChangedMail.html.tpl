<?= $this->data->trlKwf('Hello {0}!', $this->fullname); ?><br /><br />

<?= $this->data->trlKwf('Your email address at {0} has been changed.', '<a href="'.$this->webUrl.'">'.$this->webUrl.'</a>'); ?><br />
<?= $this->data->trlKwf('Your old email address was {0}, the new one is {1}', array($this->oldMail, $this->userData['email'])); ?><br /><br />

<?= $this->applicationName; ?><br /><br />

--<br />
<?= $this->data->trlKwf('This email has been generated automatically. There may be no recipient if you answer to this email.'); ?>
