<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Validator;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Schema;

class ErrorMsg{
    public $message;
    public $statusCode;

    function setMessage($message){
        $this->message = $message;
    }
    function setStatusCode($statusCode){
        $this->statusCode = $statusCode;
    }
    function getErrorMsg(){
        $message = $this->message;
        $statusCode = $this->statusCode;
        $data = [
            "message" => $message,
        ];
        return response()->json($data,$statusCode);
    }
    
}

class Filtering{
    public $filteringTarget;
    public $filteringValue;
    public $statusCode;

    function setFilteringTarget($filteringTarget){
        $this->filteringTarget = $filteringTarget;
    }
    function setFilteringValue($filteringValue){
        $this->filteringValue = $filteringValue;
    }
    function setStatusCode($statusCode){
        $this->statusCode = $statusCode;
    }
    function filtering(){
        $filteringValue = $this->filteringValue;
        $filteringTarget = $this->filteringTarget;
        $sCode = $this->statusCode;

        $gquery = Patient::query();
        $filtering = Patient::where($filteringTarget,"like","%$filteringValue%")->get();
        
        if($filteringValue){
            if($filtering){
                $data = [
                    "message" => "Filtering students by $filteringTarget",
                    "data" => $filtering
                ];
                return response()->json($data,$sCode); 
            }
            elseif(!$filtering){
                return "a";
            }
        }
        else{
            return "err";
        }
        
    }
}
class PatientsController extends Controller
{
    //Get all resource
    public function index(Request $request){
            $gquery = Patient::query();
            $grequest= $request;


        //FUNCTION LOGIC

        //function get sort data
        function getSort($sortColumn,$sortDirection,$message,$statusCode){
            $query = Patient::query();
            $query->orderBy($sortColumn,$sortDirection);
            $data = [
                "message" => $message,
                "data" => $query->get()
            ];
            return response()->json($data,$statusCode);
        }

        //function get error message
        function getErrorMsg($message,$statusCode){
            $data = [
                "message" => $message,
            ];
            return response()->json($data,$statusCode);
        }

        //function validasi apakah value request sort ada di database
        function columnExist($sortColumn,$sortDirection,$message1,$message2,$statusCode){
            if(Schema::hasColumn((new Patient())->getTable(),$sortColumn)){
                if($sortDirection == "asc" or $sortDirection == "desc"){
                    $sort = getSort($sortColumn,$sortDirection,"Sorting patients",200);
                    return $sort; 
                }
                else{
                    $error = getErrorMsg($message2,$statusCode);
                    return $error;
                }
            }
            else{
                $error = getErrorMsg($message1,$statusCode);
                return $error;
            }
        }
        
        #function validasi apakah value order berisi asc atau desc
        function isTrue($sortColumn,$sortDirection,$message1,$message2,$statusCode){
            if(Schema::hasColumn((new Patient())->getTable(),$sortColumn)){
                if($sortDirection == "asc" or $sortDirection == "desc"){
                    return true;
                }
                else{
                    return false;
                }
            }
            else{
                return false;
            }
        }

        #SORTING LOGIC

        #apakah request mempunyai kata sort
        if ($request->has('sort')) {

            //mendefinisikan value sort dan order ke dalam variabel
            $sortDirection = $request->input('order');
            $sortColumn = $request->input('sort');

            //validasi jika sort true dan order false
            if($sortColumn and !$sortDirection){
                $error = getErrorMsg("Please fill the order value",404);
                return $error;
            }

            //validasi jika sort false dan order true
            elseif(!$sortColumn and $sortDirection){
                // $error = getErrorMsg("Please fill the sort value",404);
                // return $error;
                $isSortEmpty = new ErrorMsg();
                $isSortEmpty->setMessage("Please fill the sort value");
                $isSortEmpty->setStatusCode(404);
                return $isSortEmpty->getErrorMsg();
            }

            else{
                /*mendefinisikan fungsi isTrue ke variabel untuk mengecek apakah value sort 
                ada di tabel dan value order berisi asc atau desc
                 */
                $isColumnExist = isTrue($sortColumn,$sortDirection,"Sort value not found",
                "Order value must be asc or desc",404);
                
                //memunculkan sort jika isTrue bernilai true
                if($isColumnExist){
                    $sort = getSort($sortColumn,$sortDirection,"Sorting patients",200);
                    return $sort; 
                    
                }
                else{
                    //validasi kondisi jika value sort adalah "tanggal_masuk"
                    if($sortColumn == "tanggal_masuk"){
                        $sort = getSort("in_date_at",$sortDirection,"Sorting patients by in date at",200);
                        return $sort;
                    }
                    //validasi kondisi jika value sort adalah "tanggal_keluar"
                    elseif($sortColumn == "tanggal_keluar"){
                        $sort = getSort("out_date_at",$sortDirection,"Sorting patients by out date at",200);
                        return $sort;
                    }
                    else{
                        //memunculkan eror antara value sort dan order yg kosong
                        $isColumnExist2 = columnExist($sortColumn,$sortDirection,"Sort value not found",
                        "Order value must be asc or desc",404);
                        return $isColumnExist2;
                    }
                }
            }

        }
        else{

            #FILTER LOGIC
            if($request){

                #Filtering by name
                if($request->has("name")){
                    $filterByName = new Filtering();
                    $filterByName->setFilteringTarget("name");
                    $filterByName->setFilteringValue($request->input("name"));
                    $filterByName->setStatusCode(200);
                    return $filterByName->filtering();
                }

                #Filtering by address
                elseif($request->has("address")){
                    $filterByAddress = new Filtering();
                    $filterByAddress->setFilteringTarget("address");
                    $filterByAddress->setFilteringValue($request->input("address"));
                    $filterByAddress->setStatusCode(200);
                    return $filterByAddress->filtering();
                }

                #Filtering by status
                elseif($request->has("status")){
                    $filterByStatus = new Filtering();
                    $filterByStatus->setFilteringTarget("status");
                    $filterByStatus->setFilteringValue($request->input("status"));
                    $filterByStatus->setStatusCode(200);
                    return $filterByStatus->filtering();
                }
                
                else{
                    //Get All patients
                    $students = Patient::all();
                    $data = [
                        "message"=>"Get all patients",
                        "data"=>$students
                    ];
                    return response()->json($data,200);
                }

                
            }
        }
    }

