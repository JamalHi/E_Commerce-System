<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Product;
use App\Models\User;
use App\Models\UserExtra;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Color;
use App\Models\Size;
use App\Models\Area;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class company_controller extends Controller
{
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function add_product(request $request)
    {
        $validator= Validator::make($request->all(),[
            'name'=>'required|max:191|string',
            'price'=>'required',
            'category'=>'required',
            'gender'=>'required',
            'desc'=>'required',
            'color'=>'required',
            'size' =>'required',
            'quant'=>'required'
        ],[
            //'name.required'=>'write your product name plaese',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }
        else{

            $ok=UserExtra::where('user_id',Auth::id())->first();
            if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}

            $new_product=Product::create([
                'name'=>$request->input('name'),
                'size'=>$request->input('size'),
                'price'=>$request->input('price'),
                'description'=>$request->input('desc'),
                'owner_id'=>Auth::id(),
                'category'=>$request->category,
                'gender'=>$request->gender,
            ]);

            $new_product->rate=0;
            $new_product->counter=0;
            $new_product->save();

            $new_color = Color::create([
                'product_id' => $new_product->id,
                'color' => $request->color,
            ]);
            $new_size = Size::create([
                'size' => $request->size,
                'color_id' => $new_color->id,
                'quant' => $request->quant,
            ]);
            /*
                //postman body
                "color":[
                {
                            "color_name": "red",
                            "size":
                            [
                                {"sz":"XL","quant":10},
                                {"sz":"small","quant":10}
                            ]
                        },

                        {
                            "color_name": "blue",
                            "size":
                            [
                                {"sz":"L","quant":100},
                                {"sz":"S","quant":50}
                            ]
                        }
                ]
                //



                foreach ($request->color as $c) {
                    $validator= Validator::make($c,[ 'color_name'=>'required', 'size'=>'required' ]);
                    if($validator->fails()) { return response()->json($validator->errors(),400); }

                    $new_color = Color::create([
                        'product_id' => $new_product->id,
                        'color' => $c['color_name'],
                    ]);

                    foreach($c['size'] as $s){
                        $validator= Validator::make($s,[ 'sz'=>'required','quant'=>'required' ]);
                        if($validator->fails()){ return response()->json($validator->errors(),400); }
                        $new_size = Size::create([
                            'size' => $s['sz'],
                            'color_id' => $new_color->id,
                            'quant' => $s['quant'],
                        ]);

                        $ss[]=$new_size;
                    }

                    $cc[]=([
                        'color'=>$new_color,
                        'sizes'=>$ss
                    ]);
                    $ss=null;
                }
            */
        }

        $response[]=([
            //"product"=>$new_product,
            "id"=>$new_product->id,
            "name"=>$new_product->name,
            "price"=>(string)$new_product->price,
            "description"=>$new_product->description,
            "owner_id"=>$new_product->owner_id,
            "categort"=>$new_product->category,
            "gender"=>$new_product->gender,
            "color" =>$new_color->color,
            "size" => $new_size->size,
            "quant"=> (string)$new_size->quant,
        ]);
        //return response()->json($response,200);
        return response()->json([$response,'status'=>200,'message'=>'success']);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function update_product(request $request)
    {
        $ok=UserExtra::where('user_id',Auth::id())->first();
        if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}
        //not the owner
        $p=Product::where('id',$request->product_id)->first();
        if($p->owner_id!=Auth::id()){return response()->json('Forbidden.',403);}

        $validator= Validator::make($request->all(),[
            'name'=>'max:191|string',
            'size'=>'max:1000|string',
            'color'=>'max:1000|string',
            'old_images_ids.*'=>'integer',
            'new_images.*'=>'max:10000|mimes:jpeg,png,jpg,gif,svg'

        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }
        else
        {
            $pro=Product::where('id',$request->product_id)->first();
        //~~~~~~~~~~~~~~~~~~~~name~~~~~~~~~~~~~~~~~~~~//
            if($request->new_name!=null)
            {
                $pro->name=$request->new_name;
                $pro->save();
            }
        //~~~~~~~~~~~~~~~~~~~~description~~~~~~~~~~~~~~~~~~~~//
            if($request->new_desc!=null)
            {
                $pro->description=$request->new_desc;
                $pro->save();
            }

        //~~~~~~~~~~~~~~~~~~~~category~~~~~~~~~~~~~~~~~~~~//
            if($request->new_category!=null)
            {
                $pro->category=$request->new_category;
                $pro->save();
            }
        //~~~~~~~~~~~~~~~~~~~~price~~~~~~~~~~~~~~~~~~~~//
            if($request->new_price!=null)
            {
                $pro->price=$request->new_price;
                $pro->save();
            }
        //~~~~~~~~~~~~~~~~~~~~gender~~~~~~~~~~~~~~~~~~~~//
            if($request->new_gender!=null)
            {
                $pro->gender=$request->new_gender;
                $pro->save();
            }

        //~~~~~~~~~~~~~~~~~~~~color~~~~~~~~~~~~~~~~~~~~//
            if($request->color_id!=null)
            {
                $c=Color::where('id',$request->color_id)->first();
                $c->color=$request->new_color;
                $c->save();
            }
        //~~~~~~~~~~~~~~~~~~~~size~~~~~~~~~~~~~~~~~~~~//
            if($request->size_id!=null)
            {
                $s=Size::where('id',$request->size_id)->first();
                $s->size=$request->new_size;
                $s->save();
            }
        //~~~~~~~~~~~~~~~~~~~~quant~~~~~~~~~~~~~~~~~~~~//
            if($request->quant_id!=null)
            {
                $q=Size::where('id',$request->quant_id)->first();
                $q->quant=$request->new_quant;
                $q->save();
            }
        //~~~~~~~~~~~~~~~~~~~~images~~~~~~~~~~~~~~~~~~~~//[]
            $imgs[]=null;
            if($request->hasfile('new_images'))
            {
                foreach($request->old_images_ids as $id)
                {
                    $img=Image::where('id',$id)->first();

                    if(File::exists(public_path($img->image)))
                    {
                        File::delete(public_path($img->image));
                    }

                    Image::destroy($id);
                }

                foreach($request->file('new_images') as $image)
                {
                    $img=time()."-".$request->name."-".$image->extension();
                    $img=$image->store('upload','public');
                    $image->move(public_path('upload'),$img);

                    $new_image=Image::create([
                        'product_id'=>$pro->id,
                        'image'=>$img,
                    ]);
                    $imgs[]=$new_image;
                }

            }

        }
        return response()->json("Done successfuly.",200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function delete_product($id)
    {
        $ok=UserExtra::where('user_id',Auth::id())->first();
        if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}
        //not the owner
        $p = Product::find($id);
        //$p=Product::where('id',$request->product_id)->get();
        if($p->owner_id!=Auth::id()){return response()->json('Forbidden.',403);}

        $item=OrderItem::where('product_id',$id)->latest()->first();
        if(is_null($item)){
            Product::destroy($id);
            return response()->json('Product deleted successfully.',200);
        }
        $d1=$item->created_at;
        $d1_carbon=Carbon::parse($d1);

        $today=Carbon::now();
        $d2=$today->copy()->subMonth();
        $d3=$today->diffInDays($d1_carbon);

        if($d3>29)
        {
            Product::destroy($id);
            return response()->json('Product deleted successfully.',200);
        }
        else
        {
            return response()->json("can't delete because this product still has ongoing orders!",400);
        }


    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function show_my_products(request $request)
    {
        $ok=UserExtra::where('user_id',Auth::id())->first();
        if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}
        $products=Product::where('owner_id',Auth::id())->get();
        if($products->isEmpty()){return response()->json("this list is empty!",200);}

        foreach($products as $p)
        {
            $i=Image::where('product_id',$p->id)->first();
            (new RateController)->num_of_rates($p['id']);
            $response[]=([
                "product"=>$p,
                "image"=>$i->image,
                "image_id"=>$i->id,
            ]);
        }
        if(!isset($response)){return response()->json("this list is empty",200);}
        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function filter_my_products(request $request)
    {
        $ok=UserExtra::where('user_id',Auth::id())->first();
        if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}

        $f=$request->filter_by;
        $s=$request->sort;
        if($s==null){ $s="asc"; }
        //~~~~~~~~~~~~~~~~~~~~no filter no sort~~~~~~~~~~~~~~~~~~~~//
            if(!isset($f) && !isset($s))
            {
                return $this->show_company_products($request);
            }
        //~~~~~~~~~~~~~~~~~~~~get products~~~~~~~~~~~~~~~~~~~~//

            $products=DB::table('products')
            ->where('products.owner_id',Auth::id())
            ->select('products.*')
            ;

        //~~~~~~~~~~~~~~~~~~~~name~~~~~~~~~~~~~~~~~~~~//
            if($f=="name")
            {
                $products = $products   ->orderBy('products.name', $s)
                                        ->get();
                foreach($products as $p)
                {
                    $i=Image::where('product_id',$p->id)->first();

                    $response[]=([
                        "product"=>$p,
                        "image_id"=>$i->id,
                        "image"=>$i->image,
                    ]);
                }
            }
        //~~~~~~~~~~~~~~~~~~~~price~~~~~~~~~~~~~~~~~~~~//
            if($f=="price")
            {
                $products = $products   ->orderBy('products.price', $s)
                                        ->get();
                foreach($products as $p)
                    {
                        $i=Image::where('product_id',$p->id)->first();

                        $response[]=([
                            "product"=>$p,
                            "image_id"=>$i->id,
                            "image"=>$i->image,
                        ]);

                    }
            }
        //~~~~~~~~~~~~~~~~~~~~category~~~~~~~~~~~~~~~~~~~~//
            if($f=="clothes" || $f=="shoes" || $f=="fabrics")
            {
                $products = $products   ->where('category',$f)
                                        ->orderBy('products.category', $s)
                                        ->get();
                if($products->isEmpty()){ return response()->json("this list is empty!",200);}
                foreach($products as $p)
                    {
                        $i=Image::where('product_id',$p->id)->first();

                        $response[]=([
                            "product"=>$p,
                            "image_id"=>$i->id,
                            "image"=>$i->image,
                        ]);

                    }
            }

        //~~~~~~~~~~~~~~~~~~~~gender~~~~~~~~~~~~~~~~~~~~//
            if($f=="female" || $f=="male")
            {
                $products = $products   ->where('gender',$f)
                                        ->orderBy('products.gender', $s)
                                        ->get();
                if($products->isEmpty()){ return response()->json("this list is empty!",200);}
                foreach($products as $p)
                    {
                        $i=Image::where('product_id',$p->id)->first();

                        $response[]=([
                            "product"=>$p,
                            "image_id"=>$i->id,
                            "image"=>$i->image,
                        ]);
                    }
            }
        //~~~~~~~~~~~~~~~~~~~~rate~~~~~~~~~~~~~~~~~~~~//
            if($f=="rate")
            {
                $products = $products   ->orderBy('products.rate', $s)
                                        ->get();
                foreach($products as $p)
                    {
                        $i=Image::where('product_id',$p->id)->first();

                        $response[]=([
                            "product"=>$p,
                            "image_id"=>$i->id,
                            "image"=>$i->image,
                        ]);
                    }
            }
        //~~~~~~~~~~~~~~~~~~~~quant~~~~~~~~~~~~~~~~~~~~//
            if($f=="quant")
            {

                $products = $products   ->join('colors', 'colors.product_id', '=', 'products.id')
                                        ->join('sizes', 'sizes.color_id', '=', 'colors.id')
                                        ->select('products.id', DB::raw('SUM(sizes.quant) as quants'))
                                        ->groupBy('products.id')
                                        ->orderBy('quants',$s)
                                        ->get()
                ;
                foreach($products as $p)
                {
                    $i=Image::where('product_id',$p->id)->first();
                    $ps=Product::where('id',$p->id)->first();
                    $response[]=([
                        "quant"=>$p->quants,
                        "product"=>$ps,
                        "image_id"=>$i->id,
                        "image"=>$i->image,
                        ]);

                }
            }
        //~~~~~~~~~~~~~~~~~~~~color~~~~~~~~~~~~~~~~~~~~//
            if($f=="color")
            {
                $products = $products   ->join('colors', 'colors.product_id', '=', 'products.id')
                                        ->where('color','like','%'.$request->color.'%')
                                        ->select('products.id')
                                        ->groupBy('products.id')
                                        ->orderBy('color',$s)
                                        ->get()
                                        ;

                if($products->isEmpty()){ return response()->json("this list is empty!",200);}
                foreach($products as $p)
                {
                    $i=Image::where('product_id',$p->id)->first();
                    $ps=Product::where('id',$p->id)->first();
                    $response[]=([
                        "product"=>$ps,
                        "image_id"=>$i->id,
                        "image"=>$i->image,
                        ]);

                }
            }
        //~~~~~~~~~~~~~~~~~~~~size~~~~~~~~~~~~~~~~~~~~//
            if($f=="size")
            {
                $products = $products   ->join('colors', 'colors.product_id', '=', 'products.id')
                                        ->join('sizes', 'sizes.color_id', '=', 'colors.id')
                                        ->where('sizes.size',$request->size)
                                        ->select('products.id')
                                        ->groupBy('products.id')
                                        ->orderBy('products.name',$s)
                                        ->get()
                                        ;
                if($products->isEmpty()){ return response()->json("this list is empty!",200);}
                foreach($products as $p)
                {
                    $i=Image::where('product_id',$p->id)->first();
                    $ps=Product::where('id',$p->id)->first();
                    $response[]=([
                        "product"=>$ps,
                        "image_id"=>$i->id,
                        "image"=>$i->image,
                        ]);

                }


        //~~~~~~~~~~~~~~~~~~~~done~~~~~~~~~~~~~~~~~~~~//

        }
        if(!isset($response)){ return response()->json("this list is empty!",200); }
        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function search_my_products(request $request)
    {
        if(!isset($request->search))
        {
            return $this->show_my_products($request);
        }
        else
        {
            $ok=UserExtra::where('user_id',Auth::id())->first();
            if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}
            //$products=Product::where('owner_id',$request->user_id);
            $products=Product::where('owner_id',Auth::id());
            //$ids[]=null;
            $products= $products->where('name','like','%'.$request->search.'%')->get();
            if($products->isEmpty()){return response()->json("this list is empty",200);}
            foreach($products as $p)
            {
                $i=Image::where('product_id',$p->id)->first();

                $response[]=([
                    "product"=>$p,
                    "image_id"=>$i->id,
                    "image"=>$i->image,
                    ]);

            }
            if(!isset($response)){return response()->json("this list is empty",200);}
            return response()->json($response,200);
        }
        return response()->json("no selection has been made",400);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function show_product_details($id)
    {
        $pro = Product::find($id);
        //$pro=Product::where('id',$request->product_id)->first();
        $user = User::where('id',$pro->owner_id)->first();
        //$user->userExtra;
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
        /*
        $response['product']=$pro;
        $response['images']=$is;
        $response['colors_and_sizes']=$colors_and_sizes;
        $response['owner']=$user;
        */
        $response[]=([
            "product"=>$pro,
            "images"=>$is,
            "colors_and_sizes"=>$colors_and_sizes,
            "owner" => $user,
        ]);
        return response()->json([$response,'status'=>200,'message'=>'success']);
        //return response()->json($response,200);
    }

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function show_market_orders(request $request)
    {
        $ok=UserExtra::where('user_id',Auth::id())->first();
        if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}

        $oi=OrderItem::where('from_id',Auth::id())->get();
        if($oi->isEmpty()){ return response()->json("this list is empty!",200); }
        foreach ($oi as $item)
        {
            $o=Order::where('id',$item->order_id)->get();
            foreach ($o as  $value)
            {
                $u=User::where('id',$value->by_id)->first();
                $d=Carbon::parse($value->created_at);

                $response[]=([
                    "customer_name"=>$u->name,
                    "date"=>$d->toDateString(),
                    "location"=>$value->location,
                    //"profit"=>
                ]);
            }
        }
        if(!isset($response)){return response()->json("this list is empty",200);}
        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function my_best_sales(request $request)
    {
        $products=Product::where('owner_id',Auth::id())
                        ->orderBy('counter','desc')
                        ->take(5)
                        ->get();
        if($products->isEmpty()){return response()->json("this list is empty!",200);}

        foreach($products as $p)
        {
            $i=Image::where('product_id',$p->id)->first();
            (new RateController)->num_of_rates($p['id']);
            $response[]=([
                "product"=>$p,
                "image"=>$i->image,
                "image_id"=>$i->id,
            ]);
        }
        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function my_top_rated(request $request)
    {
        $products=Product::where('owner_id',Auth::id())
                        ->orderBy('rate','desc')
                        ->take(5)
                        ->get();
        if($products->isEmpty()){return response()->json("this list is empty!",200);}

        foreach($products as $p)
        {
            $i=Image::where('product_id',$p->id)->first();
            $response[]=([
                "product"=>$p,
                "image"=>$i->image,
                "image_id"=>$i->id,
            ]);
        }
        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~



}
