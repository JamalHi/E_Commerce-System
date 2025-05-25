<?php

namespace App\Http\Controllers;


use App\Models\Book;
use App\Models\Image;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Color;
use App\Models\Size;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\UserExtra;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

//use App\Models\Image;
//use App\Http\Controllers\DB;

class ClientController extends Controller
{
    //Order Api  - Post
    //URL : http://127.0.0.1:8000/api/order/3

    public function order(Request $request)
    {
        $validator = validator::make($request->all(),[
            'location' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([$validator->errors()->all(),'status' => 400,'message' => ':(']);
        }
        $location = /*json_decode($request['location'],true);*/$request->location;
        //iteration to make sure that the order is possible
        $total=0;
        //$go = true;
        $orderItem = json_decode($request['orderItems'],true);
       // if (is_array($orderItem) || is_object($orderItem)){
        foreach(/*$request->orderItems */$orderItem as $k )
        {
            $product = Product::query()->where('id',$k['id_p'])->first();

            $total=$total+($product->price*$k['quant']);
            //increase price with each item
           // $total=$total+$product->price;

            $c=Color::where('product_id',$product->id)
                    ->where('color',$k['color'])
                    ->get();
            foreach ($c as $cc) {
                $s=Size::where('color_id',$cc->id)
                        ->where('size',$k['size'])
                        ->get();
                foreach ($s as $ss) {
                    if($k['quant']>$ss['quant'])
                    {
                        return response()->json("not enough products!",404);
                    }
                    //else{

                        //$go=true;

                        $ss->quant=$ss->quant-$k['quant'];
                        $ss->save();
                    //}
                }
            }
            $u=Payment::where('user_id',Auth::id())->first();
            if($u->wallet<$total)
            {
                //$go=false;
                return response()->json('not enough credit',401);
            }
        }
    //}
      //  return response()->json([$total,'total',401]);

        //the order is possibleF
        //if($go==true)
        //{
            $order = Order::query()->create([
                'by_id' => Auth::id(),
                'location' => $location,
            ]);

            $order_id=$order->id;
            $full['order']=$order;
            $i=0;
            //creat order items
            foreach(/*$request->orderItems*/$orderItem as $k )
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
                   // 'gender' => 'male',
                ]);

                //add payment to owner
                $o=Payment::where('user_id',$owner_id)->first();
                $o->wallet=$o->wallet+($product->price*$k['quant']);
                //$o->wallet=$o->wallet+$total;
                $o->save();

                //subtract payment from buyer
                $buyer=Payment::where('user_id',Auth::id())->first();
                $buyer->wallet=$buyer->wallet-($product->price*$k['quant']);
               //$buyer->wallet=$buyer->wallet-$total;
                $buyer->save();
                //increase counter
                $product->counter=$product->counter+1;
                $product->save();

                $full['orderItem'][$i]=$order_item;
                $i++;
            }
//send email
        $deliveries = User::where('role_id',4)->get();
        $mailcontroller = resolve(MailController::class);
        foreach($deliveries as $k){
            $mailcontroller->sendEmail("مرحباً ، يوجد لديك طلب جديدr" , $k->email);
        }
