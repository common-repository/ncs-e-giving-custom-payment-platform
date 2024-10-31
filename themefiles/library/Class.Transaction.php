<?php
namespace NCSSERVICES;

define('EG_TRANSACTION_SERVICE', WS_HOST.'transactionservice.svc/transactionservice/');
$EG_GET_TRANSACTION_LIST = array('url'=>EG_TRANSACTION_SERVICE.'list?date=%s&memid=%s', 'method'=>'GET');
$EG_GUEST_TRANSACTION = array('url'=>EG_TRANSACTION_SERVICE.'process/guest/creditcard', 'method'=>'POST');
$EG_MEMBER_CC_TRANSACTION = array('url'=>EG_TRANSACTION_SERVICE.'process/member/creditcard', 'method'=>'POST');
$EG_MEMBER_ACH_TRANSACTION = array('url'=>EG_TRANSACTION_SERVICE.'process/member/bankaccount', 'method'=>'POST');
$EG_SEND_TRANSACTION_EMAIL = array('url'=>EG_TRANSACTION_SERVICE.'/sendtransactionemail/%s', 'method'=>'POST');

class Transaction extends Entity {
	public $ID = 0; public $MemberID = 0; public $OrganizationID = 0; public $Date = '1/1/1900';
	public $Total = 0; public $Fee = 0; public $Status = ''; public $Email = ''; public $Phone;
	public $Funds = array(); public $PaymentMethod; public $BankAccount; public $CreditCard;
        
        public function __construct($Total = 0, $Email = '', $Phone = '', $Funds = '', $BankAccount = '', $CreditCard = '') {
		if ($BankAccount == '') { $BankAccount = null; }
		if ($CreditCard == '') { $CreditCard = null; }
		$this->Total = $Total;
		$this->Email = $Email;
		$this->Phone = $Phone;
		$this->Funds = $Funds;
		$this->BankAccount = $BankAccount;
		$this->CreditCard = $CreditCard;		
	}
        
        
	/*function Transaction($Total = 0, $Email = '', $Phone = '', $Funds = '', $BankAccount = '', $CreditCard = '') {
		if ($BankAccount == '') { $BankAccount = null; }
		if ($CreditCard == '') { $CreditCard = null; }
		$this->Total = $Total;
		$this->Email = $Email;
		$this->Phone = $Phone;
		$this->Funds = $Funds;
		$this->BankAccount = $BankAccount;
		$this->CreditCard = $CreditCard;		
	}*/
}

class TransactionFund extends Entity {
	public $Name = ''; public $Amount = 0; public $ShortName = ''; public $TaxDeductible = 1;
        
        public function __construct($Name = '', $Amount = 0, $ShortName = '', $TaxDeductible = 1) {
		$this->Name = $Name;
		$this->Amount = $Amount;
		$this->ShortName = $ShortName;
		$this->TaxDeductible = $TaxDeductible;
	}
        
	/*function TransactionFund($Name = '', $Amount = 0, $ShortName = '', $TaxDeductible = 1) {
		$this->Name = $Name;
		$this->Amount = $Amount;
		$this->ShortName = $ShortName;
		$this->TaxDeductible = $TaxDeductible;
	}*/
}
?>