<?php
class Vps_Component_Output_NoCache extends Vps_Component_Output_Abstract
{
    protected function _render($componentId, $componentClass, $masterTemplate = false, array $plugins = array())
    {
        return $this->_processComponent($componentId, $componentClass, $masterTemplate, $plugins);
    }
    
    protected function _processComponent($componentId, $componentClass, $masterTemplate = false, array $plugins = array())
    {
        $ret = $this->_renderContent($componentId, $componentClass, $masterTemplate);

        foreach ($plugins as $p) {
            if (!$p) throw new Vps_Exception("Invalid Plugin specified '$p'");
            $p = new $p($componentId);
            $ret = $p->processOutput($ret);
        }
        
        $ret = $this->_parseTemplate($ret);
        return $ret;
    }
    
    protected function _parseTemplate($ret)
    {
        // hasContent-Tags ersetzen
        preg_match_all("/{content: ([^ }]+) ([^ }]*)}(.*){content}/imsU", $ret, $matches);
        foreach ($matches[0] as $key => $search) {
            $componentId = $matches[2][$key];
            $componentClass = $matches[1][$key];
            $content = $matches[3][$key];
            $replace = $this->_renderHasContent($componentId, $componentClass, $content);
            $ret = str_replace($search, $replace, $ret);
        }
        
        // nocache-Tags ersetzen
        preg_match_all('/{nocache: ([^ }]+) ([^ }]*) ?([^}]*)}/', $ret, $matches);
        foreach ($matches[0] as $key => $search) {
            $componentId = $matches[2][$key];
            $componentClass = $matches[1][$key];
            $plugins = $matches[3][$key] ? explode(' ', trim($matches[3][$key])) : array();
            $replace = $this->_processComponent($componentId, $componentClass, false, $plugins);
            $ret = str_replace($search, $replace, $ret);
        }
        return $ret;
    }
    
    protected function _renderHasContent($componentId, $componentClass, $content)
    {
        Vps_Benchmark::count('rendered nocache', $componentId.' (hasContent)');
        $component = $this->_getComponent($componentId);
        return $component->hasContent() ? $content : '';
    }
    
    protected function _renderContent($componentId, $componentClass, $masterTemplate)
    {
        Vps_Benchmark::count('rendered nocache', $componentId.($masterTemplate?' (master)':''));
        if ($masterTemplate) {
            $output = new Vps_Component_Output_Master();
        } else {
            $output = new Vps_Component_Output_ComponentMaster();
        }
        $output->setIgnoreVisible($this->ignoreVisible());
        return $output->render($this->_getComponent($componentId));
    }
}
