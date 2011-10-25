<?php
class Kwf_Component_Output_Plugin_PluginAfter extends Kwf_Component_Output_Plugin_Plugin
{
    public function processOutput($output)
    {
        // Da das Plugin nach dem Rendern ausgeführt wird, muss schon der
        // fertige Content hier reinkommen
        if ($output != 'root plugin(plugin(child child2))') {
            return "not ok from plugin";
        } else {
            return "afterPlugin($output)";
        }
    }
    public function getExecutionPoint()
    {
        return Kwf_Component_Plugin_Interface_View::EXECUTE_AFTER;
    }
}
?>