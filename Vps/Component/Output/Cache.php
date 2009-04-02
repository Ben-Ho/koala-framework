<?php
class Vps_Component_Output_Cache extends Vps_Component_Output_NoCache
{
    private $_cache;
    private $_toLoadHasContent = array();
    private $_toLoad = array();
    private $_toLoadPartial = array();

    /**
     * @return Vps_Component_Cache
     */
    public function getCache()
    {
        return Vps_Component_Cache::getInstance();
    }

    public function render($component, $masterTemplate = false, array $plugins = array())
    {
        // Erste Komponente vorausladen
        $this->getCache()->preload(array($component->componentId));
        // Normal rendern
        $ret = parent::render($component, $masterTemplate, $plugins);
        $this->getCache()->writeBuffer();
        return $ret;
    }

    protected function _render($componentId, $componentClass, $masterTemplate = false, array $plugins = array())
    {
        $ret = $this->_processComponent($componentId, $componentClass, $masterTemplate, $plugins);
        return $this->_processComponent2($ret);
    }

    protected function _processComponent2($ret)
    {
        // Übergebene Ids preloaden
        $preloadIds = array();
        $toLoadHasContent = $this->_toLoadHasContent;
        $this->_toLoadHasContent = array();
        $toLoad = $this->_toLoad;
        $this->_toLoad = array();
        $toLoadPartial = $this->_toLoadPartial;
        $this->_toLoadPartial = array();
        foreach ($toLoadHasContent as $val) {
            $preloadIds[] = $this->getCache()->getCacheId(
                $val['componentId'],
                Vps_Component_Cache::TYPE_HASCONTENT,
                $val['counter']
            );
        }
        foreach ($toLoadPartial as $val) {
            $preloadIds[] = $this->getCache()->getCacheId(
                $val['componentId'],
                Vps_Component_Cache::TYPE_PARTIAL,
                $val['nr']
            );
        }
        foreach ($toLoad as $val) {
            $type = $val['masterTemplate'] ? Vps_Component_Cache::TYPE_MASTER : Vps_Component_Cache::TYPE_DEFAULT;
            $preloadIds[] = $this->getCache()->getCacheId($val['componentId'], $type);
        }

        if ($preloadIds) {
            $this->getCache()->preload($preloadIds);
        }

        // Nochmal durchgehen und ersetzen
        foreach ($toLoadHasContent as $search => $val) {
            $content = $this->_renderHasContent($val['componentId'], $val['componentClass'], $val['content'], $val['counter']);
            $childRenderData = $this->_parseTemplate($content);
            $replace = $this->_processComponent2($childRenderData);
            $ret = str_replace($search, $replace, $ret);
        }
        foreach ($toLoadPartial as $search => $val) {
            $content = $this->_renderPartial($val['componentId'], $val['componentClass'], $val['partial'], $val['nr'], $val['info']);
            $childRenderData = $this->_parseTemplate($content);
            $replace = $this->_processComponent2($childRenderData);
            $ret = str_replace($search, $replace, $ret);
        }
        foreach ($toLoad as $search => $val) {
            $replace = $this->_render($val['componentId'], $val['componentClass'], $val['masterTemplate']);
            $ret = str_replace($search, $replace, $ret);
        }
        return $this->_parseTemplate($ret);
    }

    protected function _renderPartial($componentId, $componentClass, $partial, $id, $info)
    {
        $ret = false;
        $cacheId = $this->getCache()->getCacheId($componentId, Vps_Component_Cache::TYPE_PARTIAL, $id);

        if ($this->getCache()->isLoaded($cacheId)) {
            Vps_Benchmark::count('rendered partial cache', $cacheId);
            $ret = $this->getCache()->load($cacheId);
        } else if ($this->getCache()->shouldBeLoaded($cacheId)) {
            $settings = $this->_getComponent($componentId)->getComponent()->getViewCacheSettings();
            $ret = parent::_renderPartial($componentId, $componentClass, $partial, $id, $info, $settings['enabled']);
            if ($settings['enabled']) {
                $this->getCache()->save($ret, $cacheId, $componentClass, $settings['lifetime']);
                $this->_saveMeta($componentId, $cacheId, $id);
            }
        } else {
            $ret = "{partial: $componentId $id}";
            $this->_toLoadPartial[$ret] = array(
                'componentClass' => $componentClass,
                'componentId' => $componentId,
                'partial' => $partial,
                'nr' => $id,
                'info' => $info
            );
        }
        return $ret;
    }

