<?php
/**
 * Created by PhpStorm.
 * User: ahsuoy
 * Date: 5/9/2016
 * Time: 2:35 AM
 */

namespace acton;


class ActOnAccount
{

    private $accountInfo = array();

    private $currentAccount = 'dev';

    private $expireTime = 0;

    public function __construct($accountInfo = array(), $currentAccount = 'dev')
    {

        $this->accountInfo = $accountInfo;
        $this->currentAccount = $currentAccount;

    }

    public function getAccountInfo()
    {
        return $this->accountInfo;

    }

    public function getCurrentAccount()
    {
        return $this->currentAccount;

    }

    public function getActOnList() {


    }

}