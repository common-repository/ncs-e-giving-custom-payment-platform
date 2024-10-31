<?php
namespace NCSSERVICES;

define('EG_FUND_SERVICE', WS_HOST.'fundservice.svc/fundservice/');
$EG_ADD_FUND = array('url'=>EG_FUND_SERVICE.'add', 'method'=>'POST');
$EG_LIST_FUND = array('url'=>EG_FUND_SERVICE.'list', 'method'=>'GET');
$EG_FUND_DETAILS = array('url'=>EG_FUND_SERVICE.'GetFundDetails', 'method'=>'GET');

class Fund extends Entity {
	public $ID = 0; public $Name = ''; public $ShortName = ''; public $SortOrder = 0; public $TaxDeductible = 1;
        public function __construct($Name = '', $ShortName = '', $SortOrder = 0, $TaxDeductible = 1) {
		$this->Name = $Name;
		$this->ShortName = $ShortName;
		$this->SortOrder = $SortOrder;
		$this->TaxDeductible = $TaxDeductible;
	}
        
	/*function Fund($Name = '', $ShortName = '', $SortOrder = 0, $TaxDeductible = 1) {
		$this->Name = $Name;
		$this->ShortName = $ShortName;
		$this->SortOrder = $SortOrder;
		$this->TaxDeductible = $TaxDeductible;
	}*/
}
?>