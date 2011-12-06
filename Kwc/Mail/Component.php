<?php
class Kwc_Mail_Component extends Kwc_Abstract
{
    private $_mailData;
    protected $_images = array();

    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['content'] = array(
            'class' => 'Kwf_Component_Generator_Static',
            'component' => 'Kwc_Paragraphs_Component'
        );
        $ret['generators']['redirect'] = array(
            'class' => 'Kwf_Component_Generator_Page_Static',
            'component' => 'Kwc_Mail_Redirect_Component',
            'name' => trlKwfStatic('E-Mail'),
            'filename' => 'r'
        );

        $sender = Kwf_Mail::getSenderFromConfig();
        $ret['default'] = array(
            'from_email' => $sender['address'],
            'from_name' => $sender['name']
        );

        $ret['assetsAdmin']['files'][] = 'kwf/Kwc/Mail/PreviewWindow.js';
        $ret['plugins']['placeholders'] = 'Kwc_Mail_PlaceholdersPlugin';
        $ret['ownModel'] = 'Kwc_Mail_Model';
        $ret['componentName'] = 'Mail';

        // set shorter source keys for recipient models
        // key = sourceShortcut, value = modelClass
        // e.g. array('user' => 'Kwf_User_Model')
        $ret['recipientSources'] = array();

