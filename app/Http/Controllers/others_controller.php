<?php

namespace App\Http\Controllers;
use App\Models\Image;
use App\Models\Color;
use App\Models\Size;
use App\Models\Area;
use App\Models\User;
use App\Models\UserExtra;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class others_controller extends Controller
{
    public function get_sizes(request $request)
    {
        $s = Size::distinct()->get('size')->toArray();
        $s=Arr::flatten($s);
        return response()->json($s,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function get_colors(request $request)
    {
        $c = Color::distinct()->get('color')->toArray();
        $c=Arr::flatten($c);
        return response()->json($c,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function get_areas(request $request)
    {
        $areas=Area::all();
        foreach ($areas as $a) {
            $response[]=([
                'area'=>$a
            ]);
        }
        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function add_images(request $request)
    {
        $validator= Validator::make($request->all(),[
            'images' => 'required',
            'images.*' => 'max:10000|mimes:jpeg,png,jpg,gif,svg'

        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        else{

            $new_images=null;
            if($request->hasfile('images')){
                    //$images=json_decode($request['images'],true);
                    //foreach($request->input('images') as $image)
                    //foreach ($request->file['images'] as $image)
                    //foreach($request->allFiles('images') as $image)
                    //foreach($request->file('images') as $image)
                    //$imgs[]=null;
                    foreach($request->file('images') as $image)
                    {
                        $img=time()."-".$request->name."-".$image->extension();
                        $img=$image->store('upload','public');
                        $image->move(public_path('upload'),$img);

                        $new_image=Image::create([
                            'product_id'=>$request->product_id,
                            'image'=>$img,
                        ]);
                        $imgs[]=$new_image;
                    }
            }
            if(!isset($imgs)){return response()->json("empty!",404);}
            return response()->json($imgs,200);
        }
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function filter_area_markets($area_id)
        {
            $u=User::where('role_id',2)
                    ->join('user_extras','user_extras.user_id','=','users.id')
                    ->where('user_extras.accept',1)
                    ->where('area_id',$area_id)
                    ->select('users.*')
                    ->get();

            if($u->isEmpty()){return response()->json("this list is empty",404);}
            foreach ($u as $uu) {
                $response[]=([
                    $uu
                ]);
            }

            return response()->json($response,200);
        }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function filter_area_company($area_id)
        {
            $u=User::where('role_id',1)
                    ->join('user_extras','user_extras.user_id','=','users.id')
                    ->where('user_extras.accept',1)
                    ->where('area_id',$area_id)
                    ->select('users.*')
                    ->get();

            if($u->isEmpty()){return response()->json("this list is empty",404);}
            foreach ($u as $uu) {
                $response[]=([
                    $uu
                ]);
            }

            return response()->json($response,200);
        }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
}
