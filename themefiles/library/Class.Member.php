<?php
namespace NCSSERVICES;

define('EG_MEMBER_SERVICE', WS_HOST.'memberservice.svc/memberservice/');
$EG_MEMBER_ADD = array('url'=>EG_MEMBER_SERVICE.'add', 'method'=>'POST');
$EG_GET_MEMBER_DETAILS = array('url'=>EG_MEMBER_SERVICE.'current', 'method'=>'GET');

class Member extends Entity {
	public $ID = 0; public $OrgID = 0; public $Username = ''; public $Password = ''; public $FullName = '';
	public $FirstName = '';	public $MiddleInitial = ''; public $LastName = ''; public $Address = '';
	public $City = ''; public $State = ''; public $Zip = ''; public $Country = ''; public $Phone = ''; 
	public $Email = ''; public $MembershipNumber = ''; public $ACHAuthorized = 1; public $ACHAllowed = 1;
	public $Enabled = 0; public $SignupDate = '1/1/1900'; public $LastLogin = '1/1/1900';
        
        public function __construct($Username = '', $Password = '', $FirstName = '', $MiddleInitial = '', $LastName = '', $Address = '',
		$City = '', $State = '', $Zip = '', $Country = '', $Phone = '', $Email = '', $MembershipNumber = ''
	) {
		$this->Username = $Username;
		$this->Password = $Password;
		$this->FirstName = $FirstName;
		$this->MiddleInitial = $MiddleInitial;
		$this->LastName = $LastName;
		$this->Address = $Address;
		$this->City = $City;
		$this->State = $State;
		$this->Zip = $Zip;
		$this->Country = $Country;
		$this->Phone = $Phone;
		$this->Email = $Email;
		$this->MembershipNumber = $MembershipNumber;
	}
        
	/*function Member($Username = '', $Password = '', $FirstName = '', $MiddleInitial = '', $LastName = '', $Address = '',
		$City = '', $State = '', $Zip = '', $Country = '', $Phone = '', $Email = '', $MembershipNumber = ''
	) {
		$this->Username = $Username;
		$this->Password = $Password;
		$this->FirstName = $FirstName;
		$this->MiddleInitial = $MiddleInitial;
		$this->LastName = $LastName;
		$this->Address = $Address;
		$this->City = $City;
		$this->State = $State;
		$this->Zip = $Zip;
		$this->Country = $Country;
		$this->Phone = $Phone;
		$this->Email = $Email;
		$this->MembershipNumber = $MembershipNumber;
	}*/
        
        function GetMemberDetails(){
            $EG_GET_MEMBER_DETAILS = array('url'=>EG_MEMBER_SERVICE.'current', 'method'=>'GET');
            return $EG_GET_MEMBER_DETAILS;
        }
}
?>