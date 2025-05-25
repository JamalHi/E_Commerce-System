<?php

namespace App\Http\Controllers;

use App\Models\Size;
use App\Models\User;
use App\Models\Color;
use App\Models\Image;
use App\Models\Payment;
use App\Models\Product;
use App\Models\UserExtra;
use Illuminate\Http\Request;
use App\Http\Controllers\MailController;
use phpseclib3\Crypt\EC\BaseCurves\Prime;

class AdminController extends Controller
{
    public function show_users_extra(){

        $users1 = UserExtra::where('accept',1)->get();
        foreach($users1 as $user1){
            $user = User::where('id',$user1->user_id)->first();
            $response[]=([
                'user' => $user,
                'user_extra' => $user1,
            ]);
        }
        /*$users = User::query()->get();
        foreach ($users as $user){
            if($user->role_id == 3 || $user->role_id == 4 || $user->role_id == 5 ){
                $response[]=([
                    'user' => $user,
                ]);
            }
        }*/

        /*
        if( sizeof($users) == 0 && sizeof($users1) == 0 ){
            return response()->json(['message' => 'there are no users'],status:404);
        }
        else{
            //$response = collect($response)->sortBy('user')->toArray();
            return response()->json($response,status : 200);
        }
        */
        if(!isset($response)){return response()->json("this list is empty",404);}
        return response()->json($response,status : 200);
    }

    public function show_users(){

        $users = User::query()->get();
        foreach ($users as $user){
            if($user->role_id == 3 || $user->role_id == 4 || $user->role_id == 5 ){
                $response[]=([
                    'user' => $user,
                ]);
            }
        }
        if( sizeof($users) == 0 ){
            return response()->json(['message' => 'there are no users'],status:404);
        }
        else{
            //$response = collect($response)->sortBy('user')->toArray();
            return response()->json($response,status : 200);
        }
    }

    public function search_user(Request $request){
        $query = $request->query('user_name');
        $users1 = UserExtra::where('accept',1)->get();
        foreach($users1 as $user1){
            $user = User::where('name','like',"%$query%")->where('id',$user1->user_id)->first();

            //if($use->isEmpty()){return response()->json("this list is empty!",200); }

            if(!is_null($user)){
                $user2 = UserExtra::where('user_id',$user->id)->where('accept',1)->get();
                $response[]=([
                    'user' => $user,
                    'user_extra' => $user2,
                ]);
            }
        }
        $users = User::where('name','like',"%$query%")->get();
        foreach ($users as $k){
            if($k->role_id == 3 || $k->role_id == 4 || $k->role_id == 5 ){
                $response[]=([
                    'user' => $k,
                ]);
            }
        }
        //if(sizeof($users) == 0 && sizeof($user2) == 0 && is_null($user)){
        if(!isset($response)){
            return response()->json(['message' => 'there are no users'],status:404);
        }
        else{
            return response()->json($response,status : 200);
        }

    }

    public function filter_user(Request $request){
            $query = $request->query('role_id');
            $user = User::where('role_id','like',"%$query%")->get();
            $users1 = UserExtra::where('accept',1)->get();
            foreach($users1 as $user1){
                $user = User::where('role_id','like',"%$query%")->where('id',$user1->user_id)->first();

                if(!is_null($user)){
                    $user2 = UserExtra::where('user_id',$user->id)->where('accept',1)->get();
                    $response[]=([
                        'user' => $user,
                        'user_extra' => $user2,
                    ]);
                }
            }
            $users = User::where('role_id','like',"%$query%")->get();
            foreach ($users as $k){
                if($k->role_id == 3 || $k->role_id == 4 || $k->role_id == 5 ){
                    $response[]=([
                        'user' => $k,
                    ]);
                }
            }
            if(!isset($response) /*&& sizeof($user2) == 0 && is_null($user)*/){
                return response()->json(['message' => 'there are no users'],status:404);
            }
            else{
                return response()->json($response,status : 200);
            }
    }


    public function delete_user($id)
    {
        $user = User::find($id);
        if(is_null($user)){
            return response()->json(['message' => 'not found'],status:404);
        }
        else{
            $user->delete();
//send email
            $mailcontroller = resolve(MailController::class);
            $mailcontroller->sendEmail("عذراً ، لقد تم طردك من التطبيق" , $user->email);

            return response()->json(['message' => 'deleted successfully'],status:200);
        }
    }

    public function show_join_request(){

        $users1 = UserExtra::where('accept',0)->get();
        foreach($users1 as $user1){
            $user = User::where('id',$user1->user_id)->first();
            $response[]=([
                'user' => $user,
                'user_extra' => $user1,
            ]);
        }

        if(sizeof($users1) == 0 ){
            return response()->json(['message' => 'there are no users'],status:200);
        }
        else{
            $response = collect($response)->sortBy('user')->toArray();
            return response()->json($response,status : 200);
        }
    }

    public function accept_deny_request(Request $request , $id){
        $mailcontroller = resolve(MailController::class);

        $u = User::find($id);
        $user = UserExtra::where('user_id',$id)->first();
        if(is_null($user)){
            return response()->json(['message' => 'not found'],status:404);
        }
        if($request->accept == 1){
            $user->update([
                'accept' => 1
            ]);
            $user->save();
//send email
            $mailcontroller->sendEmail("يسعدنا اضمامك إلى تطبيقنا ، أنت الآن أحد أعضاء التطبيق " , $u->email);

        return response()->json([$user , 'message' => 'the request accepted successfully'],status:200);
        }
        else if($request->accept == 0){
            $user->update([
                'accept' => 0
            ]);
            $user->save();
//send email
            $mailcontroller->sendEmail("عذراً ، لم يتم قبول طلب انضمامك" , $u->email);

            return response()->json([$user , 'message' => 'the request denyed successfully'],status:200);
        }

    }

