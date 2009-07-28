<?php
class Vps_Util_Rrd_Graph
{
    private static $_defaultColors = array('#00FF00', '#999999', '#FF0000', '#00FFFF', '#0000FF', '#000000');
    private $_rrd;
    private $_verticalLabel = null;
    private $_title = null;
    private $_devideBy = null;
    private $_fields = array();
    private $_lowerLimit = null;
    private $_upperLimit = null;

    public function __construct(Vps_Util_Rrd_File $rrd)
    {
        $this->_rrd = $rrd;
    }

    public function setVerticalLabel($l)
    {
        $this->_verticalLabel = $l;
    }

    public function setTitle($l)
    {
        $this->_title = $l;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setDevideBy($f)
    {
        if (is_string($f)) {
            $f = $this->_rrd->getField($f);
        }
        $this->_devideBy = $f;
    }

    public function setLowerLimit($l)
    {
        $this->_lowerLimit = $l;
    }

    public function setUpperLimit($l)
    {
        $this->_upperLimit = $l;
    }

    public function addField($field, $color = null, $text = null)
    {
        if (is_array($field)) {
            $f = $field;
            $field = false;
            if (isset($f['field'])) $field = $f['field'];
            if ($color || $text) throw new Vps_Exception("so ned");
        } else {
            if (is_array($color)) {
                $f = $color;
                $f['field'] = $field;
                if ($text) throw new Vps_Exception("so ned");
            } else {
                $f = array(
                    'field' => $field,
                    'color' => $color,
                    'text' => $text
                );
            }
        }
        if (isset($f['field']) && is_string($f['field'])) {
            $f['field'] = $this->_rrd->getField($f['field']);
        }
        if (!isset($f['color']) || !$f['color']) {
            foreach (self::$_defaultColors as $c) {
                $free = true;
                foreach ($this->_fields as $i) {
                    if ($i['color'] == $c) $free = false;
                }
                if ($free) {
                    $f['color'] = $c;
                    break;
                }
            }
        }
        if (!$f['color']) {
            throw new Vps_Exception("no more avaliable default colors");
        }
        if ((!isset($f['text'])) && isset($f['field'])) {
            $f['text'] = $f['field']->getText();
        }
        $this->_fields[] = $f;
    }

    public function getContents($start, $end = null)
    {
        if (is_array($start)) {
            $end = null;
            if (isset($start['height'])) $height = $start['height'];
            if (isset($start['width'])) $width = $start['width'];
            if (isset($start['end'])) $end = $start['end'];
            $start = $start['start'];
        }
        if (!isset($height)) $height = 320;
        if (!isset($width)) $width = 620;
        if (!$end) $end = time();

        if (is_string($start)) {
            $start = strtotime($start);
        }

        $tmpFile = tempnam('/tmp', 'graph');
        $cmd = "rrdtool graph $tmpFile -h $height -w $width ";
        //$cmd .= "--full-size-mode "; broken
        $cmd .= "-s $start ";
        $cmd .= "-e $end ";
        if (!is_null($this->_verticalLabel)) {
            $cmd .= "--vertical-label \"$this->_verticalLabel\" ";
        }
        if (!is_null($this->_title)) {
            $cmd .= "--title \"$this->_title\" ";
        }
        if (!is_null($this->_upperLimit)) {
            $cmd .= "--upper-limit \"$this->_upperLimit\" --rigid ";
        }
        if (!is_null($this->_lowerLimit)) {
            $cmd .= "--lower-limit \"$this->_lowerLimit\" ";
        }

        $rrdFile = $this->_rrd->getFileName();
        if ($this->_devideBy) {
            $cmd .= "DEF:requests=$rrdFile:".$this->_devideBy->getName().":AVERAGE ";
        }
        $i = 0;
        foreach ($this->_fields as $settings) {
            if (isset($settings['method'])) {
                $method = $settings['method'];
            } else {
                $method = 'AVERAGE';
            }
            if (isset($settings['field'])) {
                $field = $settings['field']->getName();
                if (isset($settings['addFields'])) {
                    $j = 0;
                    $cmd .= "DEF:line{$i}x{$j}=$rrdFile:$field:$method ";
                    foreach ($settings['addFields'] as $f) {
                        $j++;
                        $addField = $f['field'];
                        if (is_string($addField)) $addField = $this->_rrd->getField($addField);
                        $addField = $addField->getName();
                        $cmd .= "DEF:line{$i}x{$j}=$rrdFile:$addField:$method ";
                    }
                    $j = 0;
                    $cmd .= "CDEF:line{$i}=";
                    foreach ($settings['addFields'] as $f) {
                        $j++;
                        if ($j > 1) $cmd .= ",";
                        if (isset($f['defaultValue'])) {
                            $nextField = "line{$i}x{$j},line{$i}x{$j},0,IF";
                        } else {
                            $nextField = "line{$i}x{$j}";
                        }
                        $cmd .= "line{$i}x".($j-1).",$nextField,+";
                    }
                    $cmd .= " ";
                } else {
                    $cmd .= "DEF:line{$i}=$rrdFile:$field:$method ";
                }
                if ($this->_devideBy) {
                    $cmd .= "CDEF:perrequest$i=line$i,requests,/ ";
                }
            }
            if (isset($settings['cmd'])) $cmd .= $settings['cmd'].' ';
            if (isset($settings['line'])) {
                $cmd .= $settings['line'].' ';
            } else {
                if ($this->_devideBy) {
                    $fieldName = "perrequest$i";
                } else {
                    $fieldName = "line$i";
                }
                $cmd .= "LINE2:$fieldName{$settings['color']}:\"$settings[text]\" ";
            }
            $i++;
        }
        $cmd .= " 2>&1";
        if ($this->_rrd->getTimeZone()) {
            $cmd = "TZ=".$this->_rrd->getTimeZone()." ".$cmd;
        }
        exec($cmd, $out, $ret);
        if ($ret) {
            throw new Vps_Exception(implode('', $out)."\n".$cmd);
        }
        $ret = file_get_contents($tmpFile);
        unlink($tmpFile);
        return $ret;
    }

    public function output($start, $end = null)
    {
        Vps_Media_Output::output(array(
            'contents' => $this->getContents($start, $end),
            'mimeType' => 'image/png'
        ));
    }
}
