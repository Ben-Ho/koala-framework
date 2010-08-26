<?php
class Vpc_Basic_DownloadTag_Component extends Vpc_Basic_LinkTag_Abstract_Component
    implements Vps_Media_Output_IsValidInterface
{
    public static function getSettings()
    {
        $ret = array_merge(parent::getSettings(), array(
            'ownModel'     => 'Vpc_Basic_DownloadTag_Model',
            'componentName' => trlVps('Download'),
            'componentIcon' => new Vps_Asset('folder_link'),
        ));
        $ret['dataClass'] = 'Vpc_Basic_DownloadTag_Data';
        $ret['assetsAdmin']['dep'][] = 'VpsSwfUpload';
        $ret['assetsAdmin']['files'][] = 'vps/Vpc/Basic/DownloadTag/Panel.js';
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();

        $row = $this->_getRow();
        $filename = $row->filename != '' ? $row->filename : 'unnamed';


        $ret['filesize'] = $this->getFilesize();
        $ret['url'] = $this->getDownloadUrl();
        $ret['filename'] = $filename;
        return $ret;
    }

    public function hasContent()
    {
        $row = $this->getFileRow();
        if ($row && $row->vps_upload_id) {
            return true;
        }
        return false;
    }

    public function getDownloadUrl()
    {
        return $this->getData()->url;
    }

    public function getFilesize()
    {
        $fRow = $this->getFileRow()->getParentRow('File');
        if (!$fRow) return null;
        return $fRow->getFileSize();
    }

    public function getFileRow()
    {
        return $this->_getRow();
    }

    public static function isValidMediaOutput($id, $type, $className)
    {
        if (Vps_Component_Data_Root::getInstance()->getComponentById($id)) {
            return self::VALID;
        }
        if (Vps_Registry::get('config')->showInvisible) {
            //preview im frontend
            if (Vps_Component_Data_Root::getInstance()->getComponentById($id, array('ignoreVisible'=>true))) {
                return self::VALID_DONT_CACHE;
            }
        }

        //paragraphs vorschau im backend
        $authData = Vps_Registry::get('userModel')->getAuthedUser();
        if (Vps_Registry::get('acl')->isAllowedComponentById($id, $className, $authData)) {
            return self::VALID_DONT_CACHE;
        }

        return self::INVALID;
    }

    public static function getMediaOutput($id, $type, $className)
    {
        $row = Vpc_Abstract::createModel($className)->getRow($id);
        if ($row) {
            $fileRow = $row->getParentRow('File');
        } else {
            $fileRow = false;
        }
        if (!$fileRow) {
            return null;
        } else {
            $file = $fileRow->getFileSource();
            $mimeType = $fileRow->mime_type;
        }
        if (!$file || !file_exists($file)) {
            return null;
        }
        Vps_Component_Cache::getInstance()->saveMeta(
            get_class($row->getModel()), $row->component_id, $id, Vps_Component_Cache::META_CALLBACK
        );
        return array(
            'file' => $file,
            'mimeType' => $mimeType
        );
    }

    public function onCacheCallback($row)
    {
        $cacheId = Vps_Media::createCacheId(
            $this->getData()->componentClass, $this->getData()->componentId, 'default'
        );
        Vps_Media::getOutputCache()->remove($cacheId);
    }
}