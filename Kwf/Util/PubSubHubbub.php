<?php
class Kwf_Util_PubSubHubbub
{
    public static function dispatch()
    {
        $url = Kwf_Setup::getRequestPath();
        self::process();
        exit;
    }

    public static function process()
    {
        $log = print_r($_SERVER, true);
        $log .= "-----------------------\n";
        $log .= print_r($_POST, true);
        $log .= "-----------------------\n";
        $log .= print_r($_GET, true);
        $log .= "-----------------------\n";
        $log .= file_get_contents("php://input");
        //file_put_contents('log/pshb_cb'.date('Y-m-d_H:i:s'), $log);

        if (isset($_GET['hub_challenge'])) {
            //TODO: check if this is real - 404 if not
            echo $_GET['hub_challenge'];
        } else {
            header('X-Hub-On-Behalf-Of: 1'); //enter num of boxes here
            echo "OK for now";
        }
    }
}
