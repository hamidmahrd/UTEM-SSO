<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by hamid.
 * User: hamid
 * Date: 12/11/2017
 * Time: 09:31 AM
 */

$config['ldap_utem'] = array(
    'host' => 'example.com',
    'useStartTls' => false,
    'username' => 'user@example.com',
    'password' => 'password',
    'accountDomainName'      => 'staff.example.com',
    'baseDn' => 'DC=staff,DC=example,DC=com'
);

$config['ldap_astiostech'] = array(
    'host' => '192.168.0.110',
    'useStartTls' => false,
    'username' => 'administrator@utem.local',
    'password' => 'password',
    'accountDomainName'      => 'utem.local',
    'baseDn' => 'DC=utem,DC=local'
);

$config['ldap_uc-dir'] = array(
    'host' => '192.168.1.29',
    'useStartTls' => false,
    'username' => 'cn=Manager,dc=uc-dir,dc=utem,dc=edu',
        'password' => 'password',
    //'accountDomainName'      => 'dc=uc-dir,dc=utem,dc=edu',
    'baseDn' => 'dc=uc-dir,dc=utem,dc=local'
);

$config['ldap_default'] = $config['ldap_astiostech'];
