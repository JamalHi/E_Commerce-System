<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Role;
use App\Models\Area;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\UserExtra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule as ValidationRule;


class AuthController extends Controller
{

    public function createAccountCompany(Request $request){
        $validator = validator::make($request->all(), [
            'name' => ['required' , 'string' , 'max:255'],
            'email' => ['required' , 'string' , 'email' , 'max:255' ,ValidationRule::unique(table:'users')],
            'password' => ['required' , 'string' , 'min:4'],
            'phone' => ['required' , 'string'],
          //  'prof_img' => ['required' , 'image'],
            'owner' => 'required',
          //  'licence' => ['required' , 'image'],
          //  'commerical_record' => ['required' , 'image'],
          //  'personal_id' => ['required' , 'image'],
            'area_str' => 'required',
            'p_type' => 'required',
            'description' => 'required',

        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->all(),status:400);
        }

        if (!$email=Str::of($request->email)->endsWith(['@gmail.com','@yahoo.com','@outlook.com']))
        {
        return response()->json(['message' => 'Invalid E-mail formula!',
                                 'status' => '400'
                                ]);
        }
        else if($email = Str::before($request->email, '@')==null)
        {
            return response()->json(['message' => 'Invalid E-mail formula!',
                                      'status' => '400'
                                    ]);
        }

        $request['password'] = Hash::make($request['password']);

        /*if($request->hasFile('prof_img')){
            $img_name = time() . '.' . $request->prof_img->getClientOriginalExtension();
            $img_name = $request->file('prof_img')->store('upload','public');
            $request->prof_img->move(public_path('upload'),$img_name);
        }*/
        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
      //      'prof_img' => 'djfhdjh',//$img_name,
            'role_id' => 1,
        ]);

    /*   if($request->hasFile('licence')){
            $img_name1 = time() . '.' . $request->licence->getClientOriginalExtension();
            $img_name1 = $request->file('licence')->store('upload','public');
            $request->licence->move(public_path('upload'),$img_name1);
        }

        if($request->hasFile('commerical_record')){
            $img_name2 = time() . '.' . $request->commerical_record->getClientOriginalExtension();
            $img_name2 = $request->file('commerical_record')->store('upload','public');
            $request->commerical_record->move(public_path('upload'),$img_name2);
        }

        if($request->hasFile('personal_id')){
            $img_name3 = time() . '.' . $request->personal_id->getClientOriginalExtension();
            $img_name3 = $request->file('personal_id')->store('upload','public');
            $request->personal_id->move(public_path('upload'),$img_name3);
        }*/

        $a=Area::where('area',$request->area_str)->first();
        $user1 = UserExtra::query()->create([
            'user_id' => $user->id,
            'owner' => $request->owner,
      //      'licence'=> 'fdvnj j',//$img_name1,
      //      'commerical_record' => 'kdfovkkvk',// $img_name2,
      //      'personal_id' =>'cjncn', //$img_name3,
            'area_id' => $a->id,
            'p_type' => $request->p_type,
            'description' => $request->description,
        ]);

        //add token to user
        $tokenResult = $user->createToken('personal Access Token');

        $data["user"] = $user ;
        $data["user_extra"] = $user1;
        $data["token_type"] = 'Bearer';
        $data["access_token"] = $tokenResult->accessToken;

