<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\others_controller;



use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\company_controller;
use App\Http\Controllers\market_controller;
use App\Http\Middleware\AccessControl;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
/*Route::middleware(['auth:api' , 'access.control'])->group(function(){

});*/

//Route::post('auth/sign-up',[AuthController::class,'createAccountCompany']);

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~Auth~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

Route::post('auth/sign-up/com',[AuthController::class,'createAccountCompany'])->name('createAccountCompany');

Route::post('auth/sign-up/mark',[AuthController::class,'createAccountMarket'])->name('createAccountMarket');

Route::post('auth/sign-up/client',[AuthController::class,'createAccountClient'])->name('createAccountClient');

Route::post('auth/sign-up/deliv',[AuthController::class,'createAccountDelivery'])->name('createAccountDelivery');

Route::post('auth/sign-up/admin',[AuthController::class,'createAccountAdmin'])->name('createAccountAdmin');

Route::post('auth/login',[AuthController::class,'login'])->name('login');

Route::post('auth/add/image/{id}',[AuthController::class,'addImage'])->name('addImage');//////////////////////////new

Route::post('auth/add/image/admin/{id}',[AuthController::class,'add_image_admin'])->name('addImageAdmin');////////////////////new

Route::middleware(['auth:api'])->group(function(){
    Route::post('auth/logout',[AuthController::class,'logout'])->name('logout');

    Route::patch('auth/update/{id}',[AuthController::class,'update'])->name('update');

    Route::get('auth/show/{id}',[AuthController::class,'show'])->name('show_user_details');
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~admin~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

Route::middleware(['auth:api','access.control' ])->group(function(){
    Route::get('admin/show/users/extra/',[AdminController::class,'show_users_extra'])->name('show_users_extra');

    Route::get('admin/show/users/',[AdminController::class,'show_users'])->name('show_users');

    Route::get('admin/search/user',[AdminController::class,'search_user'])->name('search_user');

    Route::get('admin/filter/users',[AdminController::class,'filter_user'])->name('filter_user');

    Route::delete('admin/delete/user/{id}',[AdminController::class,'delete_user'])->name('delete_user');

    Route::get('admin/show/join/request',[AdminController::class,'show_join_request'])->name('show_join_request');

    Route::post('admin/accept/deny/request/{id}',[AdminController::class,'accept_deny_request'])->name('accept_deny_request');

    Route::get('admin/show/all/products',[AdminController::class,'show_all_products'])->name('show_all_products');

    Route::delete('admin/delete/product/{id}',[AdminController::class,'delete_product'])->name('delete_product');

    Route::get('admin/search/product',[AdminController::class,'search_product'])->name('search_product');

    Route::get('admin/filter/product',[AdminController::class,'filter_product'])->name('filter_product');

    Route::get('admin/show/product/details/{id}',[AdminController::class,'show_product_details'])->name('show_product_details');

    Route::post('admin_add_credit',[AdminController::class,'admin_add_credit'])->name('admin_add_credit');
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~client~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

////////////////////////AMMAR
Route::middleware(['auth:api' ,'access.control'])->group(function(){
    Route::post('order',[ClientController::class,'order'])->name('order');//client

    Route::get('show_my_orders',[ClientController::class,'show_my_orders'])->name('show_my_orders');//client

    Route::post('book/{id}',[ClientController::class,'book'])->name('book');//client
 //   Route::get('show_books_company',[ClientController::class,'show_books_company'])->name('show_books_company');//for company

    Route::post('add_rate/{id}/{num}',[RateController::class,'add_rate'])->name('add_rate');//client

    Route::get('num_of_rates/{id}',[RateController::class,'num_of_rates'])->name('num_of_rates');//client (we need to edit this api)

});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~delivery~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

Route::middleware(['auth:api' , 'access.control' ])->group(function(){
    Route::get('show_orders',[ClientController::class,'show_orders'])->name('show_orders');//delivery

});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~without permission (for every user)~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

//Route::middleware(['auth:api'])->group(function(){
    Route::get('show_products',[ClientController::class,'show_products'])->name('show_products');

    Route::get('search_products/{name}',[ClientController::class,'search_products'])->name('search_products_client');

    Route::get('show_product_details/{id}',[ClientController::class,'show_product_details'])->name('show_product_details_client');

    Route::get('show_markets',[ClientController::class,'show_markets'])->name('show_markets');

    Route::get('search_markets/{name}',[ClientController::class,'search_markets'])->name('search_markets');

    Route::get('show_market_details/{id}',[ClientController::class,'show_market_details'])->name('show_market_details');

    //new

    Route::get('filter_area_markets/{area_id}',[others_controller::class,'filter_area_markets'])->name('filter_area_markets');

    Route::get('filter_area_company/{area_id}',[others_controller::class,'filter_area_company'])->name('filter_area_company');
    //new
    Route::get('show_market_products/{id}',[ClientController::class,'show_market_products'])->name('show_market_products');

    //////////////////////////////////////////////new
    Route::get('markets_best_sales',[ClientController::class,'markets_best_sales'])->name('markets_best_sales');

    Route::get('markets_top_rated',[ClientController::class,'markets_top_rated'])->name('markets_top_rated');

    // Route::get('get_pros_of_one_market',[ClientController::class,'get_pros_of_one_market'])->name('get_pros_of_one_market');

    Route::get('client_filter_markets_products',[ClientController::class,'filter_markets_products'])->name('filter_markets_products');

    //new
    Route::get('isDelivered/{id}',[ClientController::class,'isDelivered'])->name('isDelivered');
//});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~company~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

Route::middleware(['auth:api' , 'access.control' ])->group(function(){
    Route::post('comp_add_product',[company_controller::class,'add_product'])->name('add_product_com')/*->middleware('check_login')*/;

    Route::post('comp_update_product',[company_controller::class,'update_product'])->name('update_product_com')/*->middleware('check_owner')*/;

    Route::post('comp_delete_product/{id}',[company_controller::class,'delete_product'])->name('delete_product_com')/*->middleware('check_owner')*/;

    Route::get('comp_show_my_products',[company_controller::class,'show_my_products'])->name('show_my_products_com')/*->middleware('check_login')*/;

    Route::get('comp_filter_my_products',[company_controller::class,'filter_my_products'])->name('filter_my_products_com')/*->middleware('check_login')*/;

    Route::get('comp_search_my_products',[company_controller::class,'search_my_products'])->name('search_my_products_com')/*->middleware('check_login')*/;

    Route::get('comp_show_product_details/{id}',[company_controller::class,'show_product_details'])->name('show_product_details_com')/*->middleware('check_login')*/;

    Route::get('comp_show_market_orders',[company_controller::class,'show_market_orders'])->name('show_market_orders')/*->middleware('check_login')*/;
////////////////////////////new
    Route::get('comp_my_best_sales',[company_controller::class,'my_best_sales'])->name('my_best_sales_com');

    Route::get('comp_my_top_rated',[company_controller::class,'my_top_rated'])->name('my_top_rated_com');

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~market~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

    Route::post('market_add_product',[company_controller::class,'add_product'])->name('add_product_mark')/*->middleware('check_login')*/;

    Route::post('market_update_product',[company_controller::class,'update_product'])->name('update_product_mark')/*->middleware('check_owner')*/;

    Route::post('market_delete_product/{id}',[company_controller::class,'delete_product'])->name('delete_product_mark')/*->middleware('check_owner')*/;

    Route::get('market_show_my_products',[company_controller::class,'show_my_products'])->name('show_my_products_mark')/*->middleware('check_login')*/;

    Route::get('market_filter_my_products',[company_controller::class,'filter_my_products'])->name('filter_my_products_mark')/*->middleware('check_login')*/;

    Route::get('market_search_my_products',[company_controller::class,'search_my_products'])->name('search_my_products_mark')/*->middleware('check_login')*/;

    Route::get('market_show_product_details/{id}',[company_controller::class,'show_product_details'])->name('show_product_details_mark')/*->middleware('check_login')*/;

    Route::get('market_show_client_orders',[market_controller::class,'show_client_orders'])->name('show_client_orders')/*->middleware('check_login')*/;

    Route::get('market_show_company_products',[market_controller::class,'show_company_products'])->name('show_company_products')/*->middleware('check_login')*/;

    Route::get('market_filter_company_products',[market_controller::class,'filter_company_products'])->name('filter_company_products')/*->middleware('check_login')*/;

    Route::get('market_search_company_products',[market_controller::class,'search_company_products'])->name('search_company_products')/*->middleware('check_login')*/;

    Route::get('market_show_comps',[market_controller::class,'show_comps'])->name('show_comps')/*->middleware('check_login')*/;

    Route::get('market_show_my_orders',[market_controller::class,'show_my_orders'])->name('show_my_orders_mark')/*->middleware('check_login')*/;

    Route::get('market_show_order_details',[market_controller::class,'show_order_details'])->name('show_order_details')/*->middleware('check_login')*/;
    ////////////////////////////////////////////////new
    Route::get('market_my_best_sales',[company_controller::class,'my_best_sales'])->name('my_best_sales_mark');

    Route::get('market_my_top_rated',[company_controller::class,'my_top_rated'])->name('my_top_rated_mark');

    Route::get('get_pros_of_one_comp',[market_controller::class,'get_pros_of_one_comp'])->name('get_pros_of_one_comp');

    Route::post('market_order',[market_controller::class,'market_order'])->name('market_order');

    Route::get('show_books_market',[ClientController::class,'show_books_market'])->name('show_books_market');
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~without permission for developer~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

Route::post('add_images',[others_controller::class,'add_images']);
Route::get('get_areas',[others_controller::class,'get_areas']);
Route::get('get_colors',[others_controller::class,'get_colors']);
Route::get('get_sizes',[others_controller::class,'get_sizes']);


Route::post('test',[company_controller::class,'test']);



