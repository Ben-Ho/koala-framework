<?php
class Kwf_Component_Cache_Meta_Static_ProcessInputCache extends Kwf_Component_Cache_Meta_Static_Model
{
    public function __construct($generator)
    {
        parent::__construct($generator->getModel());
        $this->_params['generator']['class'] = $generator->getClass();
        $this->_params['generator']['key'] = $generator->getGeneratorKey();
    }

    public static function getMetaType()
    {
        return self::META_TYPE_PROCESSINPUTCACHE;
    }

    //TODO praktisch eine kopie von Kwf_Component_Cache_Meta_Static_UrlCache
    public static function getDeleteWhere($pattern, $row, $dirtyColumns, $params)
    {
        $ret = array();
        $generator = Kwf_Component_Generator_Abstract::getInstance($params['generator']['class'], $params['generator']['key']);
        $s = new Kwf_Component_Select();
        $pk = $row->getModel()->getPrimaryKey();
        if ($generator instanceof Kwc_Root_Category_Generator) {
            $s->whereId($row->$pk);
        } else {
            $s->whereId($generator->getIdSeparator().$row->$pk);
        }
        $s->ignoreVisible(true);
        foreach ($generator->getChildData(null, $s) as $c) {
            //TODO mehere sollten möglich sein
            $ret['db_id'] = $c->dbId;
        }
        return $ret;
    }

}