//send email
        $admins = User::where('role_id',5)->get();
        $mailcontroller = resolve(MailController::class);
        foreach($admins as $k){
            $mailcontroller->sendEmail("مرحباً ، أريد الانضمام إلى تطبيقكم" , $k->email);
        }

        return response()->json([$data , 'status' => 200 , 'message' => 'signed up successfully']);

    }

    public function createAccountMarket(Request $request){
            $validator = validator::make($request->all(), [
            'name' => ['required' , 'string' , 'max:255'],
            'email' => ['required' , 'string' , 'email' , 'max:255' ,ValidationRule::unique(table:'users')],
            'password' => ['required' , 'string' , 'min:4'],
            'phone' => ['required' , 'string'],
         //   'prof_img' => ['required' , 'image'],
            'owner' => 'required',
         //   'licence' => ['required' , 'image'],
         //   'commerical_record' => ['required' , 'image'],
         //   'personal_id' => ['required' , 'image'],
            'area_str' => 'required',
            'p_type' => 'required',
            'description' => 'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->all(),status:400);
        }

        if (!$email=Str::of($request->email)->endsWith(['@gmail.com','@yahoo.com','@outlook.com']))
        {
        return response()->json(['message' => 'Invalid E-mail formula!',
                                 'status' => '400'
                                ]);
        }
        else if($email = Str::before($request->email, '@')==null)
        {
            return response()->json(['message' => 'Invalid E-mail formula!',
                                      'status' => '400'
                                    ]);
        }

        $request['password'] = Hash::make($request['password']);

        /*if($request->hasFile('prof_img')){
            $img_name = time() . '.' . $request->prof_img->getClientOriginalExtension();
            $img_name = $request->file('prof_img')->store('upload','public');
            $request->prof_img->move(public_path('upload'),$img_name);
        }*/
        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
         //   'prof_img' => $img_name,
            'role_id' => 2,
        ]);

        /*if($request->hasFile('licence')){
            $img_name1 = time() . '.' . $request->licence->getClientOriginalExtension();
            $img_name1 = $request->file('licence')->store('upload','public');
            $request->licence->move(public_path('upload'),$img_name1);
        }
        if($request->hasFile('commerical_record')){
            $img_name2 = time() . '.' . $request->commerical_record->getClientOriginalExtension();
            $img_name2 = $request->file('commerical_record')->store('upload','public');
            $request->commerical_record->move(public_path('upload'),$img_name2);
        }
        if($request->hasFile('personal_id')){
            $img_name3 = time() . '.' . $request->personal_id->getClientOriginalExtension();
            $img_name3 = $request->file('personal_id')->store('upload','public');
            $request->personal_id->move(public_path('upload'),$img_name3);
        }*/

        $a=Area::where('area',$request->area_str)->first();
        $user1 = UserExtra::query()->create([
            'user_id' => $user->id,
            'owner' => $request->owner,
        //    'licence'=> $img_name1,
        //    'commerical_record' => $img_name2,
        //    'personal_id' => $img_name3,
            'area_id' => $a->id,
            'p_type' => $request->p_type,
            'description' => $request->description,
        ]);
        //add token to user
        $tokenResult = $user->createToken('personal Access Token');

        $data["user"] = $user ;
        $data["user_extra"] = $user1;
        $data["token_type"] = 'Bearer';
        $data["access_token"] = $tokenResult->accessToken;

