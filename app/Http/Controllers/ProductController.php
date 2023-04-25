<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use GuzzleHttp\Handler\Proxy;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * I will add the products in product table
     */
    public function addProduct(Request $req)
    {
        $product = new Product();
        $product->name = $req->input('name');
        $product->price = $req->input('price');
        $product->description = $req->input('description');
        $product->category = $req->input('category');
        $product->gallery = $req->file('gallery')->store('products');
        $product->save();

        return response()->json(200);
    }

    /**
     * I will show the products
     */

    public function list()
    {
        return Product::all();
    }

    public function delete($id)
    {
        $del_product = Product::where('id', $id)->delete();
        if ($del_product) {
            return ["result" => "Product has been deleted"];
        } else {
            return ["result" => "Product has not been deleted"];
        }
    }

    public function getProduct($id)
    {

        return Product::find($id);
    }

    public function updateProduct($id, Request $req)
    {

        $product = Product::find($id);
        if (!$product) {
            $response =  [
                'status' => 0,
                'message' => 'Product not found'
            ];

            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try{
                $product->name = $req->input('name');
                $product->price = $req->input('price');
                $product->description = $req->input('description');
                $product->category = $req->input('category');
                if ($req->file('gallery')) {
                    $product->gallery = $req->file('gallery')->store('products');
                }
                $product->save();
                DB::commit();
            }

            catch(\Exception $err)
            {
                DB::rollBack();
                $product = null;
            }

            if(is_null($product))
            {
                $response =[
                    'status' => 0,
                    'message' => 'internet server error'
                ];

                $responseCode = 500;
            }
            else
            {
                $response =[
                    'status' => 1,
                    'message' => 'Product updated successfully'
                ];

                $responseCode = 200;
            }
           
        }

        return response()->json($response,$responseCode);
    }
}
