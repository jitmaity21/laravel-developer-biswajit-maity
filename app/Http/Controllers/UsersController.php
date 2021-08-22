<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Notifications\InviteNotification;
use App\Notifications\EmailOTPNotification;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use DB;
use Str;
use Image;
use JWTAuth;
use Validator;
use Notification;
use App\Models\Invite;



class UsersController extends Controller
{
    public function login(Request $request)
    {    
        $validator = Validator::make($request->all(), [
        'email' => 'required|string',
        'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = request(['email', 'password']);
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }else{

                if (JWTAuth::user()->is_email_verified == 'N') {
                    return response()->json(['error' => 'Your Email Is Not Verified'], 401);
                }
                $res['access_token'] = $token;
                $res['user_details'] = JWTAuth::user();
                //return response()->json(compact('token'));
                return response()->json(['success' => true,'message' => 'Login Successfull','result' => $res]);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could Not Create Token'], 500);
        }
        

        
    }

    public function invite(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

       
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email'
        ]);
        $validator->after(function ($validator) use ($request) {
            if (Invite::where('email', $request->input('email'))->exists()) {
                $validator->errors()->add('email', 'There exists an invite with this email!');
            }
        });
        if ($validator->fails()) {
            return response()->json(['error' => 'Please check input validation','message'=>$validator->errors()],422);
        }
        do {
            $token = Str::random(20);
        } while (Invite::where('token', $token)->first());
        Invite::create([
            'token' => $token,
            'email' => $request->input('email')
        ]);
        $url = URL::temporarySignedRoute(
    
            'signUp', now()->addMinutes(300), ['token' => $token]
        );
        
        Notification::route('mail', $request->input('email'))->notify(new InviteNotification($url));
        return response()->json(['$url'=>$url,'message'=> 'The Invite has been sent successfully' ],200);
        
    }


    public function signUp($token,Request $request){

        $invite = Invite::where('token', $token)->first();

        if(!empty($invite)){
            $validator = Validator::make($request->all(), [
               
                'user_name' => 'required|string|min:4|max:20',
                'password' => 'required|string|min:6'
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => 'Please check input validation','message'=>$validator->errors()],422);
            }else{

                $six_digit_random_number = random_int(100000, 999999);

                User::create([
                    'email' => $invite->email,
                    'user_name' => $request->input('user_name'),
                    'password' => Hash::make($request->input('password')),
                    'user_pin' => $six_digit_random_number,
                    'is_email_verified' => 'N',
                    'registered_at' => date('Y-m-d H:i:s')
                    
                ]);
                Notification::route('mail', $invite->email)->notify(new EmailOTPNotification($six_digit_random_number));
                return response()->json(['message'=>'A six digit otp sent to your registerd email id'],200);

            }

        }else{
            return response()->json(['message'=> 'Email Does not Exits'],200);
        }


    }

    public function emailverify(Request $request)
    {
        $validator = Validator::make($request->all(), [
               
            'user_pin' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Please check input validation','message'=>$validator->errors()],422);
        }else{
            $user = User::where('email', $request->input('email'))->first();
            if(!empty($user) && $user->user_pin == $request->input('user_pin')){
                $update = DB::table('users')
                            ->where('email', $request->input('email'))
                            ->update(['is_email_verified' => 'Y']);

                return response()->json(['status'=>'success','message'=>'Your email is verified successfully and Registration Complete'],200);
            }else{
                return response()->json(['status'=>'failed','message'=>'Invalid Otp or Email'],200);
            }

        }

    }

    public function updateprofile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'mimes:jpg,jpeg,png,bmp,tiff |max:4096',
            'email'  => 'required',
            'user_name' => 'string|min:4|max:20|unique:users'
        ], $messages = [
            'mimes' => 'Please upload image in jpg | png | jpeg |svg',
            'max'   => 'Image should be less than 4 MB',
        ]);

        $user = User::where('email', $request->input('email'))->first();
         
        if ($validator->fails()) {

            return response()->json(['error' => 'Please check input validation', 'message' => $validator->errors()], 422);
        } else {

            if(!empty($user) && $user->is_email_verified == 'Y'){

                if ($request->file('avatar') != '') {
                    $path = public_path('/uploads/avatars/');


                    // new intervention

                        $image = $request->file('avatar');
                        $input['avatar'] = time().'.'.$image->extension();
                        $destinationPath = public_path().'/storage/uploads/users/photos/' . $input['avatar'];
                        $img = Image::make($image->path());
                        $img->resize(256, 256, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath);

                    // intervention end

                    $update = User::where('id', $user->id)->update([
                        'avatar' => $input['avatar'],
                        'user_name'=> $request->input('user_name'),
                        'name'=>$request->input('name'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    return response()->json(['status' => 'success', 'message' => 'Profile  updated successfully'], 200);
                }
            }else{
                return response()->json(['status' => 'success', 'message' => 'Please Verify your Email Id'], 200);
            }
        }


    }

   

    

}
