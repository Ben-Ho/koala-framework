<?php
class Vps_Model_Select
{
    const WHERE = 'where';
    const WHERE_EQUALS = 'whereEquals';
    const WHERE_NOT_EQUALS = 'whereNotEquals';
    const WHERE_ID = 'whereId';
    const WHERE_NULL = 'whereNull';
    const ORDER = 'order';
    const LIMIT_COUNT = 'limitCount';
    const LIMIT_OFFSET = 'limitOffset';
    const OTHER = 'other';

    const ORDER_RAND = 'orderRand';

    protected $_parts = array();

    public function __construct($where = array())
    {
        if (is_string($where)) {
            $where = array($where);
        }
        foreach ($where as $key => $val) {
            if (is_int($key)) {
                $this->where($val);
                continue;
            }
            if ($key != 'limit' && $key != 'order') {
                $method = "where".ucfirst($key);
            } else {
                $method = $key;
            }
            if (method_exists($this, $method)) {
                $this->$method($val);
            } else if (is_null($val)) {
                $this->whereNull($key);
            } else {
                $this->where($key, $val);
            }
        }
    }

    //vielleicht mal umstellen auf:
    //return $this->where(new Vps_Model_Select_Expr_Equals($field, $value));
    public function whereEquals($field, $value = null)
    {
        if (is_array($field)) {
            foreach ($field as $f=>$v) {
                $this->whereEquals($f, $v);
            }
            return $this;
        }
        if (is_null($value)) {
            throw new Vps_Exception("value is required");
        }
        $this->_parts[self::WHERE_EQUALS][$field] = $value;
        return $this;
    }

    public function whereNotEquals($field, $value = null)
    {
        if (is_array($field)) {
            foreach ($field as $f=>$v) {
                $this->whereNotEquals($f, $v);
            }
            return $this;
        }
        if (is_null($value)) {
            throw new Vps_Exception("value is required");
        }
        $this->_parts[self::WHERE_NOT_EQUALS][$field] = $value;
        return $this;
    }

    public function whereNull($field)
    {
        if (strpos($field, '?') !==false) {
            throw new Vps_Exception("You don't want '?' in the field '$field'");
        }
        $this->_parts[self::WHERE_NULL][] = $field;
        return $this;
    }

    public function where($cond, $value = null, $type = null)
    {
        if (strpos($cond, '?') !==false && is_null($value)) {
            throw new Vps_Exception("Can't use '$cond' with value 'null'");
        }
        $this->_parts[self::WHERE][] = array($cond, $value, $type);
        return $this;
    }

    public function whereId($id)
    {
        $this->_parts[self::WHERE_ID] = $id;
        return $this;
    }

    public function order($field, $dir = 'ASC')
    {
        if (is_array($field)) {
            if (!isset($field['field'])) {
                foreach ($field as $f) {
                    $this->order($f);
                }
            } else {
                if (isset($field['dir'])) {
                    throw new Vps_Exception("'dir' key doesn't exist anymore, it was renamed to 'direction'");
                }
                if (!isset($field['direction'])) $field['direction'] = 'ASC';
                $this->_parts[self::ORDER][] = $field;
            }
        } else {
            $this->_parts[self::ORDER][] = array('field'=>$field, 'direction'=>$dir);
        }
        return $this;
    }

    public function limit($count, $offset = null)
    {
        if (is_array($count)) {
            $offset = $count['start'];
            $count = $count['limit'];
        }
        $this->_parts[self::LIMIT_COUNT] = $count;
        if ($offset) $this->_parts[self::LIMIT_OFFSET] = $offset;
        return $this;
    }

    public function getParts()
    {
        return $this->_parts;
    }

    public function getPart($part)
    {
        if (!isset($this->_parts[$part])) return null;
        return $this->_parts[$part];
    }

    public function hasPart($part)
    {
        return isset($this->_parts[$part]);
    }

    public function setPart($type, $part)
    {
        $this->_parts[$type] = $part;
        return $this;
    }

    public function unsetPart($type)
    {
        unset($this->_parts[$type]);
    }

    public function __call($method, $arguments)
    {
        $this->_parts[self::OTHER][] = array('method' => $method, 'arguments' => $arguments);
        return $this;
    }

    public function toDebug()
    {
        $out = array();
        foreach ($this->_parts as $type=>$p) {
            $out[$type] = $p;
        }
        $ret = print_r($out, true);
        $ret = preg_replace('#^Array#', get_class($this), $ret);
        $ret = "<pre>$ret</pre>";
        return $ret;
    }
}