    public function show_all_products(){
        $products = Product::query()->get();
        foreach($products as $product){
            $i=Image::where('product_id',$product->id)->first();
            $result[]=([
                'product'=>$product,
                'image'=>$i->image,
                'image_id'=>$i->id,
            ]);
        }
        if($products->isEmpty()){
            return response()->json(['message' => 'there are no products !!'],status:404);
        }
        return response()->json($result , status:200);
    }

    public function delete_product($id){
        $product = Product::find($id);
        $userextra = UserExtra::where('id',$product->owner_id)->first();
        $user = User::where('id',$userextra->user_id)->first();
        if(is_null($product)){
            return response()->json(['message' => 'product not found'] , status:404);
        }
        else{
            $product->delete();
//send email
            $mailcontroller = resolve(MailController::class);
            $mailcontroller->sendEmail("عذراً ، لقد تم حذف أحد منتجاتك ، يرجى التواصل معنا" , $user->email);
            return response()->json(['message' => 'deleted successfully'],status:200);
        }
    }

    public function search_product(Request $request){
        $query = $request->query('name');
        $products = Product::where('name','like',"%$query%")->get();
        if(sizeof($products) != 0){
            foreach($products as $product){
                $i=Image::where('product_id',$product->id)->first();
                $result[]=([
                    'product'=>$product,
                    'image'=>$i->image,
                    'image_id'=>$i->id,
                ]);
            }
            return response()->json($result,status:200);
        }
        else
            return response()->json(['message' => 'not found'],status:404);
    }

    public function filter_product(Request $request){
        $query = $request->query('category');
        $query1 = $request->query('owner_id');
        if(!is_null($query)){
            $products = Product::where('category',$query)->get();
            if(sizeof($products) != 0){
                foreach($products as $product){
                    $i=Image::where('product_id',$product->id)->first();
                    $result[]=([
                        'product'=>$product,
                        'image'=>$i->image,
                        'image_id'=>$i->id,
                    ]);
                }
                return response()->json($result,status:200);
            }
            else{
                return response()->json(['message' => 'this category is empty'] ,status:404);
            }
        }
        else if(!is_null($query1)){
            $products = Product::where('owner_id',$query1)->get();
            if(sizeof($products) != 0){
                foreach($products as $product){
                    $i=Image::where('product_id',$product->id)->first();
                    $result[]=([
                        'product'=>$product,
                        'image'=>$i->image,
                        'image_id'=>$i->id,
                    ]);
                }
                return response()->json($result,status:200);
            }
            else{
                return response()->json(['message' => 'this owner does not have any products'] ,status:404);
            }
        }
    }

    /*
        public function show_product_details($id){
            $product = Product::where('id',$id)->first();
            if(is_null($product)){
                return response()->json(['message' => 'product not found'],status:404);
            }
            else{
                $product->image;
                return response()->json($product,status:200);
            }
        }
    */

    //ran edit
    public function show_product_details($id){
        $pro=Product::where('id',$id)->first();
        $user = User::where('id',$pro->owner_id)->first();
        $user->UserExtra;
        $imgs=Image::where('product_id',$pro->id)->get();
        foreach ($imgs as $i)
        {
            $is[]=([
                "id"=>$i->id,
                "image"=>$i->image,
                "product_id"=>$i->product_id
            ]);
        }

        $c=Color::where('product_id',$pro->id)->get();
        foreach ($c as $cc) {
            $s=Size::where('color_id',$cc->id)->get();
            foreach ($s as $ss) {
                $size[]=$ss;
            }
            $colors_and_sizes[]=([
                "color"=>$cc,
                "sizes"=>$size,
            ]);
            $size=null;
        }

        $response[]=([
            "product"=>$pro,
            "images"=>$is,
            "colors_and_sizes"=>$colors_and_sizes,
            "owner" => $user,
        ]);
        return response()->json([$response,'status'=>200,'message'=>'success']);
        //return response()->json($response,200);

    }

    public function admin_add_credit(request $request){
        if(Payment::where('user_id',$request->user_id)->exists())
        {
            $u=Payment::where('user_id',$request->user_id)->first();
            $u->wallet=$u->wallet+$request->new_credit;
            $u->save();
        }
        else{
            $p=Payment::create([
                'user_id'=>$request->user_id,
                'wallet'=>$request->new_credit,
            ]);
        }

        return response()->json("done successfuly!",200);
    }

    // ran edit
    public function admin_add_credit_view(request $request){

        if ($request->email==null && $request->new_credit==null)
            {
                return redirect()->back()->with(['message'=>'يرجى ملئ جميع الخانات']);
            }
        if($request->email==null )
            {
                return redirect()->back()->with(['message'=>'يرجى إدخال بريد إلكتروني']);
            }
        if(!$user=user::where('email',$request->input('email'))->first())
            {
                return redirect()->back()->with(['message'=>'بريدإلكتروني خاطئ , حاول مجدداً']);
            }
        if($request->new_credit==null )
            {
                return redirect()->back()->with(['message'=>'يرجى إدخال قيمة للرصيد']);
            }

        $user=User::where('email',$request->email)->first();
        if(Payment::where('user_id',$user->id)->exists())
        {
            $u=Payment::where('user_id',$user->id)->first();
            $u->wallet=$u->wallet+$request->new_credit;
            $u->save();
        }
        else{
            $p=Payment::create([
                'user_id'=>$user->id,
                'wallet'=>$request->new_credit,
            ]);
        }
        //return view('add_credit');
        return redirect()->to('http://192.168.43.57:8000/add_credit')->with(['success'=>'تمت العملية بنجاح']);
    }
}

