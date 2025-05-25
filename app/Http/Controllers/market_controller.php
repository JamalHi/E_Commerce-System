<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Color;
use App\Models\Size;
use App\Models\Product;
use App\Models\User;
use App\Models\UserExtra;
use App\Models\OrderItem;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\Payment;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;


class market_controller extends Controller
{
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function show_client_orders(request $request)
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

        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function show_company_products(request $request)
    {
        $ok=UserExtra::where('user_id',Auth::id())->first();
        if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}

        $users=User::where('role_id',1)->get();
        foreach ($users as $user) {
            $products=Product::where('owner_id',$user->id)->get();
            //if($products->isEmpty()){return response()->json("this list is empty!",404);}
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
        }
        if(!isset($response)){return response()->json("this list is empty!",200);}
        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function filter_company_products(request $request)
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
            /*
            $users=User::where('role_id',1)->get();
            foreach ($users as $u) {
            $products=Product::where('owner_id',$u->id);
            */

            $products=DB::table('products')
            ->join('users','users.id','=','products.owner_id')
            ->where('users.role_id',1)
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
            }
        //~~~~~~~~~~~~~~~~~~~~done~~~~~~~~~~~~~~~~~~~~//

        if(!isset($response)){ return response()->json("this list is empty!",200); }
            return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function search_company_products(request $request)
    {
        if(!isset($request->search))
        {
            return $this->show_company_products($request);
        }
        else
        {
            $ok=UserExtra::where('user_id',Auth::id())->first();
            if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}

            $users=User::where('role_id',1)->get();
            foreach ($users as $u) {
                $products=Product::where('owner_id',$u->id);
                $products= $products->where('name','like','%'.$request->search.'%')->get();
                //if($products->isEmpty()){return response()->json("this list is empty",404);}
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
            if(!isset($response)){return response()->json("this list is empty",200);}
            return response()->json($response,200);
        }
        return response()->json("no selection has been made",400);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function show_comps(request $request)
    {
        $comps=User::where('role_id',1)->get();
        if($comps->isEmpty()){return response()->json("this list is empty",200);}
        foreach ($comps as $c)
        {
            $response[]=([
                'comp' => $c,
            ]);
        }

        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function show_my_orders(request $request)
    {
        $ok=UserExtra::where('user_id',Auth::id())->first();
        if($ok->accept==0){return response()->json('waiting for Admins to accept your request ^_^ ',401);}


        $o=Order::where('by_id',Auth::id())->get();
        if($o->isEmpty()){ return response()->json("this list is empty!",200); }

        foreach ($o as $record)
            {
                $oi=OrderItem::where('order_id',$record->id)->get();
                foreach ($oi as $value)
                {
                    $d=Carbon::parse($value->created_at);
                    $response[]=([
                        "from"=>User::where('id',$value->from_id)->first()->name,
                        "date"=>$d->toDateString(),
                        //"location"=>$value->location,
                        "order"=>$record
                    ]);
                }
            }
        if(!isset($response)){return response()->json("this list is empty",200);}
        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function show_order_details(request $request)
    {
        $o=Order::where('id',$request->order_id)->first();
        $d=Carbon::parse($o->created_at);
        $oi=OrderItem::where('order_id',$o->id)->get();
        foreach ($oi as $value)
        {
            $p=Product::where('id',$value->product_id)->first();
            $u=User::where('id',$value->from_id)->first();
            $ps[]=([
                'product_id' => $p->id,
                "from"=>$u->name,
                'name' => $p->name,
                'size' => $value->size,
                'color' => $value->color,
                'quant' => $value->quant,
                'gender' => $value->gender,

            ]);
        }

        $response[]=([
            "date"=>$d->toDateString(),
            "location"=>$o->location,
            "products" => $ps
            //"profit"=>
        ]);
        return response()->json($response,200);
    }

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function market_order(Request $request)
    {
            $validator = validator::make($request->all(),[
                'location' => 'required',
            ]);
            if($validator->fails()){
                return response()->json([$validator->errors()->all(),'status' => 400,'message' => ':(']);
            }
            $location = $request->location;

            //iteration to make sure that the order is possible
            $total=0;
            //$go = true;
            //$orderItem = json_decode($request['orderItems'],true);
            foreach(/*$orderItem*/$request->orderItems as $k )
            {
                $product = Product::query()->where('id',$k['id_p'])->first();
                $total=$total+($product->price*$k['quant']);

                //increase price with each item

                $c=Color::where('product_id',$product->id)
                        ->where('color',$k['color'])
                        ->get();
                foreach ($c as $cc)
                {
                    $s=Size::where('color_id',$cc->id)
                            ->where('size',$k['size'])
                            ->get();
                    foreach ($s as $ss)
                    {
                        if($k['quant']>$ss['quant'])
                        {
                            return response()->json("not enough products!",404);
                        }
                        //else{

                        $ss->quant=$ss->quant-$k['quant'];
                        $ss->save();
                    }
                }

                $u=Payment::where('user_id',Auth::id())->first();
                if($u->wallet<$total)
                {
                    return response()->json('not enough credit',401);
                }
            }

            //the order is possible

                $order = Order::query()->create([
                    'by_id' => Auth::id(),
                    'location' => $location,
                ]);

                $order_id=$order->id;
                $full['order']=$order;
                $i=0;

                //creat order items
                foreach(/*$orderItem */$request->orderItems as $k )
                {
                    $product = Product::query()->where('id',$k['id_p'])->first();
                    $owner_id=$product->owner_id;
                    $order_item = OrderItem::query()->create([
                        'size' => $k['size'],
                        'order_id' => $order_id,
                        'product_id' => $k['id_p'],
                        'from_id' => $owner_id,
                        'color' => $k['color'],
                        'quant' => $k['quant'],
                    ]);

                    //add payment to owner
                    $o=Payment::where('user_id',$owner_id)->first();
                    $o->wallet=$o->wallet+($product->price*$k['quant']);
                    $o->save();

                    //subtract payment from buyer
                    $buyer=Payment::where('user_id',Auth::id())->first();
                    $buyer->wallet=$buyer->wallet-($product->price*$k['quant']);
                    $buyer->save();

                    //increase counter
                    $product->counter=$product->counter+1;
                    $product->save();

                    $full['orderItem'][$i]=$order_item;
                    $i++;
                }
        //send email
        $user = User::where('id',$product->owner_id)->first();
        $mailcontroller1 = resolve(MailController::class);
        $mailcontroller1->sendEmail("مرحباً ، لقد تم شراء أحد منتجاتك" , $user->email);

        return response()->json([$full,'status' => 200,'message'=>'order created seccesfully :)']);

    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function get_pros_of_one_comp(request $request)
    {
        $pros=Product::where('owner_id',$request->owner_id)->get();

        foreach ($pros as $ps) {
            $i=Image::where('product_id',$ps->id)->first();
            (new RateController)->num_of_rates($ps['id']);
            $response[]=([
                'product'=>$ps,
                'image'=>$i->image,
                'image_id'=>$i->id
            ]);
        }
        if(!isset($response)){return response()->json('this list is empty!',200);}
        return response()->json($response,200);
    }
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