    //Add resource
    public function store(Request $request){
        $inputRules = [
            "name"=>"required|regex:/^[\pL\s\-]+$/u|max:255",
            "phone"=>"required|numeric|digits:12",
            "address"=>"required|max:255",
            "status"=>"required|regex:/^[\pL\s\-]+$/u|max:100",
            "in_date_at"=>"required|date_format:Y-m-d",
            "out_date_at"=>"required|date_format:Y-m-d"
        ];
        $inputValidator = Validator::make($request->all(),$inputRules);
        if($inputValidator->fails()){
            $errorMassage = $inputValidator->errors();
            return response()->json($errorMassage,401);
        }
        else{
            $patient = Patient::create($request->all());

            $data=[  
                "message"=>"Patient is created succesfully",
                "data"=>$patient
            ];

            return response()->json($data,201);
        }   
    }

    //Get detail resource
    public function show(Request $request,$id){
        $patients = Patient::find($id);
        if ($patients){
            $data =[
                "message" => "Get detail patient",
                "data" => $patients
            ];
            return response()->json($data,200);
        }   
        else{
            $data = [
                "message" => "Patient not found",
            ];
            return response()->json($data,404);     
        }
    }

    //Edit resource
    public function update(Request $request,$id){
        $patient = Patient::find($id);
        if($patient){
            $input = [
                "name"=> $request->name ?? $patient->name,
                "phone"=> $request->phone ?? $patient->phone,
                "address"=> $request->address ?? $patient->address,
                "status"=> $request->status ?? $patient->status,
                "in_date_at"=> $request->in_date_at ?? $patient->in_date_at,
                "out_date_at"=> $request->out_date_at ?? $patient->out_date_at
            ];
            $patient->update($input);
            $data=[
                "message"=>"Patient data has been updated",
                "data"=>$patient
            ];
            return response()->json($data,200);
        }
        else{
            $data=[
                "message"=>"Patient data not found"
            ];
            return response()->json($data,404);
        }
    }

    //Delete resource
    public function destroy(Request $request,$id){
        $patient = Patient::find($id);
        if($patient){
            $patient->delete();

            $data=[
                "message"=>"Patient data index {$id} has been deleted"
            ];
            return response()->json($data,200);
        }
        else{
            $data=[
                "message"=>"Patient data not found"
            ];
            return response()->json($data,404);
        }
    }
};
