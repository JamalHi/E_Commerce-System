<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserExtra;
use phpseclib3\Crypt\EC\BaseCurves\Prime;

class AdminController extends Controller
{
    public function show_users(){

        $users1 = UserExtra::where('accept',1)->get();
        foreach($users1 as $user1){
            $user = User::where('id',$user1->user_id)->first();
            $response[]=([
                'user' => $user,
                'user_extra' => $user1,
            ]);
        }
        $users = User::query()->get();
        foreach ($users as $user){
            if($user->role_id == 3 || $user->role_id == 4 || $user->role_id == 5 ){
                $response[]=([
                    'user' => $user,
                ]);
            }
        }
        if( sizeof($users) == 0 && sizeof($users1) == 0 ){
            return response()->json(['message' => 'there are no users'],status:404);
        }
        else{
            $response = collect($response)->sortBy('user')->toArray();
            return response()->json($response,status : 200);
        }
    }

    public function search_user(Request $request){
        $query = $request->query('user_name');
        $users1 = UserExtra::where('accept',1)->get();
        foreach($users1 as $user1){
            $user = User::where('name','like',"%$query%")->where('id',$user1->user_id)->first();

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
        if(sizeof($users) == 0 /*&& sizeof($user2) == 0 && is_null($user)*/){
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
            if(sizeof($users) == 0 /*&& sizeof($user2) == 0 && is_null($user)*/){
                return response()->json(['message' => 'there are no users'],status:404);
            }
            else{
                return response()->json($response,status : 200);
            }
    }


    public function delete_user($id){
        $user = User::find($id);
        if(is_null($user)){
            return response()->json(['message' => 'not found'],status:404);
        }
        else{
            $user->delete();
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
            return response()->json(['message' => 'there are no users'],status:404);
        }
        else{
            $response = collect($response)->sortBy('user')->toArray();
            return response()->json($response,status : 200);
        }
    }

    public function accept_deny_request(Request $request , $id){
        $user = UserExtra::where('user_id',$id)->first();
        if(is_null($user)){
            return response()->json(['message' => 'not found'],status:404);
        }
        if($request->accept == 1){
            $user->update([
                'accept' => 1
            ]);
            $user->save();
        return response()->json([$user , 'message' => 'the request accepted successfully'],status:200);
        }
        else if($request->accept == 0){
            $user->update([
                'accept' => 0
            ]);
            $user->save();
            return response()->json([$user , 'message' => 'the request denyed successfully'],status:200);
        }

    }

    public function show_all_products(){
        $products = Product::query()->get();
        foreach($products as $product){
            $product->image;
        }
        return response()->json($products , status:200);
    }

    public function delete_product($id){
        $product = Product::find($id);
        if(is_null($product)){
            return response()->json(['message' => 'product not found'] , status:404);
        }
        else{
            $product->delete();
            return response()->json(['message' => 'deleted successfully']);
        }
    }

    public function search_product(Request $request){
        $query = $request->query('name');
        $products = Product::where('name','like',"%$query%")->get();
        if(sizeof($products) != 0){
            foreach($products as $product){
                $product->image;
            }
            return response()->json($products,status:200);
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
                    $product->image;
                }
                return response()->json($products,status:200);
            }
            else{
                return response()->json(['message' => 'this category is empty'] ,status:404);
            }
        }
        else if(!is_null($query1)){
            $products = Product::where('owner_id',$query1)->get();
            if(sizeof($products) != 0){
                foreach($products as $product){
                    $product->image;
                }
                return response()->json($products,status:200);
            }
            else{
                return response()->json(['message' => 'this owner does not have any products'] ,status:404);
            }
        }
    }
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
}
