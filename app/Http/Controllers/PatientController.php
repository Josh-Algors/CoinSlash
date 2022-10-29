<?php

namespace App\Http\Controllers;

use App\Http\Services\NotificationService;
use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;

use Exception;

use App\Models\User;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\PatientDependent;
use App\Models\OtherPatientRecord as OPR;
use App\Models\PatientAccount;
use App\Models\PatientWallet;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Reminder;
use App\Models\Referral;
use App\Models\Prescription;
use App\Models\Review;
use App\Models\Coupon;
use App\Models\PatientProfile as Profile;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\User\Http\Controllers\UserController;

class PatientController extends Controller
{

    public function updateProfile(Request $request){

        $patient = Auth::user();

       //find patient
        $findPatient = Patient::where('user_id', $patient->id)->first();

        if(!$findPatient){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        //update patient record
        try{
            $findPatient->email = $request->email ? $request->email : $findPatient->email;
            $findPatient->phone = $request->phone ? $request->phone : $findPatient->phone;
            $findPatient->name = $request->name ? $request->name : $findPatient->name;
            $findPatient->dob = $request->dob ? $request->dob : $findPatient->dob;
            $findPatient->blood_group = $request->blood_group ? $request->blood_group : $findPatient->blood_group;
            $findPatient->address = $request->address ? $request->address : $findPatient->address;
            $findPatient->city = $request->city ? $request->city : $findPatient->city;
            $findPatient->state = $request->state ? $request->state : $findPatient->state;
            $findPatient->country = $request->country ? $request->country : $findPatient->country;
            $findPatient->zip_code = $request->zip_code ? $request->zip_code : $findPatient->zip_code;
            $findPatient->profile_picture = $request->profile_picture ? $request->profile_picture : $findPatient->profile_picture;
            $findPatient->save();

            $patient->email = $request->email ? $request->email : $patient->email;
            $patient->phone = $request->phone ? $request->phone : $patient->phone;
            $patient->name = $request->name ? $request->name : $patient->name;
            $patient->dob = $request->dob ? $request->dob : $patient->dob;
            $patient->profile_image = $request->profile_picture ? $request->profile_picture : $patient->profile_image;
            $patient->save();

            }
        catch(ClientException $exception){
            $error['status'] = false;
            $error['message'] = "Unable to update profile";
            return response()->json($error, 422);
        }

        $success['status'] = true;
        $success['message'] = "Profile Updated Successfully";
        $success['data'] = $findPatient;
        return response()->json($success, 200);
    }

    public function userProfile(Request $request){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }
        
        $success['status'] = true;
        $success['message'] = "Patient record fetched successfully";
        $success['data'] = $allPatients;

        return response()->json($success, 200);
       
    }

    public function dashboard(Request $request){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $getAppointments = [];
        $getPrescriptions = Prescription::where('patient_id', $allPatients->user_id)->get();
        $getMedicalRecords = MedicalRecord::where('user_id', $allPatients->user_id)->get();
        $getBillings = [];

        $presArr = array();
        foreach($getPrescriptions as $prescription){
            $getDoctor = Doctor::where('user_id', $prescription->doctor_id)->first();
            $pres['id'] = $prescription->id;
            $pres['doctor_name'] = "Dr. " . $getDoctor->legalname;
            $pres['doctor_id'] = $prescription->doctor_id;
            $pres['patient_id'] = $prescription->patient_id;
            $pres['name'] = $prescription->name;
            $pres['quantity'] = $prescription->quantity;
            $pres['days'] = $prescription->days;
            $pres['time'] = json_decode($prescription->time);
            $pres['signature'] = $prescription->signature;
            $pres['created_at'] = $prescription->created_at;

            array_push($presArr, $pres);
        }

        $medArr = array();
        foreach($getMedicalRecords as $medicalRecord){
                if($medicalRecord->ordered_by == "Doctor"){
                    $getDoctor = Doctor::where('user_id', $medicalRecord->doctor_id)->first();
                    $med['id'] = $medicalRecord->id;
                    $med['doctor_name'] = "Dr. " . $getDoctor->legalname;
                    $med['doctor_id'] = $medicalRecord->doctor_id;
                    $med['patient_id'] = $medicalRecord->user_id;
                    $med['name'] = $medicalRecord->name;
                    $med['date'] = $medicalRecord->date;
                    $med['description'] = $medicalRecord->description;
                    $med['attachment'] = $medicalRecord->attachment;
                    $med['ordered_by'] = $medicalRecord->ordered_by;
                    $med['created_at'] = $medicalRecord->created_at;

                    array_push($medArr, $med);
                }
                else{
                    $med['id'] = $medicalRecord->id;
                    $med['doctor_name'] = "";
                    $med['doctor_id'] = "";
                    $med['patient_id'] = $medicalRecord->user_id;
                    $med['name'] = $medicalRecord->name;
                    $med['date'] = $medicalRecord->date;
                    $med['description'] = $medicalRecord->description;
                    $med['attachment'] = $medicalRecord->attachment;
                    $med['ordered_by'] = $medicalRecord->ordered_by;
                    $med['created_at'] = $medicalRecord->created_at;

                    array_push($medArr, $med);
                }
        }

        
        $success['status'] = true;
        $success['message'] = "Patient record fetched successfully";
        $success['data'] = [
            'patient' => $allPatients,
            'appointments' => $getAppointments,
            'prescriptions' => $presArr,
            'medical_records' => $medArr,
            'billings' => $getBillings,
            'other_records' => []
        ];

        return response()->json($success, 200);
       
    }

    public function updatePassword(Request $request){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $error['status'] = false;
            $error['message'] = "Validation Error";
            $error['data'] = $validator->errors();
            return response(['error' => $error], 422);
        }

        if (!Hash::check($request->old_password, $patient->password)) {
            $error['status'] = false;
            $error['message'] = "Old password does not match";
            return response(['error' => $error], 400);
        }

        $patient->password = Hash::make($request->new_password);
        $patient->save();

