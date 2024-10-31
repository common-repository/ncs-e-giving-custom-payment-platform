<?php
namespace NCSSERVICES;

//define('WS_HOST', 'https://api.e-giving.org/');
//define('WS_HOST', 'https://devapi.e-giving.org/');
define('WS_HOST', $AdminDetails["wshost"]);
define('WS_XMLNS', ' xmlns="http://schemas.datacontract.org/2004/07/NCS.eGiving.ServiceLibrary" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"');

class Entity {

    function toJson() {
        return json_encode($this);
    }

}

class eg_ws_call {

    public $err = '';
    public $location = '';
    public $method = 'GET';
    public $format = 'json';

    public function do_ws_call($s_ws, $s_entity = '', $s_session_id = '') {
        $arr_headers = array();
        if ($s_ws == '') {
            $this->err = 'Invalid call';
            return false;
        } elseif (!$this->verb_allowed($this->method)) {
            $this->err = 'Method "' . $method . '" not allowed';
            return false;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_URL, $s_ws);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);

        /*         * *************************************************************************
          OPTIONAL - Identitify yourself to the API for logging
         * ************************************************************************* */
        //curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US)');

        /*         * *************************************************************************
          OPTIONAL - To debug cURL connections
         * ************************************************************************* */
        //$verbose = fopen('php://temp', 'rw+');
        //curl_setopt($curl, CURLOPT_STDERR, $verbose);

        if (strtolower($this->format) == 'json') {
            array_push($arr_headers, 'Content-type: application/json', 'Accept: application/json');
        } else {
            array_push($arr_headers, 'Content-type: application/xml');
        }
        if ($s_session_id != '') {
            array_push($arr_headers, 'SessionID: ' . $s_session_id);
        }
        if (strtoupper($this->method) == "POST" || strtoupper($this->method) == "PUT") {
            array_push($arr_headers, 'Content-Length: ' . strlen($s_entity));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $s_entity);
        }
        if (count($arr_headers) > 0) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $arr_headers);
        }
        $response = curl_exec($curl);

        /*         * *************************************************************************
          OPTIONAL - To debug cURL connections
         * ************************************************************************* */
        //!rewind($verbose);
        //$verboseLog = stream_get_contents($verbose);
        //echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
        $return_val = false;
        if (curl_error($curl) != '') {
            $this->err = 'Errno: ' . curl_errno($curl) . ' - ' . curl_error($curl);
        }
        $response_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        list($header, $body) = explode("\r\n\r\n", $response, 2);
        if ($response_status == '401') {
            $this->err = 'Invalid session';
        } elseif ($response_status == '400') {
            if (preg_match('/ErrorDescription:(.*)\b/', $header, $err_desc)) {
                $this->err = $err_desc[1];
            } else {
                $this->err = 'Bad request';
            }
        } elseif ($response_status == '404') {
            $this->err = 'Resource not found';
        } elseif ($response_status == '405') {
            $this->err = 'Method not allowed';
        } elseif ($response_status == '500') {
            $this->err = 'Server error';
        } elseif ($response_status == '503') {
            $this->err = 'Service unavailable';
        } elseif ($response_status == '504') {
            $this->err = 'The request timed out';
        } elseif ($response_status == '200') {
            $return_val = $body;
        } elseif ($response_status == '201') {
            if (preg_match('/Location:(.*)\b/', $header, $location)) {
                $this->location = WS_HOST . ltrim($location[1], ' /');
            }
            $return_val = true;
        } else {
            $this->err = 'Status code: ' . $response_status;
        }
        curl_close($curl);
        return $return_val;
    }

    public function set_params($s_url = '', $s_params = array()) {
        if (is_array($s_params)) {
            foreach ($s_params as $k => $v) {
                $s_url = str_replace($s_url, "{" . $k . "}", urlencode($v));
            }
        }
        $s_url = preg_replace('\{.*?\}', '', $s_url);
        return $s_url;
    }

    private function verb_allowed($s_verb = '') {
        $s_verb = strtoupper($s_verb);
        if ($s_verb == "GET" || $s_verb == "POST" || $s_verb == "PUT" || $s_verb == "DELETE") {
            return true;
        }
        return false;
    }

}

?>