        $ret['mailHtmlStyles'] = array();
        $ret['bcc'] = false;
        $ret['viewCache'] = false;
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $c = $this->getData()->getChildComponent('-content');
        if ($c) {
            $ret['content'] = $c;
        }
        return $ret;
    }

    public function getHtmlStyles()
    {
        $ret = $this->_getSetting('mailHtmlStyles');

        // Hack für Tests, weil da der statische getStylesArray-Aufruf nicht funktioniert
        $contentComponent = $this->getData()->getChildComponent('-content');
        if ($contentComponent &&
            is_instance_of($contentComponent->componentClass, 'Kwc_Paragraphs_Component')
        ) {
            foreach (Kwc_Basic_Text_StylesModel::getStylesArray() as $tag => $classes) {
                foreach ($classes as $class => $style) {
                    $ret[] = array(
                        'tag' => $tag,
                        'class' => $class,
                        'styles' => $style['styles']
                    );
                }
            }
        }
        return $ret;
    }

    public function createMail(Kwc_Mail_Recipient_Interface $recipient, $data = null, $toAddress = null, $format = null)
    {
        $this->_images = array();

        $this->_mailData = $data;

        $mail = new Kwf_Mail();
        $name = $recipient->getMailFirstname() . ' ' . $recipient->getMailLastname();
        if (!$name == ' ') $name = null;
        if ($toAddress) {
            $mail->addTo($toAddress, $name);
        } else {
            $mail->addTo($recipient->getMailEmail(), $name);
        }

        if ((!$format && $recipient->getMailFormat() == Kwc_Mail_Recipient_Interface::MAIL_FORMAT_HTML) ||
            $format == Kwc_Mail_Recipient_Interface::MAIL_FORMAT_HTML)
        {
            $mail->setBodyHtml($this->getHtml($recipient, true));
        }
        $mail->setBodyText($this->getText($recipient));
        $mail->setSubject($this->getSubject($recipient));
        if ($this->getRow()->from_email) {
            $mail->setFrom($this->getRow()->from_email, $this->getRow()->from_name);
        }
        if ($this->getRow()->reply_email) {
            $mail->setReplyTo($this->getRow()->reply_email);
        }

        if ($this->_images){
            $mail->setType(Zend_Mime::MULTIPART_RELATED);
            foreach ($this->_images as $image) {
                $mail->addAttachment($image);
            }
        }

        if ($this->_getSetting('bcc')) {
            $mail->addBcc($this->_getSetting('bcc'));
        }
        //TODO: attachments

        return $mail;
    }

    /**
     * Verschickt ein mail an @param $recipient.
     * @param $data Optionale Daten die benötigt werden, kann von den
     *        Komponenten per $this->getData()->getParentByClass('Kwc_Mail_Component')->getComponent()->getMailData();
     *        ausgelesen werden
     * Wird von Gästebuch verwendet
     */
    public function send(Kwc_Mail_Recipient_Interface $recipient, $data = null, $toAddress = null, $format = null)
    {
        $mail = $this->createMail($recipient, $data, $toAddress, $format);
        return $mail->send();
    }

    //kann von einer mail-content komponente aufgerufen werden
    //hier können mail spezifische daten drinstehen
    public function getMailData()
    {
        return $this->_mailData;
    }

    /**
     * Gibt den personalisierten HTML-Quelltext der Mail zurück
     *
     * @param bool forMail: ob images als attachment angehängt werden sollen oder nicht
     */
    public function getHtml(Kwc_Mail_Recipient_Interface $recipient = null, $attachImages = false)
    {
        $renderer = new Kwf_Component_Renderer_Mail();
        $renderer->setRenderFormat(Kwf_Component_Renderer_Mail::RENDER_HTML);
        $renderer->setRecipient($recipient);
        $renderer->setAttachImages($attachImages);
        $ret = $renderer->renderComponent($this->getData());
        $ret = $this->_processPlaceholder($ret, $recipient);
        $ret = $this->getData()->getChildComponent('_redirect')->getComponent()->replaceLinks($ret, $recipient);
        $htmlStyles = $this->getHtmlStyles();
        if ($htmlStyles){
            $p = new Kwc_Mail_HtmlParser($htmlStyles);
            $ret = $p->parse($ret);
        }
        return $ret;
    }

    /**
     * Gibt den personalisierten Quelltext der Mail zurück
     *
     * @see getHtml Für Ersetzungen siehe
     */
    public function getText(Kwc_Mail_Recipient_Interface $recipient = null)
    {
        $renderer = new Kwf_Component_Renderer_Mail();
        $renderer->setEnableCache(false); //TODO remove once text mails have their own cache entries
        $renderer->setRenderFormat(Kwf_Component_Renderer_Mail::RENDER_TXT);
        $renderer->setRecipient($recipient);
        $ret = $renderer->renderComponent($this->getData());
        $ret = $this->_processPlaceholder($ret, $recipient);
        $ret = str_replace('&nbsp;', ' ', $ret);
        $ret = $this->getData()->getChildComponent('_redirect')->getComponent()->replaceLinks($ret, $recipient);
        return $ret;
    }

    public function getSubject(Kwc_Mail_Recipient_Interface $recipient = null)
    {
        $ret = $this->getRow()->subject;
        $ret = $this->_processPlaceholder($ret, $recipient);
        return $ret;
    }

    protected function _processPlaceholder($ret, Kwc_Mail_Recipient_Interface $recipient = null)
    {
        $plugins = $this->_getSetting('plugins');
        foreach ($plugins as $p) {
            if (is_instance_of($p, 'Kwf_Component_Plugin_View_Abstract')) {
                $p = new $p($this->getData()->componentId);
                $ret = $p->processMailOutput($ret, $recipient);
            }
        }
        return $ret;
    }

    public function getPlaceholders(Kwc_Mail_Recipient_Interface $recipient = null)
    {
        $ret = array();
        if ($recipient) {
            $ret['firstname'] = $recipient->getMailFirstname();
            $ret['lastname'] = $recipient->getMailLastname();
            if ($recipient instanceof Kwc_Mail_Recipient_TitleInterface) {
                $replace = array(
                    $recipient->getMailTitle(),
                    $recipient->getMailLastname()
                );
                $politeM = $this->getData()->trlKwf('Dear Mr. {0} {1}', $replace);
                $politeF = $this->getData()->trlKwf('Dear Mrs. {0} {1}', $replace);
                if ($recipient->getMailGender() == 'male' && $recipient->getMailLastname()) {
                    $t = $this->getData()->trlKwf('Dear Mr. {0} {1}', $replace);
                } else if ($recipient->getMailGender() == 'female' && $recipient->getMailLastname()) {
                    $t = $this->getData()->trlKwf('Dear Mrs. {0} {1}', $replace);
                } else {
                    $t = $this->getData()->trlKwf('Dear Sir or Madam');
                }
                $ret['salutation_polite'] = trim(str_replace('  ', ' ', $t));

                if ($recipient->getMailGender() == 'male') {
                    $t = $this->getData()->trlKwf('Mr. {0}', $recipient->getMailTitle());
                } else if ($recipient->getMailGender() == 'female') {
                    $t = $this->getData()->trlKwf('Mrs. {0}', $recipient->getMailTitle());
                } else {
                    $t = $recipient->getMailTitle();
                }
                $ret['salutation_title'] = trim(str_replace('  ', ' ', $t));

                $ret['title'] = $recipient->getMailTitle();
            }
            if ($recipient instanceof Kwc_Mail_Recipient_GenderInterface) {
                $replace = array($recipient->getMailLastname());
                if ($recipient->getMailGender() == 'male') {
                    $ret['salutation_polite_notitle'] = $this->getData()->trlKwf('Dear Mr. {0}', $replace);
                    $ret['salutation_hello'] = $this->getData()->trlKwf('Hello Mr. {0}', $replace);
                    $ret['salutation'] = $this->getData()->trlKwf('Mr.');
                } else if ($recipient->getMailGender() == 'female') {
                    $ret['salutation_polite_notitle'] = $this->getData()->trlKwf('Dear Mrs. {0}', $replace);
                    $ret['salutation_hello'] = $this->getData()->trlKwf('Hello Mrs. {0}', $replace);
                    $ret['salutation'] = $this->getData()->trlKwf('Mrs.');
                } else {
                    $replace = array(
                        $recipient->getMailFirstname(),
                        $recipient->getMailLastname()
                    );
                    if ($recipient->getMailFirstname() && $recipient->getMailLastname()) {
                        $ret['salutation_polite_notitle'] = trim($this->getData()->trlKwf('Dear {0} {1}', $replace));
                    } else {
                        $ret['salutation_polite_notitle'] = $this->getData()->trlKwf('Dear Sir or Madam');
                    }
                    $ret['salutation_hello'] = trim($this->getData()->trlKwf('Hello {0} {1}', $replace));
                }
            }
        }
        return $ret;
    }

    public function addImage(Zend_Mime_Part $image)
    {
        // Bild nur hinzufügen wenn dasselbe nicht bereits hinzugefügt wurde.
        // wenns das bild schon gibt, hat es eh die gleiche cid
        $found = false;
        foreach ($this->_images as $addedImg) {
            if ($image == $addedImg) $found = true;
        }
        if ($found === false) $this->_images[] = $image;
    }
}
