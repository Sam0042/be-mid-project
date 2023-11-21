<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Validator;

class PatientsController extends Controller
{
    //Get all resource
    public function index(Request $request){
        //query builder
        // $query = Patient::query();
        //function query get
        function qry($sortColumn,$sortDirection,$message,$statusCode){
            $query = Patient::query();
            $query->orderBy($sortColumn,$sortDirection);
            $data = [
                "message" => $message,
                "data" => $query->get()
            ];
            return response()->json($data,$statusCode);
        }

        // sorting
        if ($request->has('sort')) {

            $sortDirection = $request->input('order','asc');
            $sortColumn = $request->input('sort');

            if($sortColumn == "tanggal_masuk"){
                $query = qry("in_date_at",$sortDirection,"Sorting patients by in date at",200);
                return $query;
                
                // $query->orderBy("in_date_at", $sortDirection);
                // $data = [
                //     "message" => "Sorting patients",
                //     "data" => $query->get()
                // ];
                // return response()->json($data,200);
            }
            elseif($sortColumn == "tanggal_keluar"){
                $query = qry("out_date_at",$sortDirection,"Sorting patients by out date at",200);
                return $query;
                // $query->orderBy("out_date_at", $sortDirection);
                // $data = [
                //     "message" => "Sorting patients",
                //     "data" => $query->get()
                // ];
                // return response()->json($data,200);
            }
            elseif(!$sortColumn){
                $data = [
                    "message" => "Please fill the sort value",
                ];
                return response()->json($data,404);
            }
            elseif(!$sortDirection){
                $data = [
                    "message" => "Please fill the order value",
                ];
                return response()->json($data,404);
            }
            else{
                $query->orderBy($sortColumn, $sortDirection);
                $data = [
                    "message" => "Sorting patients",
                    "data" => $query->get()
                ];
                return response()->json($data,200);
            }

            
        }
        else{
            //filtering by name
            if($request->has('name')){
                $name = $request->input('name');

                $data = [
                    "message" => "Filtering students by name",
                    "data" => $query->where('name', $name)->get()
                ];
                return response()->json($data,200);
            }

            //filtering by address
            elseif($request->has('address')){
                $address = $request->input('address');

                $data = [
                    "message" => "Filtering students by address",
                    "data" => $query->where('address', $address)->get()
                ];
                return response()->json($data,200);
            }

            //filtering by status
            elseif($request->has('status')){
                $status = $request->input('status');

                $data = [
                    "message" => "Filtering students by status",
                    "data" => $query->where('status', $status)->get()
                ];
                return response()->json($data,200);
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
