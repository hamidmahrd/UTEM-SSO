<?php
/**
 * Created by PhpStorm.
 * User: hamid
 * Date: 12/12/2017
 * Time: 01:39 AM
 */


class ActiveDirectory_model extends CI_Model {
    private $ldap;
    private $options;
    private $baseDn;
    
    public function __construct()
    {
        parent::__construct();

        $this->zend->load('Zend/autoload');
        $this->options = $this->config->item('ldap_default');
        $this->baseDn = $this->options['baseDn'];
        $this->ldap = new Zend\Ldap\Ldap($this->options);
    }

    /**
     * Get all Active Directory users.
     *@return array list of all users.
     */
    public function get_all()
    {
        $resource = $this->ldap->connect()->getResource();
        $this->ldap->bind();
        $ldap_users = new \ArrayIterator;

        $i = 0;
        $cookie = '';
        do {
            ldap_control_paged_result($resource, 100, true, $cookie);


            $result = ldap_search($resource, $this->baseDn,'(objectclass=person)', array(), 0, 100, 0);


            $entries = new \ArrayIterator(ldap_get_entries($resource, $result));

            foreach ($entries as $item) {

                if (empty($item['samaccounttype'])) {
                    continue;
                }
                if ($item['samaccounttype'][0] != "805306368") {    //samacounttypeof user account
                    continue;
                }

                $user = $this->user_to_array($item);
                $ldap_users[] = $user;
            }
            ldap_control_paged_result_response($resource, $result, $cookie);

        } while($cookie !== null && $cookie != '');

        return $ldap_users->getArrayCopy();
    }

    public function get_all_by_attr($keyword)
    {
        $resource = $this->ldap->connect()->getResource();
        $this->ldap->bind();
        $ldap_users = new \ArrayIterator;

        $i = 0;
        $cookie = '';
        do {
            ldap_control_paged_result($resource, 100, true, $cookie);


            $result = ldap_search($resource, $this->baseDn,'(objectclass=person)', array(), 0, 100, 0);


            $entries = new \ArrayIterator(ldap_get_entries($resource, $result));

            foreach ($entries as $item) {

                if (empty($item['samaccounttype'])) {
                    continue;
                }
                if ($item['samaccounttype'][0] != "805306368") {    //samacounttypeof user account
                    continue;
                }

                $user = $this->user_to_array($item);
                $ldap_users[$user[$keyword]][] = $user;
            }
            ldap_control_paged_result_response($resource, $result, $cookie);

        } while($cookie !== null && $cookie != '');

        return $ldap_users->getArrayCopy();
    }

    public function get_all_inactive_users()
    {

    }

    public function get_followme_users()
    {
        $all_users = $this->get_all();

        $followme_users = array();

        foreach ($all_users as $user) {
            if ($user['mobile'] != '') {
                $followme_users = $user;
            }
        }

        return $followme_users;
    }

    public function get_all_active_users()
    {

        $users = $this->get_all_by_attr('description');

        foreach ($users['SEDANG BERKHIDMAT'] as $user)
        {
            if (!in_array($user['useraccountcontrol'],unserialize(DISABLED_ACCOUNT_CONTROL)))
            {
                if (in_array($user['camp'],unserialize(CAMPS)))
                {
                    $active_users[] = $user;
                }
            }
        }
        return $active_users;
    }

    public function get_user($staff_id)
    {
        $resource = $this->ldap->connect()->getResource();
        $this->ldap->bind();
        $search_filter = "(sAMAccountName=$staff_id)";

        $result = ldap_search($resource, $this->baseDn, $search_filter, array(), 0, 100, 0);
        $items = ldap_get_entries($resource, $result);
//hamid
        if (empty($items[0])) {
            echo "empty";
            return;
        }
        $item = $items[0];
        if (empty($item['samaccounttype'])) {
            return;
        }
        if ($item['samaccounttype'][0] != "805306368") {    //samacounttypeof user account
            return;
        }

        $ldap_user = $this->user_to_array($item);

        return $ldap_user;
    }

    /**
     * Get Active Directory full data for staff_id.
     *@return array user with full data.
     */
    public function get_user_full($staff_id)
    {
        $resource = $this->ldap->connect()->getResource();
        $this->ldap->bind();
        $search_filter = "(sAMAccountName=$staff_id)";

        $result = ldap_search($resource, $this->baseDn, $search_filter, array(), 0, 100, 0);
        $items = ldap_get_entries($resource, $result);

        if (empty($items[0])) {
            echo "empty";
            return;
        }
        $item = $items[0];
        if (empty($item['samaccounttype'])) {
            return;
        }
        if ($item['samaccounttype'][0] != "805306368") {    //samacounttypeof user account
            return;
        }

        return $item[0];


    }