        $success['status'] = true;
        $success['message'] = "Password updated successfully";
        return response()->json($success, 200);
    }

    public function addMedicalRecord(Request $request){
            
            $patient = Auth::user();
    
            $allPatients = Patient::where('user_id', $patient->id)->first();
    
            if(!$allPatients){
                $error['status'] = false;
                $error['message'] = "Patient record not found!";
                return response()->json($error, 404);
            }
    
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'date' => 'required|string',
                'description' => 'required|string',
                'attachment' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                $error['status'] = false;
                $error['message'] = "Validation Error";
                $error['data'] = $validator->errors();
                return response(['error' => $error], 422);
            }
    
            try{
                $medicalRecord = new MedicalRecord();
                $medicalRecord->user_id = $allPatients->user_id;
                $medicalRecord->name = $request->name;
                $medicalRecord->date = $request->date;
                $medicalRecord->description = $request->description;
                $medicalRecord->attachment = $request->attachment;
                $medicalRecord->ordered_by = "patient";
                $medicalRecord->save();
            }
            catch(\Exception $e){
                $error['status'] = false;
                $error['message'] = "Unable to add medical record";
                return response()->json($error, 422);
            }
    
            $success['status'] = true;
            $success['message'] = "Medical record added successfully";
            $success['data'] = $medicalRecord;
            return response()->json($success, 200);
    }

    public function addMedicalRecords(Request $request){
            
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'date' => 'required|string',
            'description' => 'required|string',
            'attachment' => 'required|string',
            'doctor_id' => 'required',
        ]);

        if ($validator->fails()) {
            $error['status'] = false;
            $error['message'] = "Validation Error";
            $error['data'] = $validator->errors();
            return response(['error' => $error], 422);
        }

        $findDoctor = Doctor::where('user_id', $request->doctor_id)->first();

        if(!$findDoctor){
            $error['status'] = false;
            $error['message'] = "Doctor record not found!";
            return response()->json($error, 404);
        }

        try{
            $medicalRecord = new MedicalRecord();
            $medicalRecord->user_id = $allPatients->user_id;
            $medicalRecord->name = $request->name;
            $medicalRecord->date = $request->date;
            $medicalRecord->description = $request->description;
            $medicalRecord->attachment = $request->attachment;
            $medicalRecord->ordered_by = "Doctor";
            $medicalRecord->doctor_id = $request->doctor_id;
            $medicalRecord->save();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = "Unable to add medical record";
            return response()->json($error, 422);
        }

        $success['status'] = true;
        $success['message'] = "Medical record added successfully";
        $success['data'] = $medicalRecord;
        return response()->json($success, 200);
}

    public function getAllMedicalRecords(Request $request){
                
                $patient = Auth::user();
        
                $allPatients = Patient::where('user_id', $patient->id)->first();
        
                if(!$allPatients){
                    $error['status'] = false;
                    $error['message'] = "Patient record not found!";
                    return response()->json($error, 404);
                }
        
                $allMedicalRecords = MedicalRecord::where('user_id', $allPatients->user_id)->get();

                if(count($allMedicalRecords) == 0){
                    $success['status'] = true;
                    $success['message'] = "No medical record found!";
                    $success['data'] = $allMedicalRecords;
                    return response()->json($success, 200);
                }

                $arr = array();
                foreach($allMedicalRecords as $medicalRecord){
                    if($medicalRecord->ordered_by == "Doctor"){
                        $doctor = Doctor::where('user_id', $medicalRecord->doctor_id)->first();
                        
                        if(!$doctor){
                            continue;
                        }

                        $details['id'] = $medicalRecord->id;
                        $details['patient_id'] = $medicalRecord->user_id;
                        $details['name'] = $medicalRecord->name;
                        $details['date'] = $medicalRecord->date;
                        $details['description'] = $medicalRecord->description;
                        $details['attachment'] = $medicalRecord->attachment;
                        $details['doctor_name'] = "Dr. " . $doctor->legalname;
                        $details['specialization'] = $doctor->specialization;
                        $details['ordered_by'] = $medicalRecord->ordered_by;

                        array_push($arr, $details);
                    }
                    else{
                        $details['id'] = $medicalRecord->id;
                        $details['patient_id'] = $medicalRecord->user_id;
                        $details['name'] = $medicalRecord->name;
                        $details['date'] = $medicalRecord->date;
                        $details['description'] = $medicalRecord->description;
                        $details['attachment'] = $medicalRecord->attachment;
                        $details['ordered_by'] = $medicalRecord->ordered_by;
                        $details['doctor_name'] = "";
                        $details['specialization'] = "";
                        
                        array_push($arr, $details);
                    }
                }
        
        
                $success['status'] = true;
                $success['message'] = "Medical records fetched successfully";
                $success['data'] = $arr;
                return response()->json($success, 200);
    }

    public function getSingleMedicalRecord(Request $request, $id){
                
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $allMedicalRecords = MedicalRecord::where('user_id', $allPatients->user_id)
        ->where('id', $id)
        ->first();

        if(!$allMedicalRecords){
            $error['status'] = false;
            $error['message'] = "Medical record not found!";
            return response()->json($error, 404);
        }

        if($allMedicalRecords->ordered_by == "Doctor"){
            $doctor = Doctor::where('user_id', $allMedicalRecords->doctor_id)->first();
            
            if(!$doctor){
                $error['status'] = false;
                $error['message'] = "Doctor record not found!";
                return response()->json($error, 404);
            }

            $details['id'] = $allMedicalRecords->id;
            $details['patient_id'] = $allMedicalRecords->user_id;
            $details['name'] = $allMedicalRecords->name;
            $details['date'] = $allMedicalRecords->date;
            $details['description'] = $allMedicalRecords->description;
            $details['attachment'] = $allMedicalRecords->attachment;
            $details['doctor_name'] = "Dr. " . $doctor->legalname;
            $details['specialization'] = $doctor->specialization;
            $details['ordered_by'] = $allMedicalRecords->ordered_by;

        }
        else{
            $details['id'] = $allMedicalRecords->id;
            $details['patient_id'] = $allMedicalRecords->user_id;
            $details['name'] = $allMedicalRecords->name;
            $details['date'] = $allMedicalRecords->date;
            $details['description'] = $allMedicalRecords->description;
            $details['attachment'] = $allMedicalRecords->attachment;
            $details['ordered_by'] = $allMedicalRecords->ordered_by;
            $details['doctor_name'] = "";
            $details['specialization'] = "";
        }

        $success['status'] = true;
        $success['message'] = "Medical records fetched successfully";
        $success['data'] = $details;
        return response()->json($success, 200);
    }

    public function deleteSingleMedicalRecord(Request $request, $id){
                    
            $patient = Auth::user();
    
            $allPatients = Patient::where('user_id', $patient->id)->first();
    
            if(!$allPatients){
                $error['status'] = false;
                $error['message'] = "Patient record not found!";
                return response()->json($error, 404);
            }
    
            $allMedicalRecords = MedicalRecord::where('user_id', $allPatients->user_id)
            ->where('id', $id)
            ->first();
    
            if(!$allMedicalRecords){
                $error['status'] = false;
                $error['message'] = "Medical record not found!";
                return response()->json($error, 404);
            }
    
            $allMedicalRecords->delete();
    
            $success['status'] = true;
            $success['message'] = "Medical record deleted successfully";
            return response()->json($success, 200);
    }

    public function allDependents(Request $request){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $allDependents = PatientDependent::where('user_id', $allPatients->user_id)->get();

        $success['status'] = true;
        $success['message'] = "Dependents fetched successfully";
        $success['data'] = $allDependents;
        return response()->json($success, 200);
    }

    public function singleDependent(Request $request, $id){
            
            $patient = Auth::user();
    
            $allPatients = Patient::where('user_id', $patient->id)->first();
    
            if(!$allPatients){
                $error['status'] = false;
                $error['message'] = "Patient record not found!";
                return response()->json($error, 404);
            }
    
            $allDependents = PatientDependent::where('user_id', $allPatients->user_id)
            ->where('id', $id)
            ->first();
    
            if(!$allDependents){
                $error['status'] = false;
                $error['message'] = "Dependent not found!";
                return response()->json($error, 404);
            }
    
            $success['status'] = true;
            $success['message'] = "Dependent fetched successfully";
            $success['data'] = $allDependents;
            return response()->json($success, 200);

    }

    public function deleteSingleDependent(Request $request, $id){
                        
                $patient = Auth::user();
        
                $allPatients = Patient::where('user_id', $patient->id)->first();
        
                if(!$allPatients){
                    $error['status'] = false;
                    $error['message'] = "Patient record not found!";
                    return response()->json($error, 404);
                }
        
                $allDependents = PatientDependent::where('user_id', $allPatients->user_id)
                ->where('id', $id)
                ->first();
        
                if(!$allDependents){
                    $error['status'] = false;
                    $error['message'] = "Dependent not found!";
                    return response()->json($error, 404);
                }
        
                $allDependents->delete();
        
                $success['status'] = true;
                $success['message'] = "Dependent deleted successfully";
                return response()->json($success, 200);

    }

    public function addDependent(Request $request){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'number' => 'string|max:255',
            'bloodgroup' => 'required|string|max:255',
            'picture' => 'required|string|max:255'
        ]);
    
            if ($validator->fails()) {
                $error['status'] = false;
                $error['message'] = "Validation Error";
                $error['data'] = $validator->errors();
                return response(['error' => $error], 422);
            }

        try{
            $allDependents = new PatientDependent();
            $allDependents->user_id = $allPatients->user_id;
            $allDependents->name = $request->name;
            $allDependents->relationship = $request->relationship;
            $allDependents->gender = $request->gender;
            $allDependents->number = $request->number;
            $allDependents->bloodgroup = $request->bloodgroup;
            $allDependents->picture = $request->picture;
            $allDependents->save();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = "Error adding dependent";
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Dependent added successfully";
        return response()->json($success, 200);


    }

    public function updateDependent(Request $request, $id){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $allDependents = PatientDependent::where('user_id', $allPatients->user_id)
        ->where('id', $id)
        ->first();

        if(!$allDependents){
            $error['status'] = false;
            $error['message'] = "Dependent not found!";
            return response()->json($error, 404);
        } 


        try{
            $allDependents->name = $request->name ? $request->name : $allDependents->name;
            $allDependents->relationship = $request->relationship ? $request->relationship : $allDependents->relationship;
            $allDependents->gender = $request->gender ? $request->gender : $allDependents->gender;
            $allDependents->number = $request->number ? $request->number : $allDependents->number;
            $allDependents->bloodgroup = $request->bloodgroup ? $request->bloodgroup : $allDependents->bloodgroup;
            $allDependents->picture = $request->picture ? $request->picture : $allDependents->picture;
            $allDependents->save();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = "Error adding dependent";
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Dependent info updated successfully";
        return response()->json($success, 200);


    }

    public function addOtherMedicalRecord(Request $request){
        
            $patient = Auth::user();
    
            $allPatients = Patient::where('user_id', $patient->id)->first();
    
            if(!$allPatients){
                $error['status'] = false;
                $error['message'] = "Patient record not found!";
                return response()->json($error, 404);
            }
    
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'bmi' => 'required|string',
                'heart_rate' => 'required|string',
                'fbc_status' => 'required|string',
                'weight' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                $error['status'] = false;
                $error['message'] = "Validation Error";
                $error['data'] = $validator->errors();
                return response(['error' => $error], 422);
            }

            try{
                $medicalRecord = new OPR();
                $medicalRecord->user_id = $allPatients->user_id;
                $medicalRecord->name = $request->name;
                $medicalRecord->bmi = $request->bmi;
                $medicalRecord->heart_rate = $request->heart_rate;
                $medicalRecord->fbc_status = $request->fbc_status;
                $medicalRecord->weight = $request->weight;
                $medicalRecord->order_date = now();
                $medicalRecord->save();
            }
            catch(\Exception $e){
                $error['status'] = false;
                $error['message'] = "Error adding other medical record";
                return response()->json($error, 500);
            }

            $success['status'] = true;
            $success['message'] = "Medical record added successfully";
            return response()->json($success, 200);
    }

    public function updateOtherMedicalRecord(Request $request, $id){
            
            $patient = Auth::user();
    
            $allPatients = Patient::where('user_id', $patient->id)->first();
    
            if(!$allPatients){
                $error['status'] = false;
                $error['message'] = "Patient record not found!";
                return response()->json($error, 404);
            }
    
            $medicalRecord = OPR::where('user_id', $allPatients->user_id)
            ->where('id', $id)
            ->first();
    
            if(!$medicalRecord){
                $error['status'] = false;
                $error['message'] = "Medical record not found!";
                return response()->json($error, 404);
            }

            try{
                $medicalRecord->name = $request->name ? $request->name : $medicalRecord->name;
                $medicalRecord->bmi = $request->bmi ? $request->bmi : $medicalRecord->bmi;
                $medicalRecord->heart_rate = $request->heart_rate ? $request->heart_rate : $medicalRecord->heart_rate;
                $medicalRecord->fbc_status = $request->fbc_status ? $request->fbc_status : $medicalRecord->fbc_status;
                $medicalRecord->weight = $request->weight ? $request->weight : $medicalRecord->weight;
                $medicalRecord->save();
            }
            catch(\Exception $e){
                $error['status'] = false;
                $error['message'] = "Error updating medical record";
                return response()->json($error, 500);
            }

            $success['status'] = true;
            $success['message'] = "Medical record updated successfully";
            return response()->json($success, 200);
    }

    public function getSingleOtherMedicalRecord(Request $request, $id){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $medicalRecord = OPR::where('user_id', $allPatients->user_id)
        ->where('id', $id)
        ->first();

        if(!$medicalRecord){
            $error['status'] = false;
            $error['message'] = "Medical record not found!";
            return response()->json($error, 404);
        }

        $success['status'] = true;
        $success['message'] = "Medical record retrieved successfully";
        $success['data'] = $medicalRecord;
        return response()->json($success, 200);
    }

    public function allOtherMedicalRecords(Request $request){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $medicalRecord = OPR::where('user_id', $allPatients->user_id)
        ->get();


        $success['status'] = true;
        $success['message'] = "Medical record retrieved successfully";
        $success['data'] = $medicalRecord;
        return response()->json($success, 200);
    }

    public function deleteOtherMedicalRecords(Request $request, $id){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $medicalRecord = OPR::where('user_id', $allPatients->user_id)
        ->where('id', $id)
        ->first();

        if(!$medicalRecord){
            $error['status'] = false;
            $error['message'] = "Medical record not found!";
            return response()->json($error, 404);
        }

        try{
            $medicalRecord->delete();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = "Error deleting medical record";
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Medical record deleted successfully";
        return response()->json($success, 200);
    }

    public function setAccountDetails(Request $request){
            
            $patient = Auth::user();
            // $base_url = "https://patientapi.gettheskydoctors.com";
            // $url = $request->url;
    
            // if($url != $base_url){
            //     $error['status'] = false;
            //     $error['message'] = "Unauthorized Access";
            //     return response()->json($error, 401);
            // }
    
            $allPatients = Patient::where('user_id', $patient->id)->first();
    
            if(!$allPatients){
                $error['status'] = false;
                $error['message'] = "Patient record not found!";
                return response()->json($error, 404);
            }
    
            $validator = Validator::make($request->all(), [
                'account_name' => 'required',
                'account_number' => 'required',
                'bank_name' => 'required',
                'branch_name' => 'required',
                'type' => 'required',
            ]);
    
            if($validator->fails()){
                $error['status'] = false;
                $error['message'] = "Validation error";
                $error['data'] = $validator->errors();
                return response()->json($error, 400);
            }
            
            $patient_account = PatientAccount::where('user_id', $allPatients->user_id)->first();

            if($patient_account){
                $error['status'] = false;
                $error['message'] = "Account details already set";
                return response()->json($error, 400);
            }

            try{
                $patient_account = new PatientAccount();
                $patient_account->user_id = $allPatients->user_id;
                $patient_account->account_name = $request->account_name;
                $patient_account->account_number = $request->account_number;
                $patient_account->bank_name = $request->bank_name;
                $patient_account->branch_name = $request->branch_name;
                $patient_account->type = $request->type;
                $patient_account->save();
            }
            catch(\Exception $e){
                $error['status'] = false;
                $error['message'] = "Error updating account details";
                return response()->json($error, 500);
            }
    
            $success['status'] = true;
            $success['message'] = "Account details updated successfully";
            return response()->json($success, 200);
    }

    public function editAccountDetails(Request $request){
                
                $patient = Auth::user();
        
                $allPatients = Patient::where('user_id', $patient->id)->first();
        
                if(!$allPatients){
                    $error['status'] = false;
                    $error['message'] = "Patient record not found!";
                    return response()->json($error, 404);
                }
        
                $patient_account = PatientAccount::where('user_id', $allPatients->user_id)->first();
                if(!$patient_account){
                    $error['status'] = false;
                    $error['message'] = "Account details not found | Try adding account!";
                    return response()->json($error, 404);
                }
                try{
                    $patient_account->account_name = $request->account_name ?? $patient_account->account_name;
                    $patient_account->account_number = $request->account_number ?? $patient_account->account_number;
                    $patient_account->bank_name = $request->bank_name ?? $patient_account->bank_name;
                    $patient_account->branch_name = $request->branch_name ?? $patient_account->branch_name;
                    $patient_account->type = $request->type ?? $patient_account->type;
                    $patient_account->save();
                }
                catch(\Exception $e){
                    $error['status'] = false;
                    $error['message'] = "Error updating account details";
                    return response()->json($error, 500);
                }
        
                $success['status'] = true;
                $success['message'] = "Account details updated successfully";
                return response()->json($success, 200);
    }

    public function getAccountDetails(Request $request){
                
                $patient = Auth::user();
                // $base_url = "https://patientapi.gettheskydoctors.com";
                // $url = $request->url;
        
                // if($url != $base_url){
                //     $error['status'] = false;
                //     $error['message'] = "Unauthorized Access";
                //     return response()->json($error, 401);
                // }
        
                $allPatients = Patient::where('user_id', $patient->id)->first();
        
                if(!$allPatients){
                    $error['status'] = false;
                    $error['message'] = "Patient record not found!";
                    return response()->json($error, 404);
                }
        
                $patient_account = PatientAccount::where('user_id', $allPatients->user_id)->first();
                if(!$patient_account){
                    $error['status'] = false;
                    $error['message'] = "Account details not found | Try adding account!";
                    return response()->json($error, 404);
                }
        
                $success['status'] = true;
                $success['message'] = "Account details retrieved successfully";
                $success['data'] = $patient_account;
                return response()->json($success, 200);
    }

    public function createAppointment(Request $request){

        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'appointment_date' => 'required',
            'appointment_time' => 'required',
            'appointment_type' => 'required',
            'appointment_reason' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = "Validation error";
            $error['data'] = $validator->errors();
            return response()->json($error, 400);
        }

        $doctor = Doctor::where('id', $request->doctor_id)->first();

        if(!$doctor){
            $error['status'] = false;
            $error['message'] = "Doctor record not found!";
            return response()->json($error, 404);
        }

        $appointment = Appointment::where('patient_id', $allPatients->id)->where('doctor_id', $request->doctor_id)->where('appointment_date', $request->appointment_date)->where('appointment_time', $request->appointment_time)->first();

        if($appointment){
            $error['status'] = false;
            $error['message'] = "Appointment already exists!";
            return response()->json($error, 400);
        }

        try{
            $appointment = new Appointment();
            $appointment->patient_id = $allPatients->id;
            $appointment->doctor_id = $request->doctor_id;
            $appointment->appointment_date = $request->appointment_date;
            $appointment->appointment_time = $request->appointment_time;
            $appointment->appointment_type = $request->appointment_type;
            $appointment->appointment_reason = $request->appointment_reason;
            $appointment->save();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = "Error creating appointment";
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Appointment created successfully";
        return response()->json($success, 200);

    }

    public function createReminder(Request $request){
            
            $patient = Auth::user();
    // dd($patient);
            $allPatients = Patient::where('user_id', $patient->id)->first();
    
            if(!$allPatients){
                $error['status'] = false;
                $error['message'] = "Patient record not found!";
                return response()->json($error, 404);
            }
    
            $validator = Validator::make($request->all(), [
                'pill_name' => 'required',
                'reminder_dates' => 'required',
                'frequency' => 'required',
                'no_of_times' => 'required',
            ]);
    
            if($validator->fails()){
                $error['status'] = false;
                $error['message'] = "Validation error";
                $error['data'] = $validator->errors();
                return response()->json($error, 400);
            }
            // dd($allPatients->user_id);
            try{
                $reminder = new Reminder();
                $reminder->user_id = $allPatients->user_id;
                $reminder->pill_name = $request->pill_name;
                $reminder->days = json_encode($request->reminder_dates);
                $reminder->frequency = $request->frequency;
                $reminder->times = $request->no_of_times;
                $reminder->save();
                
            }
            catch(\Exception $e){
                $error['status'] = false;
                $error['message'] = "Error creating reminder";
                return response()->json($error, 500);
            }
    
            $success['status'] = true;
            $success['message'] = "Reminder created successfully";
            return response()->json($success, 200);
    
    }

    public function getReminders(Request $request){
            
            $patient = Auth::user();

            $allPatients = Patient::where('user_id', $patient->id)->first();

            if(!$allPatients){
                $error['status'] = false;
                $error['message'] = "Patient record not found!";
                return response()->json($error, 404);
            }

            $reminders = Reminder::where('user_id', $allPatients->user_id)->get();
            
            if(count($reminders) == 0){
                $success['status'] = 'success';
                $success['message'] = "No reminders found!";
                $success['data'] = $reminders;
                return response()->json($success, 200);
            }

            $arr = array();
            foreach($reminders as $reminder){
                $rem['id'] = $reminder->id;
                $rem['patient_id'] = $reminder->user_id;
                $rem['pill_name'] = $reminder->pill_name;
                $rem['reminder_dates'] = json_decode($reminder->days);
                $rem['frequency'] = $reminder->frequency;
                $rem['no_of_times'] = $reminder->times;
                $rem['created_at'] = $reminder->created_at;
                array_push($arr, $rem);
            }
    
            $success['status'] = true;
            $success['message'] = "Reminders retrieved successfully";
            $success['data'] = $arr;
            return response()->json($success, 200);
    
    }

    public function singleReminder(Request $request, $id){
                
                $patient = Auth::user();
        
                $allPatients = Patient::where('user_id', $patient->id)->first();
        
                if(!$allPatients){
                    $error['status'] = false;
                    $error['message'] = "Patient record not found!";
                    return response()->json($error, 404);
                }
        
                $reminder = Reminder::where('user_id', $allPatients->user_id)->where('id', $id)->first();
        
                if(!$reminder){
                    $error['status'] = false;
                    $error['message'] = "Reminder not found!";
                    return response()->json($error, 404);
                }
        
                $rem['id'] = $reminder->id;
                $rem['pill_name'] = $reminder->pill_name;
                $rem['reminder_dates'] = json_decode($reminder->reminder_dates);
                $rem['frequency'] = $reminder->frequency;
                $rem['no_of_times'] = $reminder->no_of_times;
                $rem['created_at'] = $reminder->created_at;
        
                $success['status'] = true;
                $success['message'] = "Reminder retrieved successfully";
                $success['data'] = $rem;
                return response()->json($success, 200);
        
    }

    public function deleteReminder(Request $request, $id){
                
                $patient = Auth::user();
        
                $allPatients = Patient::where('user_id', $patient->id)->first();
        
                if(!$allPatients){
                    $error['status'] = false;
                    $error['message'] = "Patient record not found!";
                    return response()->json($error, 404);
                }
        
                $reminder = Reminder::where('user_id', $allPatients->user_id)->where('id', $id)->first();
        
                if(!$reminder){
                    $error['status'] = false;
                    $error['message'] = "Reminder not found!";
                    return response()->json($error, 404);
                }
        
                $reminder->delete();
        
                $success['status'] = true;
                $success['message'] = "Reminder deleted successfully";
                return response()->json($success, 200);
        
    }

    public function getReferralCode(){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        if($allPatients->referral_code == null){
            $referral_code = str_rand(6);
            $allPatients->referral_code = $referral_code;
            $allPatients->save();
        }

        $referral_code = $allPatients->referral_code;

        $success['status'] = true;
        $success['message'] = "Referral code retrieved successfully";
        $success['data'] = [
            'referral_code' => $referral_code,
            'facebook_link' => 'https://www.facebook.com/?referral-code='.$referral_code,
            'twitter_link' => 'https://twitter.com/intent/?referral-code='.$referral_code,
            'whatsapp_link' => 'https://api.whatsapp.com/?referral-code='.$referral_code,
        ];
        return response()->json($success, 200);
    }

    public function getAllReferrals(){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        if($allPatients->referral_code == null){
            $referral_code = str_rand(6);
            $allPatients->referral_code = $referral_code;
            $allPatients->save();
        }

        $referrals = Referral::where('referral_code', $allPatients->referral_code)->get();

        if(count($referrals) == 0){
            $success['status'] = true;
            $success['message'] = "No referrals found!";
            $success['data'] = $referrals;
            return response()->json($success, 200);
        }

        $arr = array();
        $countReferrals = 0;
        foreach($referrals as $referral){
            $ref['id'] = $referral->id;
            $ref['referral_code'] = $referral->referral_code;
            $ref['referred_email'] = $referral->referred_email;
            $ref['created_at'] = $referral->created_at;
            array_push($arr, $ref);

            $countReferrals++;
        }

        $success['status'] = true;
        $success['message'] = "Referrals retrieved successfully";
        $success['data'] = [
            'referrals' => $arr,
            'total Referrals' => $countReferrals
        ];
        return response()->json($success, 200);
    }

    public function createPrescription(Request $request){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'name' => 'required',
            'quantity' => 'required',
            'days' => 'required',
            'time' => 'required',
            'signature' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        $findDoctor = Doctor::where('user_id', $request->doctor_id)->first();

        if(!$findDoctor){
            $error['status'] = false;
            $error['message'] = "Doctor not found!";
            return response()->json($error, 404);
        }

        try{
            $prescription = new Prescription;
            $prescription->doctor_id = $findDoctor->user_id;
            $prescription->patient_id = $allPatients->user_id;
            $prescription->name = $request->name;
            $prescription->quantity = $request->quantity;
            $prescription->days = $request->days;
            $prescription->time = json_encode($request->time);
            $prescription->signature = $request->signature;
            $prescription->save();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error creating prescription!';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Prescription created successfully";

        return response()->json($success, 200);
    }

    public function getAllPrescriptions(){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $prescriptions = Prescription::where('patient_id', $allPatients->user_id)->get();

        if(count($prescriptions) == 0){
            $success['status'] = true;
            $success['message'] = "No prescriptions found!";
            $success['data'] = $prescriptions;
            return response()->json($success, 200);
        }

        $arr = array();
        foreach($prescriptions as $prescription){
            $doctors = Doctor::where('user_id', $prescription->doctor_id)->first();

            $pres['id'] = $prescription->id;
            $pres['doctor_name'] = "Dr. " . $doctors->legalname;
            $pres['specialization'] = $doctors->specialization;
            $pres['patient_id'] = $prescription->patient_id;
            $pres['name'] = $prescription->name;
            $pres['quantity'] = $prescription->quantity;
            $pres['days'] = $prescription->days;
            $pres['time'] = json_decode($prescription->time);
            $pres['signature'] = $prescription->signature;
            $pres['created_at'] = $prescription->created_at;
            array_push($arr, $pres);

        }

        $success['status'] = true;
        $success['message'] = "Prescriptions retrieved successfully";
        $success['data'] = $arr;

        return response()->json($success, 200);
    }

    public function singlePrescription($id){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $prescription = Prescription::where('patient_id', $allPatients->user_id)->where('id', $id)->first();

        if(!$prescription){
            $error['status'] = false;
            $error['message'] = "Prescription not found!";
            return response()->json($error, 404);
        }

        $doctors = Doctor::where('user_id', $prescription->doctor_id)->first();


        $pres['id'] = $prescription->id;
        $pres['doctor_name'] = "Dr. " . $doctors->legalname;
        $pres['specialization'] = $doctors->specialization;
        $pres['patient_id'] = $prescription->patient_id;
        $pres['name'] = $prescription->name;
        $pres['quantity'] = $prescription->quantity;
        $pres['days'] = $prescription->days;
        $pres['time'] = json_decode($prescription->time);
        $pres['signature'] = $prescription->signature;
        $pres['created_at'] = $prescription->created_at;

        $success['status'] = true;
        $success['message'] = "Prescription retrieved successfully";
        $success['data'] = $pres;

        return response()->json($success, 200);
    }

    public function createReview(Request $request){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'rating' => 'required',
            'review' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        $findDoctor = Doctor::where('user_id', $request->doctor_id)->first();

        if(!$findDoctor){
            $error['status'] = false;
            $error['message'] = "Doctor not found!";
            return response()->json($error, 404);
        }

        try{
            $review = new Review;
            $review->doctor_id = $findDoctor->user_id;
            $review->patient_id = $allPatients->user_id;
            $review->rating = $request->rating;
            $review->review = $request->review;
            $review->save();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error creating review!';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Review sent successfully";

        return response()->json($success, 200);
    }

    public function allReviews(){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $reviews = Review::where('patient_id', $allPatients->user_id)->get();

        if(count($reviews) == 0){
            $success['status'] = true;
            $success['message'] = "No reviews found!";
            $success['data'] = $reviews;
            return response()->json($success, 200);
        }

        $arr = array();
        foreach($reviews as $review){
            $doctors = Doctor::where('user_id', $review->doctor_id)->first();

            $rev['id'] = $review->id;
            $rev['doctor_name'] = "Dr. " . $doctors->legalname;
            $rev['specialization'] = $doctors->specialization;
            $rev['patient_name'] = $allPatients->name;
            $rev['patient_picture'] = $allPatients->profile_picture ?? "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y";
            $rev['rating'] = $review->rating;
            $rev['review'] = $review->review;
            $rev['created_at'] = $review->created_at;
            array_push($arr, $rev);

        }

        $success['status'] = true;
        $success['message'] = "Reviews retrieved successfully";
        $success['data'] = $arr;

        return response()->json($success, 200);
    }

    public function createCoupon(Request $request){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required',
            'description' => 'required',
            "discount_type" => "required",
            "discount_value" => "required"
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        $findCoupon = Coupon::where('coupon_code', $request->coupon_code)->first();

        if($findCoupon){
            $error['status'] = false;
            $error['message'] = "Coupon already exists!";
            return response()->json($error, 400);
        }

        try{
            if($request->key == "qwerty"){

                if($request->discount_type == "percentage"){
                    if($request->discount_value > 100 || $request->discount_value < 0){
                        $error['status'] = false;
                        $error['message'] = "Discount value must be between 0 and 100";
                        return response()->json($error, 400);
                    }
                }
                else if($request->discount_type == "fixed"){
                    if($request->discount_value < 0){
                        $error['status'] = false;
                        $error['message'] = "Discount value must be greater than 0";
                        return response()->json($error, 400);
                    }
                }
                else{
                    $error['status'] = false;
                    $error['message'] = "Discount type must be either percentage or fixed";
                    return response()->json($error, 400);
                }
                $coupon = new Coupon;
                $coupon->coupon_code = $request->coupon_code;
                $coupon->description = $request->description;
                $coupon->discount_type = $request->discount_type;
                $coupon->discount_value = $request->discount_value;
                $coupon->status = $request->status ?? 0;
                $coupon->save();
            }
            else{
                $error['status'] = false;
                $error['message'] = "You're not allowed to perform this operation!";
                return response()->json($error, 400);
            }
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error creating coupon!';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Coupon created successfully";

        return response()->json($success, 200);
    }

    public function allCoupons(){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $coupons = Coupon::where('status', 1)->get();

        if(count($coupons) == 0){
            $success['status'] = true;
            $success['message'] = "No coupons found!";
            $success['data'] = $coupons;
            return response()->json($success, 200);
        }

        $success['status'] = true;
        $success['message'] = "Coupons retrieved successfully";
        $success['data'] = $coupons;

        return response()->json($success, 200);
    }

    public function deleteCoupon($id){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 404);
        }

        $coupon = Coupon::where('id', $id)->first();

        if(!$coupon){
            $error['status'] = false;
            $error['message'] = "Coupon not found!";
            return response()->json($error, 404);
        }

        try{
            $coupon->delete();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error deleting coupon!';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Coupon deleted successfully";

        return response()->json($success, 200);
    }
    
    public function logout(){
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $getToken = $patient->token();
        $getToken->revoke();

        $success['status'] = true;
        $success['message'] = "Logout successful";
        return response()->json($success, 200);
    }

    public function allDoctors(){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $doctors = Doctor::where('onboarding', 1)->get();

        if(count($doctors) == 0){
            $success['status'] = true;
            $success['message'] = "No doctors found!";
            $success['data'] = $doctors;
            return response()->json($success, 200);
        }

        $docArr = array();
        foreach($doctors as $doctor){
            $doc['id'] = $doctor->id;
            $doc['user_id'] = $doctor->user_id;
            $doc['name'] = "Dr. " . $doctor->legalname;
            $doc['email'] = $doctor->primary_email;
            $doc['specialization'] = $doctor->specialization;
            $doc['avatar'] = $doctor->avatar;
            $doc['address'] = $doctor->address;
            $doc['city'] = $doctor->city;
            $doc['state'] = $doctor->state;
            $doc['country'] = $doctor->country;
            $doc['consultation_fee'] = $doctor->consultfee;
            $doc['consult_mode'] = $doctor->consultmode;
            $doc['consult_language'] = $doctor->consultlang;
            $doc['currency'] = $doctor->currency;

            array_push($docArr, $doc);
        }

        $success['status'] = true;
        $success['message'] = "Doctors retrieved successfully";
        $success['data'] = $docArr;
        return response()->json($success, 200);
    }

    public function singleDoctor($id){
        $patient = Auth::user();
        $allPatients = Patient::where('user_id', $patient->id)->first();
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $doctor = Doctor::where('user_id', $id)->first();

        if(!$doctor){
            $error['status'] = false;
            $error['message'] = "Doctor not found!";
            return response()->json($error, 404);
        }

        $doc['id'] = $doctor->id;
        $doc['user_id'] = $doctor->user_id;
        $doc['name'] = "Dr. " . $doctor->legalname;
        $doc['email'] = $doctor->primary_email;
        $doc['specialization'] = $doctor->specialization;
        $doc['avatar'] = $doctor->avatar;
        $doc['address'] = $doctor->address;
        $doc['city'] = $doctor->city;
        $doc['state'] = $doctor->state;
        $doc['country'] = $doctor->country;
        $doc['consultation_fee'] = $doctor->consultfee;
        $doc['consult_mode'] = $doctor->consultmode;
        $doc['consult_language'] = $doctor->consultlang;
        $doc['currency'] = $doctor->currency;
        $doc['about_me'] = $doctor->aboutme;

        $getReviews = Review::where('doctor_id', $doctor->user_id)->get();
        if(count($getReviews) == 0){
            $doc['reviews'] = [];
        }
        else{
            $reviews = array();
            foreach($getReviews as $review){
                $rev['id'] = $review->id;
                $rev['patient_id'] = $review->patient_id;
                $rev['doctor_id'] = $review->doctor_id;
                $rev['rating'] = $review->rating;
                $rev['review'] = $review->review;
                $rev['created_at'] = $review->created_at;

                array_push($reviews, $rev);
            }

            $doc['reviews'] = $reviews;
        }

        $success['status'] = true;
        $success['message'] = "Doctor retrieved successfully";
        $success['data'] = $doc;
        return response()->json($success, 200);
    }

    public function register(Request $request){
            $patient = Auth::user();

            $allPatients = Patient::where('user_id', $patient->id)->first();

            if(!$allPatients){
                $error['status'] = false;
                $error['message'] = "Patient record not found!";
                return response()->json($error, 403);
            }

            $validator = Validator::make($request->all(), [
                'legal_name' => 'required',
                "email" => "required"
            ]);
    
            if($validator->fails()){
                $error['status'] = false;
                $error['message'] = $validator->errors();
                return response()->json($error, 400);
            }

            try{
                $allPatients->name = $request->legal_name;
                $allPatients->email = $request->email;
                $allPatients->save();

                $patient->name = $request->legal_name;
                $patient->email = $request->email;
                $patient->save();
            }
            catch(\Exception $e){
                $error['status'] = false;
                $error['message'] = 'Error updating patient record';
                return response()->json($error, 500);
            }

            $success['status'] = true;
            $success['message'] = "Patient record updated successfully";
            return response()->json($success, 200);     
    }

    public function uploadProfilePicture(Request $request){
        $patient = Auth::user();
        
        $allPatients = Patient::where('user_id', $patient->id)->first();
        
        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }
        
        $validator = Validator::make($request->all(), [
            'avatar' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        try{
            $allPatients->profile_picture = $request->avatar;
            $allPatients->save();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error updating patient record';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Patient record updated successfully";
        return response()->json($success, 200);    
    }

    public function personalDetails(Request $request){
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $validator = Validator::make($request->all(), [
            // 'gender' => 'required',
            // 'marital_status' => 'required',
            // 'dob' => 'required',
            'weight' => 'required',
            'height' => 'required',
            'age' => 'required',
            // 'blood_group' => 'required',
            'heart_rate' => 'required',
            'blood_pressure' => 'required',
            'glucose_level' => 'required',
            // 'allergies' => 'required',
            // 'chronic_diseases' => 'required',
            // 'medications' => 'required',
            // 'surgeries' => 'required',
            // 'injuries' => 'required',
            // 'pregnant' => 'required',
            // 'pre-existing_conditions' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        try{
            $allPatients->gender = $request->gender ?? $allPatients->gender;
            $allPatients->marital_status = $request->marital_status ?? $allPatients->marital_status;
            $allPatients->dob = $request->dob ?? $allPatients->dob;
            $allPatients->age = $request->age;

            Profile::create([
                'user_id' => $patient->id,
                'weight' => $request->weight,
                'height' => $request->height,
                'blood_group' => $request->blood_group ?? $allPatients->blood_group,
                'heart_rate' => $request->heart_rate,
                'blood_pressure' => $request->blood_pressure,
                'glucose_level' => $request->glucose_level,
                'allergies' => $request->allergies,
                'chronic_diseases' => $request->chronic_diseases,
                'medications' => $request->medications,
                'surgeries' => $request->surgeries,
                'injuries' => $request->injuries,
                'pregnant' => $request->pregnant,
                'pre-existing_conditions' => $request->pre_existing_conditions
            ]);
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error updating patient record';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Patient record updated successfully";
        return response()->json($success, 200);

    }

    public function onboardDependents(Request $request){
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $validator = Validator::make($request->all(), [
            'dependents' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        $dependents = $request->dependents;
        try{
            foreach($dependents as $dependent){
                $dependents = new PatientDependent();
                $dependents->user_id = $patient->id;
                $dependents->name = $dependent['name'];
                $dependents->relationship = $dependent['relationship'];
                $dependents->gender = $dependent['gender'];
                $dependents->picture = $dependent['picture'];
                $dependents->age = $dependent['age'];
                $dependents->save();
            }
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error updating patient record';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Patient record updated successfully";
        return response()->json($success, 200);

    }

    public function otherDetails(Request $request){
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $validator = Validator::make($request->all(), [
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'about_me' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        try{
            $allPatients->address = $request->address;
            $allPatients->city = $request->city;
            $allPatients->state = $request->state;
            $allPatients->country = $request->country;
            $allPatients->about_me = $request->about_me;
            $allPatients->onboarded = 1;
            $allPatients->save();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error updating patient record';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Patient record updated successfully";
        $success['onboarded_status'] = $allPatients->onboarded;
        return response()->json($success, 200);


    }

    public function bloodPressure(Request $request){
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $validator = Validator::make($request->all(), [
            'systolic' => 'required',
            'diastolic' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        try{
            $findProfile = Profile::where('user_id', $patient->id)->first();

            if(!$findProfile){
                Profile::create([
                    'user_id' => $patient->id,
                    'blood_pressure' => $request->systolic . '/' . $request->diastolic,
                ]);
            }

            $findProfile->blood_pressure = $request->systolic . '/' . $request->diastolic;
            $findProfile->save();
            
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error updating patient record';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Patient record updated successfully";
        return response()->json($success, 200);

    }

    public function bloodSugar(Request $request){
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $validator = Validator::make($request->all(), [
            'unit' => 'required',
            'fast_blood_sugar' => 'required',
            'blood_sugar_after_meal' => 'required',
            'A1C' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        try{
            $findProfile = Profile::where('user_id', $patient->id)->first();

            if(!$findProfile){
                Profile::create([
                    'user_id' => $patient->id,
                    'blood_sugar' => $request->fast_blood_sugar . '/' . $request->blood_sugar_after_meal . '/' . $request->A1C . '/' . $request->unit,
                ]);
            }

            $findProfile->blood_sugar = $request->fast_blood_sugar . '/' . $request->blood_sugar_after_meal . '/' . $request->A1C . '/' . $request->unit;
            $findProfile->save();
            
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error updating patient record';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Patient record updated successfully";
        return response()->json($success, 200);

    }

    public function cholesterol(Request $request){
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $validator = Validator::make($request->all(), [
            'unit' => 'required',
            'HDL' => 'required',
            'LDL' => 'required',
            'total_cholesterol' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        try{
            $findProfile = Profile::where('user_id', $patient->id)->first();

            if(!$findProfile){
                Profile::create([
                    'user_id' => $patient->id,
                    'cholesterol' => $request->HDL . '/' . $request->LDL . '/' . $request->total_cholesterol . '/' . $request->unit,
                ]);
            }

            $findProfile->cholesterol = $request->HDL . '/' . $request->LDL . '/' . $request->total_cholesterol . '/' . $request->unit;
            $findProfile->save();
            
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error updating patient record';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Patient record updated successfully";
        return response()->json($success, 200);

    }

    public function weight(Request $request){
        $patient = Auth::user();

        $allPatients = Patient::where('user_id', $patient->id)->first();

        if(!$allPatients){
            $error['status'] = false;
            $error['message'] = "Patient record not found!";
            return response()->json($error, 403);
        }

        $validator = Validator::make($request->all(), [
            'weight' => 'required',
        ]);

        if($validator->fails()){
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        try{
            $findProfile = Profile::where('user_id', $patient->id)->first();

            if(!$findProfile){
                Profile::create([
                    'user_id' => $patient->id,
                    'weight' => $request->weight . '/' . "kg"
                ]);
            }

            $findProfile->weight = $request->weight . '/' . "kg";
            $findProfile->save();
            
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error updating patient record';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Patient record updated successfully";
        return response()->json($success, 200);

    }

    public function validatePatient(){
        $patient = Auth::user();
        
        try{
            $createPatient = new Patient();
            $createPatient->user_id = $patient->id;
            $createPatient->email = $patient->email;
            $createPatient->phone = $patient->phone;
            $createPatient->is_verified = 1;
            $createPatient->country = $patient->country;
            $createPatient->save();

            $createProfile = new Profile();
            $createProfile->user_id = $patient->id;
            $createProfile->save();
        }
        catch(\Exception $e){
            $error['status'] = false;
            $error['message'] = 'Error creating patient record';
            return response()->json($error, 500);
        }

        $success['status'] = true;
        $success['message'] = "Patient record created successfully";
        return response()->json($success, 200);
    }

}
