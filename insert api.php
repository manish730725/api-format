<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
error_reporting(E_ALL);
ini_set('display_errors', '1');
include_once ('dbConnect.php'); 

class props {
   protected $http_method;
   protected $require_fields = [
       "dealerapikey",
       "acref",
       "lender"
   ];
   protected $error;
   protected $error_fields;
   protected $getdata;
   protected $status;
   protected $json_response;
   protected $sql;
   protected $sql_check;
   protected $response;
   protected $con;
   
}

class api extends props{
    function __construct()
    {
        $this->con = new Dbconnect();
        $this->con = $this->con->__construct();
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        //check request method
        if($this->http_method == "POST")
        {
            $this->insertData();
        }
        
    }
    function insertData()
    {
        $this->getdata = file_get_contents("php://input");
        $requestData = $this->jsonConvertArray($this->getdata);
        $this->status = $this->verifyRequiredParams($requestData,$this->require_fields);
        if($this->status == 1)
        {
           $dealerapikey = trim($requestData['dealerapikey']);
           $acref = trim($requestData['acref']);
           $lender = trim($requestData['lender']);
           $tier = trim($requestData['tier']);
           $apr = trim($requestData['apr']);
           $flatrate = trim($requestData['flatrate']);
           $registration = trim($requestData['registration']);
           $cashprice = trim($requestData['cashprice']);
           $deposit = trim($requestData['deposit']);
           $settlement = trim($requestData['settlement']);
           $balancetofinance = trim($requestData['balancetofinance']);
           $partexchange = trim($requestData['partexchange']);
           $term = trim($requestData['term']);
           $monthlypayment = trim($requestData['monthlypayment']);
           $maxadvance = trim($requestData['maxadvance']);
           $conditions = trim($requestData['conditions']);
           $annualmileage = trim($requestData['annualmileage']);
           $residualvalue = trim($requestData['residualvalue']);
           $totalpayable = trim($requestData['totalpayable']);
           $product = trim($requestData['product']);
           $this->sql_check = mysqli_query($this->con , "SELECT * FROM `approval_info_new` WHERE `acref`='$acref' AND `lender`='$lender' AND `tier`='$tier' AND `dealerapikey`='$dealerapikey'");
           if(mysqli_num_rows($this->sql_check)>0)
	       {
                $response_data = mysqli_fetch_array($this->sql_check);
                $id=$response_data['id'];
                $date = date('Y-m-d H:i:s');
                $this->sql = "UPDATE `approval_info_new` SET `dealerapikey`='$dealerapikey',`acref`='$acref',`lender`='$lender',`apr`='$apr',`flatrate`='$flatrate', `registration`='$registration',`cashprice`='$cashprice',`deposit`='$deposit',`settlement`='$settlement',`balancetofinance`='$balancetofinance',`partexchange`='$partexchange',`term`='$term',`monthlypayment`='$monthlypayment',`maxadvance`='$maxadvance',`conditions`='$conditions',`annualmileage`='$annualmileage',`residualvalue`='$residualvalue',`totalpayable`='totalpayable',`product`='product',`tier`='$tier',`updated_at`='$date' WHERE `id`='$id'";
                $this->response = $this->con->query($this->sql);
                if($this->response)
                {
                    $this->response(200,"Approval Updated Successfully");
                }
                else{
                    $this->response(500,"Database Error !");
                }
           }
           else{
                $this->sql = "INSERT INTO `approval_info_new`(dealerapikey,acref,lender,apr,flatrate,registration,cashprice,deposit,settlement,balancetofinance,partexchange,term,monthlypayment,maxadvance,conditions,annualmileage,residualvalue,totalpayable,product,tier) VALUES('$dealerapikey','$acref','$lender','$apr','$flatrate','$registration','$cashprice','$deposit','$settlement','$balancetofinance','$partexchange','$term','$monthlypayment','$maxadvance','$conditions','$annualmileage','$residualvalue','$totalpayable','$product','$tier')";
                $this->response = $this->con->query($this->sql);
                if($this->response)
                {
                    $this->response(200,"Approval Saved Successfully");
                }
                else{
                    $this->response(500,"Database Error !");
                }
            }   
        }
        else{
            $this->response(400,$this->status);
           
        }
    }

    //check request data match from database column
    function verifyRequiredParams($request_params,$required_fields)
    {
        $response_data=array();
        $this->error = false;
        $this->error_fields = "";
        foreach ($required_fields as $field) {
            if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $this->error = true;
            $this->error_fields .= $field . ', ';
            }
        }
        if ($this->error) {
            $message='Required field(s) ' . substr($this->error_fields, 0, -2) . ' is missing or empty';
            $response_data['message']=$message;
            return $response_data;
        }
        else
        {
            return true;
        }
    }

    //api response
    function response($response_code,$result){
        
        $response['response_code'] = $response_code;
        $response['Result'] = $result;
        $this->json_response = json_encode($response);
        echo $this->json_response;
    }
   //check json or not 
    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    //json convert into array
    function jsonConvertArray($data){
        $this->getdata = $data;
        if($this->getdata  === FALSE)
        {
            response(404,"Please send data!");
        }
        else {
            $check= $this->isJson($this->getdata);
            if($check==1)
            {
                $request= json_decode($this->getdata,true);
            } 
            else
            {
                $this->getdata = substr($this->getdata, 1, -1);
                $asArr = explode(',',$this->getdata );
                foreach( $asArr as $val ){
                    $tmp = explode( ':', trim(str_replace(array('\n', '\r','"'), '', $val)),2);
                    
                    if ( !isset($tmp[1])) {
                        $finalArray[ $tmp[0] ] = null;
                     }
                     else{
                        $finalArray[$tmp[0]] = $tmp[1];
                     }
                }
                $request= $finalArray;
                $request = array_map('trim', $request);
            }
            return $request;
        }
    }
}

new api();

?>