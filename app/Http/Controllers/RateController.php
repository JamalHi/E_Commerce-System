<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RateController extends Controller
{
    //Add_Rate  Api  - Post
    //URL : http://127.0.0.1:8000/api/add_rate/1/4
    public function add_rate($id_p,$Num){
        $rate2=null;
        $product = Product::query()->find($id_p);
        if(is_null($product))
        return response()->json(['message' => 'not found','status' => 404]);
        $ratesArray = $product->rates()->get();
        foreach($ratesArray as $k){
            if($k->user_id == Auth::id()){
                $rate2=$k;
                break;
            }
        }
        if($rate2 != null){
            if($rate2->user_id == Auth::id())
            {
                $rate2->update([
                    'user_id' => Auth::id(),
                    'product_id' => $id_p,
                    'num_of_stars'=>$Num
                ]);
                $rate2->save;
            }

            return response()->json(['message' => ' you updated the rates','status' => 200]);
        }
        if($rate2==null){
            Rate::query()->create([
                'user_id' => Auth::id(),
                'product_id' => $id_p,
                'num_of_stars'=>$Num
            ]);
            return response()->json(['message' => 'Rate added successfully','status' => 200]);
        }
    }

    //Show_Rate  Api  - Post
    //URL : http://127.0.0.1:8000/api/num_of_rates/1
    public function num_of_rates($id){
        $sum=0;
        $Final=0;
        $product = Product::query()->find($id);
        $likesArray = $product->rates()->get();
        $n=sizeof($likesArray);
        for($i=0;$i<$n;$i++)
        {
            $sum+=$likesArray[$i]->num_of_stars;
        }
        if(sizeof($likesArray) != 0)
        {
            $Final=$sum/$n;
            $product['rate']=$Final;
            $product->update([
                'rate' => $Final,
            ]);
            $product->save;
        }
        return response()->json(['Rate'=>$Final,'status' => 200,'message'=>'Rate on this product']);
    }

}
