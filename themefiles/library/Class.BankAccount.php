<?php
namespace NCSSERVICES;

define('EG_BANK_ACCOUNT_SERVICE', WS_HOST.'bankaccountservice.svc/bankaccountservice/');
$EG_BANK_ACCOUNT_LIST = array('url'=>EG_BANK_ACCOUNT_SERVICE.'list', 'method'=>'GET');
$EG_BANK_ACCOUNT_GET = array('url'=>EG_BANK_ACCOUNT_SERVICE.'%s', 'method'=>'GET');
$EG_BANK_ACCOUNT_ADD = array('url'=>EG_BANK_ACCOUNT_SERVICE.'add', 'method'=>'POST');
$EG_BANK_ACCOUNT_UPDATE = array('url'=>EG_BANK_ACCOUNT_SERVICE.'%s', 'method'=>'PUT');
$EG_BANK_ACCOUNT_DELETE = array('url'=>EG_BANK_ACCOUNT_SERVICE.'%s', 'method'=>'DELETE');

class BankAccount extends Entity {
	public $ID = 0; public $MemberID = 0; public $AccountNumber = ''; public $RoutingNumber = '';
	public $AccountHolderName = ''; public $BankName = ''; public $Type = '';
        public function __construct($AccountNumber = '', $RoutingNumber = '', $AccountHolderName = '',
		$BankName = '', $Type = ''
	) {
		$this->AccountNumber = $AccountNumber;
		$this->RoutingNumber = $RoutingNumber;
		$this->AccountHolderName = $AccountHolderName;
		$this->BankName = $BankName;
		$this->Type = $Type;
	}
	/*function BankAccount($AccountNumber = '', $RoutingNumber = '', $AccountHolderName = '',
		$BankName = '', $Type = ''
	) {
		$this->AccountNumber = $AccountNumber;
		$this->RoutingNumber = $RoutingNumber;
		$this->AccountHolderName = $AccountHolderName;
		$this->BankName = $BankName;
		$this->Type = $Type;
	}*/
}
?>