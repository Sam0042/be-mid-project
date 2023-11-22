<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Validator;
use Illuminate\Support\Str;
use App\Rules\ValidateStatusInput;
use Illuminate\Support\Facades\Schema;

class Responsing{
    public $query;
    public $message;
    public $statusCode;

    public $filteringTarget;
    public $filteringValue;

    public $sortColumn;
    public $sortOrder;

    function setQuery($query){
        $this->query = $query;
    }
    function setMessage($message){
        $this->message = $message;
    }
    function setStatusCode($statusCode){
        $this->statusCode = $statusCode;
    }
    function setSortColumn($sortColumn){
        $this->sortColumn = $sortColumn;
    }
    function setSortOrder($sortOrder){
        $this->sortOrder = $sortOrder;
    }
    #FILTERING
    function setFilteringTarget($filteringTarget){
        $this->filteringTarget = $filteringTarget;
    }
    function setFilteringValue($filteringValue){
        $this->filteringValue = $filteringValue;
    }

    #<--ACTION FUNCTION-->
    #GET MESSAGE ONLY
    function getMsgOnly(){
        $data = [
            "message"=> $this->message
        ];
        return response()->json($data,$this->statusCode);
    }
    #GET MESSAGE AND DATA
    function getMsgAndData(){
        $data = [
            "message"=> $this->message,
            "data"=> $this->query
        ];
        return response()->json($data,$this->statusCode);
    }
    #IS TRUE?
    function isTrue(){
        if(Schema::hasColumn((new Patient())->getTable(),$this->sortColumn)){
            if($this->sortOrder == "asc" or $this->sortOrder == "desc"){
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
    #FILTERING
    function filtering(){
        $filteringValue = $this->filteringValue;
        $filteringTarget = $this->filteringTarget;

        $gquery = Patient::query();
        $filtering = Patient::where($filteringTarget,"like","%$filteringValue%")->get();
        
        if($filteringValue){
            if($filtering){
                $data = [
                    "message" => "Filtering students by $filteringTarget",
                    "data" => $filtering
                ];
                return response()->json($data,$this->statusCode); 
            }
            elseif(!$filtering){
                return "a";
            }
        }
        else{
            $getFilteringTargetValueEmptyError = new Responsing();
            $getFilteringTargetValueEmptyError->setMessage("Please fill the filtering value");
            $getFilteringTargetValueEmptyError->setStatusCode(404);
            return $getFilteringTargetValueEmptyError->getMsgOnly();
        }
        
    }
    #SORTING
    function getSort(){
        $query = Patient::query();
        $query->orderBy($this->sortColumn,$this->sortOrder);
        $data = [
            "message" => $this->message,
            "data" => $query->paginate(5) #PENERAPAN PAGINATION :-)
        ];
        return response()->json($data,$this->statusCode);
    }
    
}

class PatientsController extends Controller
{

    #Get all resource
    public function index(Request $request){
            $gquery = Patient::query();
            $grequest= $request;

        #function validasi apakah value request sort ada di database
        function columnExist($sortColumn,$sortOrder,$message1,$message2,$statusCode){
            if(Schema::hasColumn((new Patient())->getTable(),$sortColumn)){
                if($sortOrder == "asc" or $sortOrder == "desc"){
                    $sortPatients = new Responsing();
                    $sortPatients->setSortColumn($sortColumn);
                    $sortPatients->setSortOrder($sortOrder);
                    $sortPatients->setMessage("Sorting patients");
                    $sortPatients->setStatusCode(200);
                    return $sortPatients->getSort();    
                }
                else{
                    $someError = new Responsing();
                    $someError->setMessage($message2);
                    $someError->setStatusCode($statusCode);
                    return $someError->getMsgOnly();
                }
            }
            else{
                $someError = new Responsing();
                $someError->setMessage($message1);
                $someError->setStatusCode($statusCode);
                return $someError->getMsgOnly();
            }
        }
        

        #SORTING LOGIC
        #apakah request mempunyai kata sort
        if ($request->has('sort')) {

            #mendefinisikan value sort dan order ke dalam variabel
            $sortOrder = $request->input('order');
            $sortColumn = $request->input('sort');

            #validasi jika sort true dan order false
            if($sortColumn and !$sortOrder){
                $getOrderEmptyError = new Responsing();
                $getOrderEmptyError->setMessage("Please fill the order value");
                $getOrderEmptyError->setStatusCode(404);
                return $getOrderEmptyError->getMsgOnly();
            }

            #validasi jika sort false dan order true
            elseif(!$sortColumn and $sortOrder){
                $getSortEmptyError = new Responsing();
                $getSortEmptyError->setMessage("Please fill the sort value");
                $getSortEmptyError->setStatusCode(404);
                return $getSortEmptyError->getMsgOnly();
            }

            else{
                /*mendefinisikan fungsi isTrue ke variabel untuk mengecek apakah value sort 
                ada di tabel dan value order berisi asc atau desc
                 */
                $isSortColumnAndOrderTrue = new Responsing();
                $isSortColumnAndOrderTrue->setSortColumn($sortColumn);
                $isSortColumnAndOrderTrue->setSortOrder($sortOrder);
                $isColumnExist = $isSortColumnAndOrderTrue->isTrue();
                
                #memunculkan sort jika isTrue bernilai true
                if($isColumnExist){
                    $SortPatientsData = new Responsing();
                    $SortPatientsData->setSortColumn($sortColumn);
                    $SortPatientsData->setSortOrder($sortOrder);
                    $SortPatientsData->setMessage("Sorting patients");
                    $SortPatientsData->setStatusCode(200);
                    return $SortPatientsData->getSort();                     
                }
                else{
                    #validasi kondisi jika value sort adalah "tanggal_masuk"
                    if($sortColumn == "tanggal_masuk"){
                        $sortPatientsByTanggalMasuk = new Responsing();
                        $sortPatientsByTanggalMasuk->setSortColumn("in_date_at");
                        $sortPatientsByTanggalMasuk->setSortOrder($sortOrder);
                        $sortPatientsByTanggalMasuk->setMessage("Sorting patients by in date at");
                        $sortPatientsByTanggalMasuk->setStatusCode(200);
                        return $sortPatientsByTanggalMasuk->getSort();
                    }
                    #validasi kondisi jika value sort adalah "tanggal_keluar"
                    elseif($sortColumn == "tanggal_keluar"){
                        $sortPatientsByTanggalMasuk = new Responsing();
                        $sortPatientsByTanggalMasuk->setSortColumn("out_date_at");
                        $sortPatientsByTanggalMasuk->setSortOrder($sortOrder);
                        $sortPatientsByTanggalMasuk->setMessage("Sorting patients by out date at");
                        $sortPatientsByTanggalMasuk->setStatusCode(200);
                        return $sortPatientsByTanggalMasuk->getSort(); 
                    }
                    else{
                        #memunculkan eror antara value sort dan order yg kosong
                        $isColumnExist2 = columnExist($sortColumn,$sortOrder,"Sort value not found",
                        "Order value must be asc or desc",404);
                        return $isColumnExist2;
                    }
                }
            }

        }
        else{

            #FILTER LOGIC
            if($request){

                #FILTERING BY NAME
                if($request->has("name")){
                    $filterByName = new Responsing();
                    $filterByName->setFilteringTarget("name");
                    $filterByName->setFilteringValue($request->input("name"));
                    $filterByName->setStatusCode(200);
                    return $filterByName->filtering();
                }

                #FILTERING BY STATUS
                elseif($request->has("status")){
                    if($request->input("status") == "sembuh" or $request->input("status") == "positif" or $request->input("status") == "meninggal"){
                        $filterByStatus = new Responsing();
                        $filterByStatus->setFilteringTarget("status");
                        $filterByStatus->setFilteringValue($request->input("status"));
                        $filterByStatus->setStatusCode(200);
                        return $filterByStatus->filtering();
                    }
                    else{
                        $getStatusValueNotValidError = new Responsing();
                        $getStatusValueNotValidError->setMessage("Status value must be sembuh, positif, or meninggal");
                        $getStatusValueNotValidError->setStatusCode(404);
                        return $getStatusValueNotValidError->getMsgOnly();
                    }
                }

                #FILTERING BY ADDRESS
                elseif($request->has("address")){
                    $filterByAddress = new Responsing();
                    $filterByAddress->setFilteringTarget("address");
                    $filterByAddress->setFilteringValue($request->input("address"));
                    $filterByAddress->setStatusCode(200);
                    return $filterByAddress->filtering();
                }
                
                else{
                    #Get All patients
                    $patients = Patient::all();
                    if($patients){
                        $getAllPatients = new Responsing();
                        $getAllPatients->setQuery($patients);
                        $getAllPatients->setMessage("Get all patients");
                        $getAllPatients->setStatusCode(200);
                        return $getAllPatients->getMsgAndData();
                    }
                    else{
                        $getDataNotAvailableError = new Responsing();
                        $getDataNotAvailableError->setMessage("Data not available");
                        $getDataNotAvailableError->setStatusCode(404);
                        return $getDataNotAvailableError->getMsgOnly();
                    }
            }

                
            }
        }
    }

    #Add resource
    public function store(Request $request){

        $possibleStatus = ["sembuh","positif","meninggal"];
        $validateStatus = new ValidateStatusInput($possibleStatus);

        $inputRules = [
            "name"=>"required|regex:/^[\pL\s\-]+$/u|max:255",
            "phone"=>"required|numeric|digits:12|",
            "address"=>"required|max:255",
            "status"=> ["required","regex:/^[\pL\s\-]+$/u","max:100",$validateStatus],
            "in_date_at"=>"required|date_format:Y-m-d",
            "out_date_at"=>"required|date_format:Y-m-d"
        ];
        $inputValidator = Validator::make($request->all(),$inputRules);
        if($inputValidator->fails()){
            $errorMassage = $inputValidator->errors();
            return response()->json($errorMassage,400);
        }
        else{
            $patient = Patient::create($request->all());
            $createPatientData = new Responsing();
            $createPatientData->setQuery($patient);
            $createPatientData->setMessage("Patient is created succesfully");
            $createPatientData->setStatusCode(201);
            return $createPatientData->getMsgAndData();
        }   
    }

    #Get detail resource
    public function show(Request $request,$id){
        $patients = Patient::find($id);
        
        if ($patients){
            $getDetailPatient = new Responsing();
            $getDetailPatient->setQuery($patients);
            $getDetailPatient->setMessage("Get detail patient");
            $getDetailPatient->setStatusCode(200);
            return $getDetailPatient->getMsgAndData();
        }   
        else{
            $getPatientNotFoundError = new Responsing();
            $getPatientNotFoundError->setMessage("Patient id not found");
            $getPatientNotFoundError->setStatusCode(404);
            return $getPatientNotFoundError->getMsgOnly();    
        }
    }

    #Edit resource
    public function update(Request $request,$id){
        $possibleStatus = ["sembuh","positif","meninggal"];
        $validateStatus = new ValidateStatusInput($possibleStatus);
        $patient = Patient::find($id);

        $inputRules = [
            "name"=>"regex:/^[\pL\s\-]+$/u|max:255" ??$patient->name,
            "phone"=>"numeric|digits:12|" ??$patient->phone,
            "address"=>"max:255" ?? $patient->address,
            "status"=> ["regex:/^[\pL\s\-]+$/u","max:100",$validateStatus] ??$patient->status,
            "in_date_at"=>"date_format:Y-m-d" ?? $patient->in_date_at,
            "out_date_at"=>"date_format:Y-m-d" ?? $patient->out_date_at
        ];

        $inputValidator = Validator::make($request->all(),$inputRules);
        
        
        if($patient){
            if($inputValidator->fails()){
                $errorMassage = $inputValidator->errors();
                return response()->json($errorMassage,400);
            }
            else{
                #hasil betul
                $input = [
                    "name"=> $request->name ?? $patient->name,
                    "phone"=> $request->phone ?? $patient->phone,
                    "address"=> $request->address ?? $patient->address,
                    "status"=> $request->status ?? $patient->status,
                    "in_date_at"=> $request->in_date_at ?? $patient->in_date_at,
                    "out_date_at"=> $request->out_date_at ?? $patient->out_date_at
                ];
                $patient->update($input);

                $updatePatientData = new Responsing();
                $updatePatientData->setQuery($patient);
                $updatePatientData->setMessage("Patient data has been updated");
                $updatePatientData->setStatusCode(200);
                return $updatePatientData->getMsgAndData();
            }   
        }
        else{
            $getPatientIdNotFoundError = new Responsing();
            $getPatientIdNotFoundError->setMessage("Patient id not found");
            $getPatientIdNotFoundError->setStatusCode(404);
            return $getPatientIdNotFoundError->getMsgOnly();
        }
    }

    #Delete resource
    public function destroy(Request $request,$id){
        $patient = Patient::find($id);
        if($patient){
            $patient->delete();
            $getDeletedPatientDataMessage = new Responsing();
            $getDeletedPatientDataMessage->setMessage("Patient data has been deleted");
            $getDeletedPatientDataMessage->setStatusCode(200);
            return $getDeletedPatientDataMessage->getMsgOnly();

        }
        else{
            $getPatientIdNotFoundError = new Responsing();
            $getPatientIdNotFoundError->setMessage("Patient id not found");
            $getPatientIdNotFoundError->setStatusCode(404);
            return $getPatientIdNotFoundError->getMsgOnly();
        }
    }
};
