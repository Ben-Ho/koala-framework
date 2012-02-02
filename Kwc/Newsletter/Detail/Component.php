<?php
class Kwc_Newsletter_Detail_Component extends Kwc_Directories_Item_Detail_Component
{
    private $_toImport = array();

    /**
     * Cache for email addresses that should be checked against the rtr-ecg list
     * Key   = the same key as in $this->_toImport
     * Value = the email address that should be checked
     */
    private $_rtrCheck = array();

    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['mail'] = array(
            'class' => 'Kwf_Component_Generator_Static',
            'component' => 'Kwc_Newsletter_Detail_Mail_Component'
        );
        $ret['assetsAdmin']['files'][] = 'kwf/Kwc/Newsletter/Detail/MailingPanel.js';
        $ret['assetsAdmin']['files'][] = 'kwf/Kwc/Newsletter/Detail/RecipientsPanel.js';
        $ret['assetsAdmin']['files'][] = 'kwf/Kwc/Newsletter/Detail/RecipientsAction.js';
        $ret['assetsAdmin']['files'][] = 'kwf/Kwc/Newsletter/Detail/Recipients.css';
        $ret['assetsAdmin']['files'][] = 'ext/src/widgets/StatusBar.js';
        $ret['componentName'] = 'Newsletter';
        $ret['checkRtrList'] = !!Kwf_Registry::get('config')->service->rtrlist->url;
        $ret['flags']['skipFulltext'] = true;

        $ret['extConfig'] = 'Kwc_Newsletter_Detail_ExtConfig';

        $ret['contentSender'] = 'Kwc_Newsletter_Detail_ContentSender';
        return $ret;
    }

    public function addToQueue(Kwc_Mail_Recipient_Interface $recipient)
    {
        $newsletter = $this->getData()->row;
        if (in_array($newsletter->status, array('start', 'stop', 'finished', 'sending'))) {
            throw new Kwf_ClientException(trlKwf('Can only add users to a paused newsletter'));
        }

        if ($recipient instanceof Zend_Db_Table_Row_Abstract) {
            $class = get_class($recipient->getTable());
        } else if ($recipient instanceof Kwf_Model_Row_Abstract) {
            $class = get_class($recipient->getModel());
        } else {
            throw new Kwf_Exception('Only models or tables are supported.');
        }

        // check if the necessary modelShortcut is set in 'mail' childComponent
        $generators = $this->_getSetting('generators');
        // this function checks if everything neccessary is set
        Kwc_Mail_Redirect_Component::getRecipientModelShortcut(
            $generators['mail']['component'],
            $class
        );

        // break here if the receiver has unsubscribed
        if ($recipient instanceof Kwc_Mail_Recipient_UnsubscribableInterface) {
            if ($recipient->getMailUnsubscribe()) return false;
        }
        // break if the account has not been activated
        if ($recipient instanceof Kwf_Model_Abstract &&
            $recipient->getModel()->hasColumn('activated') &&
            !$recipient->activated
        ) {
            return false;
        }

        // add to senders list import
        $this->_toImport[] = array(
            'newsletter_id' => $newsletter->id,
            'recipient_model' => $class,
            'recipient_id' => $recipient->id,
            'status' => 'queued',
            'searchtext' =>
                $recipient->getMailFirstname() . ' ' .
                $recipient->getMailLastname() . ' ' .
                $recipient->getMailEmail()
        );

        // if this receiver should be checked against the rtr-ecg
        if (count($this->_toImport) && $this->_getSetting('checkRtrList')) {
            $this->_rtrCheck[count($this->_toImport) - 1] = $recipient->getMailEmail();
        }

        return true;
    }

    public function saveQueue()
    {
        $ret = array();
        $ret['rtrExcluded'] = array();

        // check against rtr-ecg list
        if (count($this->_rtrCheck)) {
            $badKeys = Kwf_Util_RtrList::getBadKeys($this->_rtrCheck);

            // remove the bad rtr entries from the list
            if ($badKeys) {
                foreach ($badKeys as $badKey) {
                    $ret['rtrExcluded'][] = $this->_rtrCheck[$badKey];
                    unset($this->_toImport[$badKey]);
                }
                // assign new keys
                $this->_toImport = array_values($this->_toImport);
            }
        }

        // add to model
        $newsletter = $this->getData()->row;
        $model = $this->getData()->parent->getComponent()->getChildModel()->getDependentModel('Queue');
        $select = $model->select()->whereEquals('newsletter_id', $newsletter->id);
        $ret['before'] = $model->countRows($select);
        $model->import(Kwf_Model_Db::FORMAT_ARRAY, $this->_toImport, array('ignore' => true));
        $ret['after'] = $model->countRows($select);
        $ret['added'] = $ret['after'] - $ret['before'];
        return $ret;
    }
}
