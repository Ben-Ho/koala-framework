<?php
class Kwc_Basic_LinkTag_Intern_Admin extends Kwc_Basic_LinkTag_Abstract_Admin
    implements Kwf_Component_Abstract_Admin_Interface_DependsOnRow
{
    private $_duplicated = array();

    public function getComponentsDependingOnRow(Kwf_Model_Row_Interface $row)
    {
        // nur bei pageModel
        if ($row->getModel() instanceof Kwc_Root_Category_GeneratorModel) {
            $linkModel = Kwf_Model_Abstract::getInstance(
                Kwc_Abstract::getSetting($this->_class, 'ownModel')
            );
            $linkingRows = $linkModel->getRows($linkModel->select()
                ->whereEquals('target', $row->{$row->getModel()->getPrimaryKey()})
            );
            if (count($linkingRows)) {
                $ret = array();
                foreach ($linkingRows as $linkingRow) {
                    $c = Kwf_Component_Data_Root::getInstance()
                        ->getComponentByDbId($linkingRow->component_id);
                    //$c kann null sein wenn es nicht online ist
                    if ($c) $ret[] = $c;
                }
                return $ret;
            }
        }
        return array();
    }

    public function componentToString(Kwf_Component_Data $data)
    {
        if (!$data->getLinkedData()) return '';
        return $data->getLinkedData()->name;
    }

    public function duplicate($source, $target, $progressBar = null)
    {
        parent::duplicate($source, $target, $progressBar);
        $this->_duplicated[] = array(
            'source' => $source->componentId,
            'target' => $target->componentId,
        );
    }

    public function afterDuplicate($rootSource, $rootTarget)
    {
        parent::afterDuplicate($rootSource, $rootTarget);
        foreach ($this->_duplicated as $d) {
            //modify duplicated links so they point to duplicated page
            //only IF link points to page below $rootSource
            $source = Kwf_Component_Data_Root::getInstance()->getComponentById($d['source'], array('ignoreVisible'=>true));
            $sourceRow = $source->getComponent()->getRow();
            foreach (Kwf_Component_Data_Root::getInstance()->getComponentsByDbId($sourceRow->target, array('ignoreVisible'=>true)) as $sourceLinkTarget) {
                $linkTargetIsBelowRootSource = false;
                do {
                    if ($sourceLinkTarget->componentId == $rootSource->componentId) {
                        $linkTargetIsBelowRootSource = true;
                        break;
                    }
                } while ($sourceLinkTarget = $sourceLinkTarget->parent);
            }
            if ($linkTargetIsBelowRootSource) {
                //get duplicated link target id from duplicate log
                $sql = "SELECT target_component_id FROM kwc_log_duplicate WHERE source_component_id = ? ORDER BY id DESC LIMIT 1";
                $q = Kwf_Registry::get('db')->query($sql, $sourceRow->target);
                $q = $q->fetchAll();
                if (!$q) continue;
                $linkTargetId =  $q[0]['target_component_id'];
                $target = Kwf_Component_Data_Root::getInstance()->getComponentById($d['target'], array('ignoreVisible'=>true));
                $targetRow = $target->getComponent()->getRow();
                $targetRow->target = $linkTargetId;
                $targetRow->save();
            }
        }
        $this->_duplicated = array();
    }
}
