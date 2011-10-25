<?php
class Kwf_Filter_Row_CurrentDateTimeIfColumnChanged extends Kwf_Filter_Row_CurrentDateTime
{
    private $_columns;
    public function __construct(array $columns, $dateFormat = 'Y-m-d H:i:s')
    {
        $this->_columns = $columns;
        parent::__construct($dateFormat);
    }

    public function skipFilter($row, $column)
    {
        if (!$row->$column) return false;

        //TODO: $row->getDirtyColumns direkt verwenden!
        while ($row instanceof Kwf_Model_Proxy_Row) $row = $row->getProxiedRow();
        if (!$row instanceof Kwf_Model_Db_Row) return false;
        $dc = $row->getRow()->___getDirtyColumns();
        if (!array_intersect($dc, $this->_columns)) {
            return true;
        }

        return false;
    }
}