    /**
     * Check DID for valid utem DID numbers.
     *@return phonenumber.
     */
    private function checkDID($telephone_number)
    {
        $phoneNumber = preg_replace('/[^0-9]/','',$telephone_number);
        $phoneNumber = ltrim($phoneNumber, '0');

        if(strlen($phoneNumber) == 11) {
            $countryCode = substr($phoneNumber, 0, 2);
            $areaCode = substr($phoneNumber, 2, 2);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+'.$countryCode.''.$areaCode.''.$nextThree.''.$lastFour;
        }
        else if(strlen($phoneNumber) == 10) {
            $countryCode = substr($phoneNumber, 0, 2);
            $areaCode = substr($phoneNumber, 2, 1);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+'.$countryCode.''.$areaCode.''.$nextThree.''.$lastFour;
        }
        else if(strlen($phoneNumber) == 9) {
            $countryCode = '60';
            $areaCode = substr($phoneNumber, 0, 2);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+'.$countryCode.''.$areaCode.''.$nextThree.''.$lastFour;
        }
        else if(strlen($phoneNumber) == 8) {
            $countryCode = '60';
            $areaCode = substr($phoneNumber, 0, 1);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+'.$countryCode.''.$areaCode.''.$nextThree.''.$lastFour;
        }
        else if(strlen($phoneNumber) == 7) {
            $countryCode = '60';
            $areaCode = '6';
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+'.$countryCode.''.$areaCode.''.$nextThree.''.$lastFour;
        }
        else
        {
            $countryCode = '60';
            $areaCode='6';
            $nextThree = '270';
            $phoneNumber .= '-not_verified';
        }

        //if not inside malaysia
        if($countryCode!='60')
        {$phoneNumber .= '-not_in_malaysia';}
        elseif($areaCode != '6') // not in melaka
        {$phoneNumber .= '-not_in_melaka';}
        elseif($nextThree !='270')
        {$phoneNumber .= '-DID_Prefix';}

        return $phoneNumber;
    }

    private function check_mobile($mobile_number)
    {
        $phoneNumber = preg_replace('/[^0-9]/','',$mobile_number);
        $phoneNumber = ltrim($phoneNumber, '0');


        if(strlen($phoneNumber) == 0)
        {
            return "";
        }
        else {
            $phoneNumber = '0' . $phoneNumber;
        }
        return $phoneNumber;
    }

    private function getExten($telephone_number){
        $phoneNumber = $this->checkDID($telephone_number);

        if(is_numeric($phoneNumber))
        {
            $exten = substr($phoneNumber, -4, 4);
            return $exten;
        }
        return ;
    }

    private function getCamp($exten)
    {

        switch (substr($exten, 0, 1)) {
            case '8':
                $camp = 'city';
                break;
            case '4':
                $camp = 'tech';
                break;
            case '1':
                $camp = 'main';
                break;
            case '2':
                $camp = 'main';
                break;
            default:
                $camp = 'unknown';
                break;

        }
        return $camp;
    }

    private function user_to_array($item)
    {

        $samaccountname = isset($item['samaccountname'][0]) ? $item['samaccountname'][0] : "[no account name]";
        $samaccounttype = isset($item['samaccounttype'][0]) ? "user" : "[no account type]";
        $givenname = isset($item['givenname'][0]) ? $item['givenname'][0] : "";
        $displayname = isset($item['displayname'][0]) ? $item['displayname'][0] : "";
        $mail = isset($item['mail'][0]) ? $item['mail'][0] : "";
        $department = isset($item['department'][0]) ? $item['department'][0] : "";
        $telephonenumber = isset($item['ipphone'][0]) ? $this->checkDID($item['ipphone'][0]) : "";
        $mobile = isset($item['mobile'][0]) ? $this->check_mobile($item['mobile'][0]) : "";
        $exten = isset($item['ipphone'][0]) ? $this->getExten($item['ipphone'][0]) : "";
        $user_camp = isset($exten) ? $this->getCamp($exten) : "";
        $useraccountcontrol = isset($item['useraccountcontrol'][0]) ? $item['useraccountcontrol'][0] : "";
        $description = isset($item['description'][0]) ? $item['description'][0] : "";
        $create = isset($item['whencreated'][0]) ? $item['whencreated'][0] : "";
        $change = isset($item['whenchanged'][0]) ? $item['whenchanged'][0] : "";

        $user = array('samaccountname' => $samaccountname, 'samaccounttype' => $samaccounttype, 'givenname' => $givenname, 'displayname' => $displayname, 'mail' => $mail, 'department' => $department, 'telephonenumber' => $telephonenumber, 'mobile' => $mobile, 'exten' => $exten, 'camp' => $user_camp, 'useraccountcontrol' => $useraccountcontrol,'description' => $description, 'whencreated' => $create, 'whenchanged' => $change);

        return $user;
    }



}
