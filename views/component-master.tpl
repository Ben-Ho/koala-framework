<?= $this->doctype('XHTML1_STRICT') ?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?= $this->assets('Frontend') ?>
        <?= $this->debugData() ?>
    </head>
    <body class="frontend">
        <?= $this->component($this->component) ?>
    </body>
</html>