//send email
        $user = User::where('id',$product->owner_id)->first();
        $mailcontroller1 = resolve(MailController::class);
        $mailcontroller1->sendEmail("مرحباً ، بقد تم شراء أحد منتجاتك" , $user->email);

            return response()->json([$full,'status' => 200,'message'=>'order created seccesfully :)']);
        //}
    }

    //My_Orders Api  - Get
    //URL : http://127.0.0.1:8000/api/show_my_orders
    public function show_my_orders()
    {
        $user=5;
        $my_orders = Order::query()->where('by_id',Auth::id())->get();
        $my_books = Book::query()->where('user_id',Auth::id())->get();
        if($my_orders->isEmpty() && $my_books->isEmpty()){
            return response()->json(['message' => 'there are no orders!!'],status:404);
        }

        $i=0;
        foreach ($my_orders as $k)
        {
          //  $my_orders[$i] = $k->OrderItem;
            $moi[$i] = OrderItem::query()->where('order_id',$k->id)->get();
            $my_ordersI = collect($moi);
            $my_ordersI = $my_ordersI->reject(function ($value, $key) {
                return count($value) === 0;
            });
            //$my_ordersI = array_filter( $my_ordersI );
            //$my_ordersI = array_map('array_filter', $my_ordersI);
            foreach($my_ordersI as $pp)
            {
                foreach($pp as $pp1)
                {
                    $pp1->product;
                }
            }
            $i++;
        }
        $full["orders"]=$my_orders;
        $full["ordersItem"]=$my_ordersI;
        //$full["product"]=$product;
        $full["books"]=$my_books;

        return response()->json(['Order&Books'=>$full , 'status' => 200,'message'=>'MY Orders :)']);
    }

    //Show_Products  Api  - Get
    //URL : http://127.0.0.1:8000/api/show_products
    /*
        public function old_show_products()
        {
            $markets = User::where('role_id',2)->get();
            //$products = Product::query()->where('owner_id',$markets->id())->get();
            foreach ($markets as $m) {
                $products = Product::query()->where('owner_id',$m->id)->get();
                $ps[]=$products;
            }

            return response()->json([$ps , 'status' => 200,'message'=>'All Products :)']);
        }
    */
   //ran edit
    public function show_products(request $request)
    {
        $products=Product::join('user_extras' , 'user_extras.user_id' , '=' , 'products.owner_id')
                            ->join('users','products.owner_id' , '=' , 'users.id')
                            ->where('user_extras.accept' , 1)
                            ->where('users.role_id',2)
                            ->select('products.*')
                            ->get();

        if($products->isEmpty()){return response()->json("this list is empty!",404);}
        foreach($products as $p)
        {
            $user = User::where('id',$p->owner_id)->first();
            $user->userExtra;

            $i=Image::where('product_id',$p->id)->get();
            foreach($i as $ii)
            {
                $images[]=([
                    "image"=>$ii->image,
                    "image_id"=>$ii->id,
                ]);
            }
            /*$response[]=([
                "product"=>$p,
                "images" => $images,
            ]);*/
            //$images=null;

            $c=Color::where('product_id',$p->id)->get();
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
                "product"=>$p,
                "colors_and_sizes"=>$colors_and_sizes,
                "images"=>$images,
                "owner"=>$user,
            ]);
            $images=null;
            $colors_and_sizes=null;
        }

        return response()->json($response,200);
    }

    //Search_Products  Api  - Get
    //URL : http://127.0.0.1:8000/api/search_products/
    /*
        public function oldsearch_products($name)
        {
        //  $result = Product::query()->where('name',$name)->get();
            $result = Product::where('name', 'LIKE', '%'.$name.'%')->get();
            return response()->json([$result , 'status' => 200,'message'=>'Result of search :)']);
        }
    */
    //ran edit
    public function search_products($name)
    {
        $users=User::join('user_extras','user_extras.user_id' , '=' , 'users.id')
                    ->select('users.*')
                    ->where('user_extras.accept' , 1)
                    ->where('role_id',2)
                    ->get();

        foreach ($users as $u) {
            $products=Product::where('owner_id',$u->id);
            $products= $products->where('name','like','%'.$name.'%')->get();

            /*if($products == null ){
                return response()->json(['message' => "there is no product with this name"]);
            }*/

            foreach($products as $p)
            {
                $i=Image::where('product_id',$p->id)->get();
                foreach($i as $ii)
                {
                    $images[]=([
                        "image"=>$ii->image,
                        "image_id"=>$ii->id,
                    ]);
                }
                $response[]=([
                    "product"=>$p,
                    "images" => $images,
                ]);
                $images=null;
            }
        }
        if(!isset($response)){return response()->json("this list is empty",404);}
        return response()->json($response,200);
    }

    //Product_details  Api  - Get
    //URL : http://127.0.0.1:8000/api/show_product_details/2
    /*
        public function old_show_product_details($id)
        {
            $Product_det = Product::where('id',$id)->first();
            $Product_det->image;
            return response()->json([$Product_det , 'status' => 200,'message'=>'Product details :)']);
        }
    */
    //ran edit
    public function show_product_details($id)
    {
        $pro=Product::where('id',$id)->first();
        $user = User::where('id',$pro->owner_id)->first();
        $user->userExtra;
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
            "colors_and_sizes"=>$colors_and_sizes,
            "images"=>$is,
            "owner"=>$user,
        ]);

        return response()->json($response,200);
    }

    //All Markets  Api  - Get
    //URL : http://127.0.0.1:8000/api/show_markets
    public function show_markets()
    {
        $users1 = UserExtra::join('users','users.id' , '=' , 'user_extras.user_id')
                            ->where('users.role_id',2)
                            ->where('accept',1)
                            ->get()
                            ;
        foreach($users1 as $user1){
            $user = User::where('id',$user1->user_id)->first();
            $response[]=([
                'user' => $user,
                'user_extra' => $user1,
            ]);
        }
        if(!isset($response)){return response()->json("this list is empty",404);}
        return response()->json([$response , 'status' => 200,'message'=>'All markets :)'],status : 200);
    }

    //Search_Markets  Api  - Get
    //URL : http://127.0.0.1:8000/api/search_markets/sh
    public function search_markets($name)
    {
        $result = User::join('user_extras','user_extras.user_id' , '=' , 'users.id')
                        ->select('users.*')
                        ->where('user_extras.accept' , 1)
                        ->where('role_id',2)
                        ->where('name', 'LIKE', '%'.$name.'%')
                        ->get();

        if($result->isEmpty()){
            return response()->json(['message' => 'this is empty!!'] , status:404);
        }
        return response()->json([$result , 'status' => 200,'message'=>'Result of search :)']);
    }

    //Market_details  Api  - Get
    //URL : http://127.0.0.1:8000/api/show_market_details/2
    public function show_market_details($id)
    {
        $market_det = UserExtra::where('id',$id)->first();
      //  $m=User::where('id',$market_det->user_id)->first();
        $market_det->image;
        return response()->json([$market_det ,'status' => 200,'message'=>'Product details :)']);
    }


    //All Orders  Api  - Get
    //URL : http://127.0.0.1:8000/api/show_orders
    public function show_orders()//////for delivery
    {
        $order=Order::join('users','orders.by_id','users.id')->where('users.role_id',3)->select('orders.*')->get();
        //query()->get();
        $order_item = OrderItem::query()->get();
        if(is_null($order)){
            return response()->json(['message' => 'there are no oreders!!'],status:404);
        }

        $i=0;
        $k=0;
        foreach($order as $o)
        {

            $total_price = 0;
            $oi = OrderItem::where('order_id' , $o->id)->get();
            foreach($oi as $oo){
                $product1 = Product::where('id',$oo->product_id)->first();
                $total_price =$total_price + ($oo->quant*$product1->price);
                //echo($oo->order->id  $total_price);
            }
            //$o->total_price= $total_price;
            $o->update([
                'total_price' => $total_price,
            ]);
            $o->save;

            $o->user;
            $order[$k]['user']=$o;
            $k++;
        }

        foreach ($order_item as $oi)
        {
                $order_item[$i]['market']=User::join('user_extras','user_extras.user_id' , '=' , 'users.id')
                                                ->select('users.*')
                                                ->where('role_id',2)
                                                ->where('users.id',$oi->from_id)->first();
                $i++;
        }

        $full["orders"]=$order;
        $full["orderItems"]=$order_item;
        return response()->json([$full , 'status' => 200,'message'=>'All Orders :)']);
    }

    ////////////////////////////////new for delivered
    public function isDelivered($id){
        $oi = Order::find($id);
        $oi->update(['isDelivered' => true]);
        $oi->save;
        return response()->json(['message' => 'the order is delevered'],status:200);
    }

    //Book  Api - POST
    //URL  : http://127.0.0.1:8000/api/book/2
    public function book($id_P,Request $request)
    {
        $validator = validator::make($request->all(),[
            'size'=>'required',
            'color'=>'required',
            'quant'=>'required',
            'gender'=>'required',
        ]);

        if($validator->fails()){
            return response()->json([$validator->errors()->all(),'status' => 400,'message' => ':(']);
        }

        $size=$request->size;
        $color=$request->color;
        $quant=$request->quant;
        $gender=$request->gender;
        $product = Product::query()->where('id',$id_P)->first();
        $owner_id=$product->owner_id;

        ////////////'////////////////////'///////////////////////////

        $c=Color::where('product_id',$product->id)
        ->where('color',$request->color)
        ->first();
      //  foreach ($c as $cc) {
            $s=Size::where('color_id',$c->id)
                ->where('size',$request->size)
                ->first();
           // foreach ($s as $ss) {
                if($request->quant>$s->quant)//['quant'])
                {
                    return response()->json("not enough products!",400);
                }
                else{
                    $u=Payment::where('user_id',Auth::id())->first();
                    $go=true;
                    if($u->wallet<($product->price*$request->quant)) { $go=false; return response()->json('not enough credit',400);}
                    $s->quant=$s->quant-$request->quant;
                    $s->save();
                    //the order is possible
                    if($go==true)
                    {
                        $book = Book::query()->create([
                            'user_id' => Auth::id(),
                            'product_id' => $id_P,
                            'from_id'=>$owner_id,
                            'size' => $size,
                            'color' => $color,
                            'quant' => $quant,
                            'gender' => $gender
                        ]);

                        //add payment to owner
                        $o=Payment::where('user_id',$owner_id)->first();
                        $o->wallet=$o->wallet+($product->price*$request->quant);
                        //$o->wallet=$o->wallet+$total;
                        $o->save();

                        //subtract payment from buyer
                        $buyer=Payment::where('user_id',Auth::id())->first();
                        $buyer->wallet=$buyer->wallet-($product->price*$request->quant);
                        //$buyer->wallet=$buyer->wallet-$total;
                        $buyer->save();
                        //increase counter
                        $product['counter']=$product->counter+1;
                        $product->save();

                        //send email
                        $user = User::where('id',$product->owner_id)->first();
                        $mailcontroller1 = resolve(MailController::class);
                        $mailcontroller1->sendEmail("hello ,you have a new book for your product" , $user->email);

                        return response()->json([$book,'status' => 200,'message'=>'Book created seccesfully :)']);
                    }
                }
            //}
        //}
        ////////////'//////////////////////'////////////////////////

    }

    //Company books  Api - GET
    //URL  : http://127.0.0.1:8000/api/show_books_company
    public function show_books_market()
    {
        $from=Auth::id();
        //$from=1;
        $my_books = Book::query()->where('from_id',$from)->get();
        return response()->json(['message' => 'there is no books'],status:404);
        return response()->json([$my_books , 'status' => 200,'message'=>'Books on your Products :)']);
    }

    //new
    public function markets_best_sales(request $request)
    {
        $products =Product::join('users', 'users.id', '=', 'products.owner_id')
                                ->where('users.role_id',2)
                                ->select('products.*')
                                ->orderBy('products.counter','desc')
                                ->take(5)
                                ->get();

        if($products->isEmpty()){return response()->json("this list is empty!",404);}
        foreach($products as $p)
        {
            $user = User::where('id',$p->owner_id)->first();
            $user->userExtra;

            $i=Image::where('product_id',$p->id)->get();
            foreach($i as $ii)
            {
                $images[]=([
                    "image"=>$ii->image,
                    "image_id"=>$ii->id,
                ]);
            }
            /*$response[]=([
                "product"=>$p,
                "images" => $images,
            ]);*/
            //$images=null;

            $c=Color::where('product_id',$p->id)->get();
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
                "product"=>$p,
                "colors_and_sizes"=>$colors_and_sizes,
                "images"=>$images,
                "owner"=>$user,
            ]);
            $images=null;
            $colors_and_sizes=null;
        }

        return response()->json($response,200);
    }

 //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function markets_top_rated(request $request)
    {
        //$users=User::where('role_id',2)->get();
        $products =Product::join('users', 'users.id', '=', 'products.owner_id')
                                ->where('users.role_id',2)
                                ->select('products.*')
                                ->orderBy('products.rate','desc')
                                ->take(5)
                                ->get();
        if($products->isEmpty()){return response()->json("this list is empty!",404);}

            foreach($products as $p)
            {
                $user = User::where('id',$p->owner_id)->first();
                $user->userExtra;
                $i=Image::where('product_id',$p->id)->get();
                foreach($i as $ii)
                {
                    $images[]=([
                        "image"=>$ii->image,
                        "image_id"=>$ii->id,
                    ]);
                }
                /*$response[]=([
                    "product"=>$p,
                    "images" => $images,
                ]);
                $images=null;*/

                $c=Color::where('product_id',$p->id)->get();
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
                    "product"=>$p,
                    "colors_and_sizes"=>$colors_and_sizes,
                    "images"=>$images,
                    "owner"=>$user,
                ]);
                $images=null;
                $colors_and_sizes=null;
            }

        return response()->json($response,200);
    }
 //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function filter_markets_products(request $request)
    {

        $f=$request->filter_by;
        $s=$request->sort;
        if($s==null){ $s="asc"; }
        //~~~~~~~~~~~~~~~~~~~~get products~~~~~~~~~~~~~~~~~~~~//
            /*
            $users=User::where('role_id',1)->get();
            foreach ($users as $u) {
            $products=Product::where('owner_id',$u->id);
            */

            $products=DB::table('products')
                        ->join('users','users.id','=','products.owner_id')
                        ->where('users.role_id',2)
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
                if($products->isEmpty()){ return response()->json("this list is empty!",404);}
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
                if($products->isEmpty()){ return response()->json("this list is empty!",404);}
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

                if($products->isEmpty()){ return response()->json("this list is empty!",404);}
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
                if($products->isEmpty()){ return response()->json("this list is empty!",404);}
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

    if(!isset($response)){ return response()->json("this list is empty!",404); }
            return response()->json($response,200);
    }
//new
    public function show_market_products(request $request , $id)
    {
        //$pros=Product::where('owner_id',$id)->get();

        $users=User::where('role_id',2)->where('id',$id)->get();
        if($users->isEmpty()){return response()->json("you entered a wronge id !",404);}

        foreach ($users as $user) {
            $products=Product::where('owner_id',$user->id)->get();
        }

        if($products->isEmpty()){return response()->json("this list is empty!",404);}
        foreach($products as $p)
        {
            $user = User::where('id',$p->owner_id)->first();
            $user->userExtra;

            $i=Image::where('product_id',$p->id)->get();
            foreach($i as $ii)
            {
                $images[]=([
                    "image"=>$ii->image,
                    "image_id"=>$ii->id,
                ]);
            }
            /*$response[]=([
                "product"=>$p,
                "images" => $images,
            ]);*/
            //$images=null;

            $c=Color::where('product_id',$p->id)->get();
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
                "product"=>$p,
                "colors_and_sizes"=>$colors_and_sizes,
                "images"=>$images,
                "owner"=>$user,
            ]);
            $images=null;
            $colors_and_sizes=null;
        }

        return response()->json($response,200);
    }

}
