<?php
namespace NCSSERVICES;

define('EG_CREDIT_CARD_SERVICE', WS_HOST.'creditcardservice.svc/creditcardservice/');
//$EG_BANK_ACCOUNT_LIST = array('url'=>EG_CREDIT_CARD_SERVICE.'list', 'method'=>'GET');
//$EG_BANK_ACCOUNT_GET = array('url'=>EG_CREDIT_CARD_SERVICE.'{ID}', 'method'=>'GET');
//$EG_BANK_ACCOUNT_ADD = array('url'=>EG_CREDIT_CARD_SERVICE.'add', 'method'=>'POST');
//$EG_BANK_ACCOUNT_UPDATE = array('url'=>EG_CREDIT_CARD_SERVICE.'{ID}', 'method'=>'PUT');
//$EG_BANK_ACCOUNT_DELETE = array('url'=>EG_CREDIT_CARD_SERVICE.'{ID}', 'method'=>'DELETE');

class CreditCard extends Entity {
	public $ID = 0; public $MemberID = 0; public $Description = ''; public $Number = ''; public $CVV = '';
	public $ExpirationMonth = ''; public $ExpirationYear = ''; public $CardHolderName = '';
	public $BillingAddress = ''; public $BillingCity = ''; public $BillingState = ''; public $BillingZip = '';
	public $Type = ''; public $CreateDate = '1/1/1900'; public $LastModified = '1/1/1900';
	public $LastUsed = '1/1/1900';
        
        public function __construct($Description = '', $Number = '', $CVV = '', $ExpirationMonth = '', $ExpirationYear = '',
		$CardHolderName = '', $BillingAddress = '', $BillingCity = '', $BillingState = '', $BillingZip = ''
	) {
		$this->Description = $Description;
		$this->Number = $Number;
		$this->CVV = $CVV;
		$this->ExpirationMonth = $ExpirationMonth;
		$this->ExpirationYear = $ExpirationYear;
		$this->CardHolderName = $CardHolderName;
		$this->BillingAddress = $BillingAddress;
		$this->BillingCity = $BillingCity;
		$this->BillingState = $BillingState;
		$this->BillingZip = $BillingZip;
	}
        
	/*function CreditCard($Description = '', $Number = '', $CVV = '', $ExpirationMonth = '', $ExpirationYear = '',
		$CardHolderName = '', $BillingAddress = '', $BillingCity = '', $BillingState = '', $BillingZip = ''
	) {
		$this->Description = $Description;
		$this->Number = $Number;
		$this->CVV = $CVV;
		$this->ExpirationMonth = $ExpirationMonth;
		$this->ExpirationYear = $ExpirationYear;
		$this->CardHolderName = $CardHolderName;
		$this->BillingAddress = $BillingAddress;
		$this->BillingCity = $BillingCity;
		$this->BillingState = $BillingState;
		$this->BillingZip = $BillingZip;
	}*/
}
?>