<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User; 
use App\studentdocument;
use Illuminate\Support\Facades\Auth; 
use Validator;

class UserController extends Controller
{

    // Success status code

    // Register Api's 

    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function register(Request $request) 
    { 
        // validation of a request coming from a front end
        $validator = Validator::make($request->all(), [ 
            'firstname' => 'required', 
            'lastname' => 'required|string',
            'parentname' => 'required|string',
            'standard' => 'required|string',
            'course' => 'required|string',
            'mobilenumber' => 'required|integer',
            'password' => 'required', 
            'c_password' => 'required|same:password', 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $input = $request->all(); 
        $input['password'] = bcrypt($input['password']); 
        $user = User::create($input); 
        $success['token'] =  $user->createToken('studentportal')-> accessToken; 
        $success['firstname'] =  $user->firstname;
        $success['message'] = "Regiistration process completed successfully";
        $success['statuscode'] = 501;
        return response()->json(['success'=>$success]); 
    }

    // login Api's
    /** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(){ 
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            $success['message'] = "successfully login to the system";
            $success['statuscode'] = 502;
            $success['user'] = $user;
            return response()->json(['success' => $success]); 
        } 
        else{ 
            return response()->json(['error'=>'Invalid email or password'], 401); 
        } 
    }

    public function editprofile(Request $request)
    {
        $user = Auth::user();
        // dd($user);
        if($user)
        {
            $validator = Validator::make($request->all(), [ 
                'firstname' => 'string', 
                'lastname' => 'string',
                'parentname' => 'string',
                'standard' => 'string',
                'course' => 'string',
                'mobilenumber' => 'integer', 
                'email'=>'string'
            ]);

            if ($validator->fails()) { 
                return response()->json(['error'=>$validator->errors()], 401);            
            }
            
            $input = $request->all(); 
            $user->firstname = $input['firstname'];
            $user->lastname = $input['lastname'];
            $user->parentname = $input['parentname'];
            $user->course = $input['course'];
            $user->standard = $input['standard'];
            $user->mobilenumber = $input['mobilenumber'];
            $user->email = $input['email'];
            $user->save();
            $success['statuscode'] = 503;
            $success['user'] = $user;
            return response()->json(['success'=>$success]);
            
        }
        else
        {
            return response()->json(['error'=>'invalid user'], 401);
        }

    }

    public function loginuserdetails()
    {
        $user = Auth::user();
        if($user)
        {
            $documents = auth()->user()->studentdocument;
            //dd($documents);
            $success['statuscode'] = 504;
            $success['user'] = $user;
            return response()->json(['success' => $success]); 
        }

        else
        {
            return response()->json(['error'=>'invalid user'], 401);
        }
    }

    public function uploaddocuments(Request $request)
    {
        $request->validate([
            'birthcertificate' => 'required|mimes:pdf,png,jpeg|max:2048',
        ]);

                $otherdocuments = NULL;

                if($request->hasFile('otherdocuments'))
                {
                    // Get filename with extension           
                $filenameWithExt = $request->file('otherdocuments')->getClientOriginalName();
                // Get just filename
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);            
                // Get just ext
                $extension = $request->file('otherdocuments')->getClientOriginalExtension();
                //Filename to store
                $fileNameToStore = $filename.'_'.time().'.'.$extension;                       
                // Upload Image
                $path = $request->file('otherdocuments')->storeAs('public/otherdocuments', $fileNameToStore);
                }
             
                //dd($request->otherdocuments);

                // Handle File Upload
                // Get filename with extension           
                $filenameWithExt = $request->file('birthcertificate')->getClientOriginalName();
                // Get just filename
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);            
                // Get just ext
                $extension = $request->file('birthcertificate')->getClientOriginalExtension();
                //Filename to store
                $fileNameToStore = $filename.'_'.time().'.'.$extension;                       
                // Upload Image
                $path = $request->file('birthcertificate')->storeAs('public/birthcertificate', $fileNameToStore);
                

                auth()->user()->studentdocument()->create([
                    'birthcertificate' => $request->birthcertificate,
                    'otherdocument' => $otherdocuments,
                    'user_id' => auth()->id()
                ]);
                $success['statuscode'] = 505;    
                $success['message'] = "documents uploaded successfully";
                return response()->json(['success'=>$success]);
    
    }

    public function documentdelete($id)
    {
        $user = Auth::user();
        if($user)
        {
            $docs = auth()->user()->studentdocument;
            if(count($docs)>0)
            {
                auth()->user()->studentdocument($id)->delete();
                return response()->json(['success'=>"Document is delete"], 506);
            }
            else
            {
                return response()->json(['error'=>"None of your document found"], 507);
            }
        }
    }



}