    protected function _renderContent($componentId, $componentClass, $masterTemplate)
    {
        $ret = false;
        $type = $masterTemplate ? Vps_Component_Cache::TYPE_MASTER : Vps_Component_Cache::TYPE_DEFAULT;
        $cacheId = $this->getCache()->getCacheId($componentId, $type);

        if ($this->getCache()->isLoaded($cacheId)) {
            Vps_Benchmark::count('rendered cache', $cacheId);
            $ret = $this->getCache()->load($cacheId);
        } else if ($this->getCache()->shouldBeLoaded($cacheId)) {
            $settings = $this->_getComponent($componentId)->getComponent()->getViewCacheSettings();
            $ret = parent::_renderContent($componentId, $componentClass, $masterTemplate, $settings['lifetime']);
            if ($settings['enabled']) {
                $this->getCache()->save($ret, $cacheId, $componentClass, $settings['lifetime']);
                $this->_saveMeta($componentId, $cacheId);
            }
        } else {
            $ret = "{empty: $componentId}";
            $this->_toLoad[$ret] = array(
                'componentClass' => $componentClass,
                'componentId' => $componentId,
                'masterTemplate' => $masterTemplate
            );
        }
        return $ret;
    }

    protected function _renderHasContent($componentId, $componentClass, $content, $counter)
    {
        // Komponente aus Cache holen
        $ret = false; // Falls nicht in Cache und sollte noch nicht geladen sein, kann auch false zurückgegeben werden
        $cacheId = $this->getCache()->getCacheId($componentId, Vps_Component_Cache::TYPE_HASCONTENT, $counter);

        if ($this->getCache()->isLoaded($cacheId)) { // Wurde bereits preloaded
            Vps_Benchmark::count('rendered cache', $cacheId);
            $ret = $this->getCache()->load($cacheId);
        } else if ($this->getCache()->shouldBeLoaded($cacheId)) { // Nicht in Cache, aber sollte in Cache sein -> ohne Cache holen
            $settings = $this->_getComponent($componentId)->getComponent()->getViewCacheSettings();
            $ret = parent::_renderHasContent($componentId, $componentClass, $content, $counter, $settings['enabled']);
            if ($settings['enabled']) {
                $this->getCache()->save($ret, $cacheId, $componentClass, $settings['lifetime']);
                $this->_saveMeta($componentId, $cacheId);
            }
        } else {
            $ret = "{hasContent " . $componentId . '#' . $counter . "}";
            $this->_toLoadHasContent[$ret] = array(
                'componentId' => $componentId,
                'componentClass' => $componentClass,
                'content' => $content,
                'counter' => $counter
            );
        }

        return $ret;
    }

    private function _saveMeta($componentId, $cacheId, $partial = false)
    {
        $component = $this->_getComponent($componentId);
        if ($partial === false) {
            $meta = $component->getComponent()->getCacheVars();
        } else {
            $meta = $component->getComponent()->getPartialCacheVars($partial);
        }
        foreach ($meta as $m) {
            if (!isset($m['model'])) throw new Vps_Exception('getCacheVars for ' . $component->componentClass . ' must deliver model');
            $model = $m['model'];
            $id = isset($m['id']) ? $m['id'] : null;
            if (isset($m['callback']) && $m['callback']) {
                $type = Vps_Component_Cache::META_CALLBACK;
                $value = $componentId;
            } else {
                $type = Vps_Component_Cache::META_CACHE_ID;
                $value = $cacheId;
            }
            if (isset($m['componentId'])) {
                $value = $this->getCache()->getCacheId($m['componentId']);
            }
            if (!isset($m['field'])) {
                $m['field'] = Vps_Component_Cache::META_FIELD_PRIMARY;
            }
            $this->getCache()->saveMeta($model, $id, $value, $type, $m['field']);
        }
        return $meta;
    }
}
