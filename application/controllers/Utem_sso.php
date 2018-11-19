<?php
/**
 * Created by Hamid.
 * User:
 * Date: 19/01/2018
 * Time: 12:21 PM
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'third_party/Cli.php';

use Stunt\Console\Cli as Cli;

class Utem_sso extends CI_Controller {
    public $cli;

    public function __construct()
    {
        parent::__construct();

        set_time_limit(0);
        $this->cli = new Cli('', '', array());

    }

    public function index()
    {
        if(is_cli())
        {
            $this->cli->out("[white_bold]UTeM SSO script help :.(".date('Y-m-d H:i:s').").[reset]\n")->new_line();
            $this->cli->out("[green_bold]- leech :[white_bold] get all ldap users and update against DB.[reset]\n")->new_line();
            $this->cli->out("[green_bold]- wipedb :[white_bold] [white_bold]wipe all DB users ([red_bold]warning-- make sure before wipping.)[reset]\n")->new_line();
            $this->cli->out("[green_bold]- AD_user/stafid :[white_bold] show AD user and equvalent DB user if exist.[reset]\n")->new_line();

        }
    }

    public function AD_get_all()
    {
        $this->load->model('ActiveDirectory_model','AD');
        $users = $this->AD->get_all();

        printr_pre($users);

    }

    public function AD_get_all_by_attr($attr='description')
    {
        $this->load->model('ActiveDirectory_model','AD');
        $users = $this->AD->get_all_by_attr($attr);

        printr_pre($users);

    }

    public function AD_get_all_active()
    {
        $this->load->model('ActiveDirectory_model','AD');
        $active_users = $this->AD->get_all_active_users();

        printr_pre($active_users);

    }

    public function AD_get_all_inactive()
    {
        $this->load->model('ActiveDirectory_model','AD');
        $inactive_users = $this->AD->get_all_inactive_users();

        printr_pre($inactive_users);

    }


    public function AD_user($staff_id)
    {
        $this->load->model('ActiveDirectory_model','AD');
        $user = $this->AD->get_user($staff_id);


            $name=$user['displayname'];
            $staff_id=$user['samaccountname'];

            printr_pre($user);
    }

    public function AD_followme_users($with_mobile)
    {
        $this->load->model('ActiveDirectory_model', 'AD');
        $users = $this->AD->get_followme_users($with_mobile);

        usort($users, function ($item1, $item2) {
            if ($item1['exten'] == $item2['exten']) return 0;
            return $item1['exten'] < $item2['exten'] ? -1 : 1;
        });

        $list = array();
        $counter  = 0;

        foreach ($users as $user)
        {
            ++$counter;
            $name = $user['displayname'];
            $exten = $user['exten'];
            $mobile = $user['mobile'];

            echo "$counter) $exten, $name, $mobile \r\n";
        }

    }



    public function update_followme_from_AD()
    {
        $this->load->model('ActiveDirectory_model', 'AD');
        $users = $this->AD->get_followme_users();

        usort($users, function ($item1, $item2) {
            if ($item1['exten'] == $item2['exten']) return 0;
            return $item1['exten'] < $item2['exten'] ? -1 : 1;
        });

        $list = array();
        $counter  = 0;

        $frpbx_servername = "localhost";
        $frpbx_username = "sso-user";
        $frpbx_password = "UTEMAstios01!";
        $frpbx_dname = "asterisk";
        $conn = new mysqli($frpbx_servername, $frpbx_username, $frpbx_password,$frpbx_dname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        echo "Connected to freepbx database successfully\r\n";

        foreach ($users as $user) {

            $name = $user['displayname'];
            $exten = $user['exten'];
            $mobile = $user['mobile'];

            echo "\r\n$counter) $exten, $name, $mobile  current followme list:";

            $sql = "SELECT grplist FROM findmefollow where grpnum = $exten";
            $result = $conn->query($sql);


            if ($result->num_rows > 0) {
                // output data of each row
                $row = $result->fetch_assoc();
                echo $row['grplist'];
                echo "\r\n";

                if ($row['grplist']=='') {
                    echo "empty follow me\r\n";
                    $followme_string = $exten . "-" . substr($exten,0,1) . $mobile . "#";
                    echo  "followme will be like this : $followme_string\r\n";

                    $sql_update = "UPDATE findmefollow SET grplist='$followme_string' WHERE grpnum=$exten";
                    if ($conn->query($sql_update) === TRUE) {
                        echo "\r\nRecord updated successfully";
                    } else {
                        echo "\r\nError updating record: " . $conn->error;
                    }

                    ++$counter;
                    continue;
                }
                if ($row['grplist'] == $exten) {
                    echo "exten but no mobile \r\n";
                    $followme_string = $row['grplist'] . "-" . substr($exten,0,1) . $mobile . "#";
                    echo "followme will be like this : $followme_string\r\n";

                    $sql_update = "UPDATE findmefollow SET grplist='$followme_string' WHERE grpnum=$exten";
                    echo "\r\n $sql_update";
                    if ($conn->query($sql_update) === TRUE) {
                        echo "\r\nRecord updated successfully";
                    } else {
                        echo "\r\nError updating record: " . $conn->error;
                    }

                    ++$counter;
                    continue;
                }

                if (!strstr($row['grplist'],$mobile)) {
                    echo "subbb" . strstr($row['grplist'],$mobile) . "\r\n";
                    echo "some other exten but no mobile \r\n";
                    $followme_string = $row['grplist'] . "-" . substr($exten,0,1) . $mobile . "#";
                    echo "followme will be like this : $followme_string\r\n";

                    $sql_update = "UPDATE findmefollow SET grplist='$followme_string' WHERE grpnum=$exten";
                    if ($conn->query($sql_update) === TRUE) {
                        echo "\r\nRecord updated successfully";
                    } else {
                        echo "\r\nError updating record: " . $conn->error;
                    }

                    ++$counter;
                    continue;
                }
            }

        }
        $conn->close();
    }

    public function AD_user_full($staff_id)
    {
        $this->load->model('ActiveDirectory_model','AD');
        $user = $this->AD->get_user_full($staff_id);


        printr_pre($user);
    }

    public function AD_camp_users($camp='main')
    {
        $this->load->model('ActiveDirectory_model','AD');
        $users = $this->AD->get_camp_users($camp);
        $this->cli->out("[green_bold]-users in $camp : [reset]\n");
        printr_pre($users);
    }

    public function frpbx_add_user($staff_id)
    {
        $this->load->helper('string');
        $this->load->helper('file');
        $this->load->helper('date');



        $this->load->model('ActiveDirectory_model', 'AD');
        $user = $this->AD->get_user($staff_id);

        //set variables
        $exten = $user['exten'];
        $did = $user['telephonenumber'];
        $camp = $user['camp'];
        $name = $user['displayname'];
        $discription = $user['displayname'];
        $voicemail_email = $user['mail'];
        $account = $user['samaccountname'];
        $mobile = $user['mobile'];
        $department = $user['department'];
        $secret = "ss$exten-$exten";
        $trunk_prefix = substr($exten, 0, 1);

        if($mobile == "" ) {
            $followme_list = $exten;
        }
        else
        {
            $followme_list = $exten . "-" . $trunk_prefix . $mobile . "#";
        }

        $followme_post_dest = "\"ext-local,$exten,dest\"";
        $voicemail_option = "attach=yes|saycid=yes|envelope=yes|delete=no";
        printr_pre($user);

//header for Exten csv file.
        $frpbx_exten_header =  FRPBX_EXTEN_HEADER;
//header for DID csv file.
        $frpbx_did_header = FRPBX_DID_HEADER;

        $frpbx_exten_string = $frpbx_exten_header ;
        $frpbx_did_string = $frpbx_did_header;



        $frpbx_exten_string .= "$exten,$exten,$name,default,0,default,$exten,pjsip,PJSIP/$exten,fixed,$exten,$name,";
        $frpbx_exten_string .= "$exten,6,$exten,$department,opus&ulaw&alaw&vp8,$name <$exten>,from-internal,all,6,$secret,";
        $frpbx_exten_string .= "chan_pjsip,ENABLED,ringallv2-prim,20,,$followme_list,$followme_post_dest,,,Ring,";
        $frpbx_exten_string .= "7,extern,$did,yes,yes,$exten,$voicemail_email,$voicemail_option,yes,yes,,,,,,,";

        $frpbx_did_string .= ",$did,\"from-did-direct,$exten,1\",$discription,default\n";

        //check camp
        switch ($camp) {
            case 'main':
                break;
            case 'tech':
                break;
            case 'city':
                break;
            default:
                echo "unknown camp";
                return;
                break;
        }

        $m_date = mdate('%Y%m%d%h%i%a');
        $frpbx_extens_file = "/var/www/html/utem-sso/tmp/frpbx-exten-$account-$m_date.csv";
        $frpbx_dids_file = "/var/www/html/utem-sso/tmp/frpbx-did-$account-$m_date.csv";


        if (!write_file($frpbx_extens_file, $frpbx_exten_string)) {
            echo "error writing file frpbx_exten_file in $frpbx_extens_file";
            exit();
        }
        if (!write_file($frpbx_dids_file, $frpbx_did_string)) {
            echo "error writing file frpbx_did_file in $frpbx_dids_file";
            exit();
        }

        echo "freepbx extensions and dids files path:\n";
        echo "$frpbx_extens_file\n";
        echo "$frpbx_dids_file\n";

    }

    public function freepbx_add_camp($camp='main')
    {
        $this->load->helper('string');
        $this->load->helper('file');
        $this->load->helper('date');

//header for Exten csv file.


        $frpbx_exten_string = FRPBX_EXTEN_HEADER;
        $frpbx_did_string = FRPBX_DID_HEADER;


        $this->load->model('ActiveDirectory_model','AD');
        $users = $this->AD->get_camp_users($camp);
        foreach ($users as $user) {
            //set variables

            printr_pre($user);




        }
        //check camp


        $m_date = mdate('%Y%m%d%h%i%a');
        $frpbx_extens_file = "/var/www/html/utem-sso/tmp/frpbx-exten-$camp-$m_date.csv";
        $frpbx_dids_file = "/var/www/html/utem-sso/tmp/frpbx-did-$camp-$m_date.csv";


        if (!write_file($frpbx_extens_file, $frpbx_exten_string)) {
            echo "error writing file frpbx_exten_file in $frpbx_extens_file";
            exit();
        }
        if (!write_file($frpbx_dids_file, $frpbx_did_string)) {
            echo "error writing file frpbx_did_file in $frpbx_dids_file";
            exit();
        }

        echo "freepbx extensions and dids files path:\n";
        echo "$frpbx_extens_file\n";
        echo "$frpbx_dids_file\n";

    }


    private function add_to_frpbx_exten_string(&$frpbx_string,$user)
    {
        $exten = $user['exten'];
        $did = $user['telephonenumber'];
        $camp = $user['camp'];
        $name = $user['displayname'];
        $discription = $user['displayname'];
        $voicemail_email = $user['mail'];
        $account = $user['samaccountname'];
        $mobile = $user['mobile'];
        $department = $user['department'];
        $secret = "ss$exten-$exten";
        $trunk_prefix = substr($exten, 0, 1);
        if ($mobile == "") {
            $followme_list = $exten;
        } else {
            $followme_list = $exten . "-" . $trunk_prefix . $mobile . "#";
        }
        $followme_post_dest = "\"ext-local,$exten,dest\"";
        $voicemail_option = "attach=yes|saycid=yes|envelope=yes|delete=no";

        $frpbx_string .= "$exten,$exten,$name,default,0,default,$exten,pjsip,PJSIP/$exten,fixed,$exten,$name,";
        $frpbx_string .= "$exten,6,$exten,$department,opus&ulaw&alaw&vp8,$name <$exten>,from-internal,all,6,$secret,";
        $frpbx_string .= "chan_pjsip,ENABLED,ringallv2-prim,20,,$followme_list,$followme_post_dest,,,Ring,";
        $frpbx_string .= "7,extern,$did,yes,yes,$exten,$voicemail_email,$voicemail_option,yes,yes,blocked,blocked,blocked,no,,,\n";

    }

}