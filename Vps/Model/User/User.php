<?p
class Vps_Model_User_User extends Zend_Db_Table_Row_Abstra

    public function __toString
   
        return $this->realnam
   

    public function generateNewPassword
   
        $newPassword = substr(md5(uniqid(mt_rand(), true)), 0, 6
        if (!$this->password_salt)
            mt_srand((double)microtime()*1000000
            $this->password_salt = substr(md5(uniqid(mt_rand(), true)), 0, 10
       
        $this->password = md5($newPassword.$this->password_salt
        $this->password_isnew = 
        $this->password_mailed = 
        return $newPasswor
   

    /
     * Erstellt ein neues Passwort und sendet es per Mail an den Us
     
    public function sendPasswordMail
   
        $newPassword = $this->generateNewPassword(
        if ($this->email)
            $this->password_mailed = 
            $mail = new Zend_Mail('utf-8'
            //todo: smarty template verwenden für mailte
            $bodyText = "Hallo ".$this->__toString()."!\n\
                ."Folgendes Login ist ab sofort für Sie aktiv.\n\
                ."Benutzername: ".$this->username."\
                ."Passwort: ".$newPassword."\n\
                ."---\nDiese Email wurde automatisch erstellt - bitte nicht antworten.
            $mail->setBodyText($bodyText
            $mail->setFrom('noreply@vivid-planet.com', 'Vivid Planet Software'
            $mail->addTo($this->email, $this->__toString()
            $mail->setSubject('Ihr Account'
            $mail->send(
            return tru
       
        return fals
   

    public function toArray
   
        $user = parent::toArray(
        unset($user['password'], $user['password_salt']
        return $use
   