//send email
        $admins = User::where('role_id',5)->get();
        $mailcontroller = resolve(MailController::class);
        foreach($admins as $k){
            $mailcontroller->sendEmail("مرحباً ، أريد الانضمام إلى تطبيقكم" , $k->email);
        }

        return response()->json([$data , 'status' => 200 , 'message' => 'signed up successfully']);

    }

    public function addImage(Request $request , $id){
        $user = User::find($id);
        $validator = validator::make($request->all(), [
            'prof_img' => ['required' , 'image'],
            'licence' => ['required' , 'image'],
            'commerical_record' => ['required' , 'image'],
            'personal_id' => ['required' , 'image'],
        ]);
        if($validator->fails()){
            return response()->json( $validator->errors()->all(),status:400);
        }
        if($request->hasFile('prof_img')){
            $img_name = time() . '.' . $request->prof_img->getClientOriginalExtension();
            $img_name = $request->file('prof_img')->store('upload','public');
            $request->prof_img->move(public_path('upload'),$img_name);
        }
        $user->update([
            'prof_img' => $img_name,
        ]);
        $user->save();

        if($request->hasFile('licence')){
            $img_name1 = time() . '.' . $request->licence->getClientOriginalExtension();
            $img_name1 = $request->file('licence')->store('upload','public');
            $request->licence->move(public_path('upload'),$img_name1);
        }
        if($request->hasFile('commerical_record')){
            $img_name2 = time() . '.' . $request->commerical_record->getClientOriginalExtension();
            $img_name2 = $request->file('commerical_record')->store('upload','public');
            $request->commerical_record->move(public_path('upload'),$img_name2);
        }
        if($request->hasFile('personal_id')){
            $img_name3 = time() . '.' . $request->personal_id->getClientOriginalExtension();
            $img_name3 = $request->file('personal_id')->store('upload','public');
            $request->personal_id->move(public_path('upload'),$img_name3);
        }

        $user1 = UserExtra::where('user_id',$id)->first();
        $user1->update([
            'licence'=> $img_name1,
            'commerical_record' => $img_name2,
            'personal_id' => $img_name3,
        ]);

        $data["user"] = $user;
        $data["userExtra"] = $user1;

        return response()->json([$data , 'status' => 200 , 'message' => 'image added successfully']);
    }


    public function createAccountClient(Request $request){
        $validator = validator::make($request->all(), [
            'name' => ['required' , 'string' , 'max:255'],
            'email' => ['required' , 'string' , 'email' , 'max:255' ,ValidationRule::unique(table:'users')],
            'password' => ['required' , 'string' , 'min:4'],
            'phone' => ['required' , 'string'],
            'prof_img' => ['image'],
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->all(),status:400);
        }

        if (!$email=Str::of($request->email)->endsWith(['@gmail.com','@yahoo.com','@outlook.com']))
        {
        return response()->json(['message' => 'Invalid E-mail formula!',
                                 'status' => '400'
                                ]);
        }
        else if($email = Str::before($request->email, '@')==null)
        {
            return response()->json(['message' => 'Invalid E-mail formula!',
                                      'status' => '400'
                                    ]);
        }

        $request['password'] = Hash::make($request['password']);

        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'role_id' => 3,
        ]);

        if($request->hasFile('prof_img')){
            $img_name = time() . '.' . $request->prof_img->getClientOriginalExtension();
            $img_name = $request->file('prof_img')->store('upload','public');
            $request->prof_img->move(public_path('upload'),$img_name);

            $user->update([
                'prof_img' => $img_name,
            ]);
            $user->save();
        }

        //add token to user
        $tokenResult = $user->createToken('personal Access Token');

        $data["user"] = $user ;
        $data["token_type"] = 'Bearer';
        $data["access_token"] = $tokenResult->accessToken;

        return response()->json([$data , 'status' => 200 , 'message' => 'signed up successfully']);

    }


    public function createAccountDelivery(Request $request){
        $validator = validator::make($request->all(), [
            'name' => ['required' , 'string' , 'max:255'],
            'email' => ['required' , 'string' , 'email' , 'max:255' ,ValidationRule::unique(table:'users')],
            'password' => ['required' , 'string' , 'min:4'],
            'phone' => ['required' , 'string'],
            'prof_img' => ['required' , 'image'],

        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->all(),status:400);
        }

        if (!$email=Str::of($request->email)->endsWith(['@gmail.com','@yahoo.com','@outlook.com']))
        {
        return response()->json(['message' => 'Invalid E-mail formula!',
                                 'status' => '400'
                                ]);
        }
        else if($email = Str::before($request->email, '@')==null)
        {
            return response()->json(['message' => 'Invalid E-mail formula!',
                                      'status' => '400'
                                    ]);
        }

        $request['password'] = Hash::make($request['password']);

        if($request->hasFile('prof_img')){
            $img_name = time() . '.' . $request->prof_img->getClientOriginalExtension();
            $img_name = $request->file('prof_img')->store('upload','public');
            $request->prof_img->move(public_path('upload'),$img_name);
        }
        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'prof_img' => $img_name,
            'role_id' => 4,
        ]);

        //add token to user
        $tokenResult = $user->createToken('personal Access Token');

        $data["user"] = $user ;
        $data["token_type"] = 'Bearer';
        $data["access_token"] = $tokenResult->accessToken;

        return response()->json([$data , 'status' => 200 , 'message' => 'signed up successfully']);

    }

    public function createAccountAdmin(Request $request){
        $validator = validator::make($request->all(), [
            'name' => ['required' , 'string' , 'max:255'],
            'email' => ['required' , 'string' , 'email' , 'max:255' ,ValidationRule::unique(table:'users')],
            'password' => ['required' , 'string' , 'min:4'],
            'phone' => ['required' , 'string'],
         //   'prof_img' => ['required' , 'image'],

        ]);
        if($validator->fails()){
            return response()->json( $validator->errors()->all(),status:400);
        }

        if (!$email=Str::of($request->email)->endsWith(['@gmail.com','@yahoo.com','@outlook.com']))
        {
        return response()->json(['message' => 'Invalid E-mail formula!',
                                 'status' => '400'
                                ]);
        }
        else if($email = Str::before($request->email, '@')==null)
        {
            return response()->json(['message' => 'Invalid E-mail formula!',
                                      'status' => '400'
                                    ]);
        }

        $request['password'] = Hash::make($request['password']);

        /*if($request->hasFile('prof_img')){
            $img_name = time() . '.' . $request->prof_img->getClientOriginalExtension();
            $img_name = $request->file('prof_img')->store('upload','public');
            $request->prof_img->move(public_path('upload'),$img_name);
        }*/
        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
         //   'prof_img' => $img_name,
            'role_id' => 5,
        ]);

        //add token to user
        $tokenResult = $user->createToken('personal Access Token');

        $data["user"] = $user ;
        $data["token_type"] = 'Bearer';
        $data["access_token"] = $tokenResult->accessToken;

        return response()->json([$data , 'status' => 200 , 'message' => 'signed up successfully']);

    }


    public function add_image_admin(Request $request, $id){
        $user = User::find($id);
        $validator = validator::make($request->all(), [
            'prof_img' => ['required' , 'image'],
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->all(),status:400);
        }
        if($request->hasFile('prof_img')){
            $img_name = time() . '.' . $request->prof_img->getClientOriginalExtension();
            $img_name = $request->file('prof_img')->store('upload','public');
            $request->prof_img->move(public_path('upload'),$img_name);
        }
        $user->update([
            'prof_img' => $img_name,
        ]);
        return response()->json([$user,'message' => 'added successfully'],status:200);
    }


    public function login(Request $request){
        $validator = validator::make($request->all(), [
            'email' => ['required' , 'string' , 'email' , 'max:255'],
            'password' => ['required' , 'string' , 'min:4'],
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->all() , status:422);
        }
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials)){
            throw new AuthenticationException();
        }
        $user = $request->user();
        //add token to user
        $tokenResult = $user->createToken('personal Access Token');//->accessToken;

        $user = User::where('id' , '=' , auth()->id())->first();
        $role = Role::where('id' , '=' , $user->role_id)->first();

        $data["user"] = $user;
        $data["token_type"] = 'Bearer';
        $data["access_token"] = $tokenResult->accessToken;

        $data["role"] = $role;
        $data["permissions"] = $role->permissions()->get();

        //ran edit 18/aug
        if($user->role_id==1 || $user->role_id==2)
        {
            $ue=UserExtra::where('user_id',$user->id)->first();
            $data['accept'] = $ue->accept;
        }

        return response()->json([$data,'status'=>200,'message'=>'logedd In successfully']);

    }

    public function update(Request $request , $id){
        $user = User::find($id);
        if(is_null($user))
            return response()->json(['message' => 'not found'],status:404);
        if($user->id == Auth::id()){
            $validator = validator::make($request->all(), [
                'name' => [ 'string' , 'max:255'],
                'email' => [ 'string' , 'email' , 'max:255'/* ,ValidationRule::unique(table:'users')*/],
                'password' => [ 'string' , 'min:4'],
                'phone' => [ 'string'],
                'prof_img' => [ 'image'],
            ]);
            if($validator->fails()){
                return response()->json($validator->errors()->all(),status:400);
            }
            if($request->password != null){
                $request['password'] = Hash::make($request['password']);
            }
            if($request->hasFile('prof_img')){
                //$user->prof_img = null;
                //$user->save();
                $img_name = time() . '.' . $request->prof_img->getClientOriginalExtension();
                $img_name = $request->file('prof_img')->store('upload','public');
                $request->prof_img->move(public_path('upload'),$img_name);
                if($img_name != null){
                    $user->update(['prof_img' => $img_name]);
                    $user->save();
                }
            }
            $name = $request->name;
            $email = $request->email;
            $password = $request->password;
            $phone = $request->phone;

            if($name != null){
                $user->update(['name' => $name]);
                $user->save();
            }
            if($email != null){
                $user->update(['email' => $email]);
                $user->save();
            }
            if($password != null){
                $user->update(['password' => $password]);
                $user->save();
            }
            if($phone != null){
                $user->update(['phone' => $phone]);
                $user->save();
            }

            $user1 = UserExtra::where('user_id' , $user->id)->first();

            if($user->role_id == 1 || $user->role_id == 2){
                $validator = validator::make($request->all(), [
                    'owner' => ['string'],
                    'licence' => [ 'image'],
                    'commerical_record' => [ 'image'],
                    'personal_id' => [ 'image'],
                    //'area_id' => ['required'],
                    'p_type' => ['string'],
                    'description' => ['string'],
                ]);
                if($validator->fails()){
                    return response()->json($validator->errors()->all(),status:400);
                }

                if($request->hasFile('licence')){
                    $img_name1 = time() . '.' . $request->licence->getClientOriginalExtension();
                    $img_name1 = $request->file('licence')->store('upload','public');
                    $request->licence->move(public_path('upload'),$img_name1);
                    if($img_name1 != null){
                        $user1->update(['licence' => $img_name1]);
                        $user1->save();
                    }
                }

                if($request->hasFile('commerical_record')){
                    $img_name2 = time() . '.' . $request->commerical_record->getClientOriginalExtension();
                    $img_name2 = $request->file('commerical_record')->store('upload','public');
                    $request->commerical_record->move(public_path('upload'),$img_name2);
                    if($img_name2 != null){
                        $user1->update(['commerical_record' => $img_name2]);
                        $user1->save();
                    }
                }

                if($request->hasFile('personal_id')){
                    $img_name3 = time() . '.' . $request->personal_id->getClientOriginalExtension();
                    $img_name3 = $request->file('personal_id')->store('upload','public');
                    $request->personal_id->move(public_path('upload'),$img_name3);
                    if($img_name3 != null){
                        $user1->update(['personal_id' => $img_name3]);
                        $user1->save();
                    }
                }

                $owner = $request->owner;
                $area_id = $request->area_id;
                $p_type = $request->p_type;
                $description = $request->description;
                $area_id = $request->area_id;
                if($owner != null){
                    $user1->update(['owner' => $owner]);
                    $user1->save();
                }
                if($area_id  != null){
                    $user1->update(['area_id' => $area_id]);
                    $user1->save();
                }
                if($p_type  != null){
                    $user1->update(['p_type' => $p_type]);
                    $user1->save();
                }
                if($description != null){
                    $user1->update(['description' => $description]);
                    $user1->save();
                }


                if($area_id != null){
                    $user1->update(['area_id' => $area_id]);
                    $user1->save();
                }

            //    if($user1 != null){
                    $data["user_extra"] = $user1;
            //    }
            }
                    //add token to user
            //$tokenResult = $user->createToken('personal Access Token');

            $data["user"] = $user ;
            //$data["token_type"] = 'Bearer';
            //$data["access_token"] = $tokenResult->accessToken;

            //$user->save();

            return response()->json([$data,'status'=>200,'message'=>'updated successfully']);
        }
        else {
            return response()->json(['message' => 'you can not do that ,you are not the accounts owner','status'=>401]);
        }
    }


    public function show($id){
        $us = User::where('id', Auth::id())->first();
        if(Auth::id() == $id || $us->role_id == 5){
            $user = User::where('id',$id)->first();
            $data["user"] = $user;
            $wallett = Payment::where('user_id',$user->id)->first();
            //if(!$wallett->isEmpty()){
                $user->wallet;
            //}
            if(is_null($user)){
                return response()->json(['message' => 'user not found' ], status : 404);
            }
            if($user->role_id == 1 || $user->role_id == 2 ){
                $user->userExtra;
            }
            return response()->json([$data,'status'=>200,'message'=>'show successfully'] , status : 200);
        }
        else {
            return response()->json(['message' => 'you can not do that'] , status : 404);
        }
    }

    public function logout(Request $request ){
        $request->user()->token()->revoke();
        return response()->json(['message' => 'logged out ','status'=>200]);
    }
}
