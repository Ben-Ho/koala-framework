<?php
class Vpc_Posts_Detail_Component extends Vpc_Abstract_Composite_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component']['actions'] = 'Vpc_Posts_Detail_Actions_Component';
        $ret['generators']['child']['component']['signature'] = 'Vpc_Posts_Detail_Signature_Component';
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $data = $this->getData();

        $ret['content'] = self::replaceCodes($data->row->content);
        $ret['user'] = Vps_Component_Data_Root::getInstance()
            ->getComponentByClass(
                'Vpc_User_Directory_Component',
                array('subroot' => $this->getData())
            )
            ->getChildComponent('_'.$data->row->user_id);
        $select = $data->parent->getGenerator('detail')->select($data->parent)
            ->where('create_time <= ?', $data->row->create_time);
        $ret['postNumber'] = $data->parent->countChildComponents($select);
        return $ret;
    }

    static public function replaceCodes($content)
    {
        // html entfernen
        $content = htmlspecialchars($content);

        // smileys
        $content = preg_replace('/:-?\)/', '<img src="/assets/silkicons/emoticon_smile.png" alt=":-)" />', $content);
        $content = preg_replace('/:-?D/', '<img src="/assets/silkicons/emoticon_grin.png" alt=":-D" />', $content);
        $content = preg_replace('/:-?P/', '<img src="/assets/silkicons/emoticon_tongue.png" alt=":-P" />', $content);
        $content = preg_replace('/:-?\(/', '<img src="/assets/silkicons/emoticon_unhappy.png" alt=":-(" />', $content);
        $content = preg_replace('/;-?\)/', '<img src="/assets/silkicons/emoticon_wink.png" alt=";-)" />', $content);

        // zitate
        $content = str_replace('[quote]', '<fieldset class="quote"><legend>Zitat</legend>', $content, $countOpened);

        $content = preg_replace('/\[quote=([^\]]*)\]/i',
            '<fieldset class="quote"><legend>Zitat von $1</legend>',
            $content,
            -1, $countOpenedPreg
        );

        $content = str_replace('[/quote]', '</fieldset>', $content, $closed);

        $open = $countOpened + $countOpenedPreg;

        while ($open > $closed) {
            $content .= '</fieldset>';
            $closed++;
        }
        while ($closed > $open) {
            $content = '<fieldset class="quote"><legend>Zitat</legend>'.$content;
            $open++;
        }

        // automatische verlinkung
        $truncate = new Vps_View_Helper_Truncate();
        $pattern = '/((http:\/\/)|(www\.)|(http:\/\/www\.)){1,1}([a-z0-9äöü;\/?:@=&!*~#%\'+$.,_-]+)/i';
        $offset = 0;
        while (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $showUrl = $truncate->truncate($matches[5][0], 60, '...', true);
            $replace = "<a href=\"http://{$matches[3][0]}{$matches[5][0]}\" "
                ."title=\"{$matches[3][0]}{$matches[5][0]}\" rel=\"popup_blank\">{$matches[3][0]}$showUrl</a>";
            $content = substr($content, 0, $matches[0][1])
                .$replace.substr($content, $matches[0][1] + strlen($matches[0][0]));
            $offset = $matches[0][1] + strlen($replace);
        }

        return nl2br($content);
    }

    public function getCacheVars()
    {
        $ret = parent::getCacheVars();
        $row = $this->getData()->row;
        $ret[] = array(
            'model' => Vps_Registry::get('config')->user->model,
            'id' => $row->user_id
        );
        return $ret;
    }
}
