<div class="<?=$this->cssClass?>">
    <p>
        <strong><?=trlVps('Your useraccount was successfully activated.')?></strong>
    </p>
    <p>
        <?=trlVps('You were logged in, automatically')?><br />
        <a href="/"><?=trlVps('Click here')?></a>, <?=trlVps('to get back to the Startpage')?>.

        <script type="text/javascript">
            window.setTimeout("window.location.href = '/'", 3000);
        </script>
    </p>
</div>