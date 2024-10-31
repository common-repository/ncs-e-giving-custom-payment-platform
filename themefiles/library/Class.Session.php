<?php 
namespace NCSSERVICES;

define('EG_SESSION_SERVICE', WS_HOST.'sessionservice.svc/sessionservice/');

class Session extends Entity {
	public $Token = ''; public $Username = ''; public $Password = '';
        public function __construct($Token = '', $Username = '', $Password = ''){
          
		$this->Token = $Token;
		$this->Username = $Username;
		$this->Password = $Password;
            
        }
	/*public  function Session($Token = '', $Username = '', $Password = '') {
		$this->Token = $Token;
		$this->Username = $Username;
		$this->Password = $Password;
	}*/
        function AdminSession(){
            $EG_START_ADMIN_SESSION = array('url'=>EG_SESSION_SERVICE.'start/org', 'method'=>'POST');
            return $EG_START_ADMIN_SESSION;
        }
        function MemberSession(){
            $EG_START_MEMBER_SESSION = array('url'=>EG_SESSION_SERVICE.'start/member', 'method'=>'POST');
            return $EG_START_MEMBER_SESSION;
        }
        function DeleteSession(){
            $EG_END_SESSION = array('url'=>EG_SESSION_SERVICE.'end', 'method'=>'DELETE');
            return $EG_END_SESSION;
        }
}
?>