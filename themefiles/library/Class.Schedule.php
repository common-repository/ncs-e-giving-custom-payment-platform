<?php

namespace NCSSERVICES;

define('EG_SCHEDULE_SERVICE', WS_HOST . 'scheduleservice.svc/scheduleservice/');
/*$EG_ADD_CREDITCARD_SCHEDULE = array('url' => EG_SCHEDULE_SERVICE . 'schedule/creditcard', 'method' => 'POST');
$EG_ADD_BANKACCOUNT_SCHEDULE = array('url' => EG_SCHEDULE_SERVICE . 'schedule/bankaccount', 'method' => 'POST');
$EG_DELETE_SCHEDULE = array('url' => EG_SCHEDULE_SERVICE . '%s', 'method' => 'DELETE');*/

class Schedule extends Entity {
    /* public $ID = 0; public $MemberID = 0; public $Frequency = ''; public $StartDate = '1/1/1900';
      public $EndDate = '1/1/1900'; public $NextScheduleDate = '1/1/1900'; public $Total = 0;
      public $IsDeleted = 0; public $Funds = array(); public $PaymentMethod = ''; public $CreditCardID = 0;
      public $CreditCard = ''; public $BankID = 0; public $BankAccount = ''; */

    public $Frequency = '';
    public $StartDate = '1/1/1900';
    public $EndDate = '1/1/1900';
    public $Total = 0;
    public $Funds = array();
    public $CreditCard = '';
    public $BankAccount = '';

    public function __construct($Frequency = '', $StartDate = '1/1/1900', $EndDate = '1/1/1900', $Total = 0, $Funds = '', $CreditCard = '', $BankAccount = ''
    ) {
        if ($BankAccount == '') {
            $BankAccount = null;
        }
        if ($CreditCard == '') {
            $CreditCard = null;
        }
        $this->Frequency = $Frequency;
        $this->StartDate = $StartDate;
        $this->EndDate = $EndDate;
        $this->Total = $Total;
        $this->Funds = $Funds;
        $this->CreditCard = $CreditCard;
        $this->BankAccount = $BankAccount;
    }
    
    ## function to schedule payments using credit cards...
    public function CreditCardSchedulePayment() {
        $EG_ADD_CREDITCARD_SCHEDULE = array('url' => EG_SCHEDULE_SERVICE . 'schedule/creditcard', 'method' => 'POST');
        return $EG_ADD_CREDITCARD_SCHEDULE;
    }
    
    ## function to schedule payments using Cheque(ACH Payments)...
    public function ACHSchedulePayment() {
        $EG_ADD_BANKACCOUNT_SCHEDULE = array('url' => EG_SCHEDULE_SERVICE . 'schedule/bankaccount', 'method' => 'POST');
        return $EG_ADD_BANKACCOUNT_SCHEDULE;
    }
    
    ## function to delete the scheduled payments...
    public function DeleteSchedulePayment() {
        $EG_DELETE_SCHEDULE = array('url' => EG_SCHEDULE_SERVICE . '%s', 'method' => 'DELETE');
        return $EG_DELETE_SCHEDULE;
    }

    /* function Schedule($Frequency = '', $StartDate = '1/1/1900', $EndDate = '1/1/1900', $Total = 0, $Funds = '',
      $CreditCardID = 0, $CreditCard = '', $BankID = 0, $BankAccount = ''
      ) {
      if ($BankAccount == '') { $BankAccount = null; }
      if ($CreditCard == '') { $CreditCard = null; }
      $this->Frequency = $Frequency;
      $this->StartDate = $StartDate;
      $this->EndDate = $EndDate;
      $this->Total = $Total;
      $this->Funds = $Funds;
      $this->CreditCardID = $CreditCardID;
      $this->CreditCard = $CreditCard;
      $this->BankID = $BankID;
      $this->BankAccount = $BankAccount;
      } */
}

?>