<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
class ItemController extends Controller
{
    //Index
    public function index(Request $request)
    {
        try {
            $data = Item::select('item_name','type','price','description','id')->OrderBy('id', 'DESC');
            if ($request->pencarian != '') {
                $data->when($request->pencarian, function ($q) use ($request) {
                    $q->where('item_name', 'like', "%{$request->pencarian}%");
                    $q->orwhere('price', 'like', "%{$request->pencarian}%");
                    $q->orwhere('description', 'like', "%{$request->pencarian}%");  
                });
            }

            if (!empty($request->input('field'))) {
                $data->orderBy($request->input('field'), $request->input('sort'));
            } else {
                $data->orderBy('id', 'DESC');
            }
            
            $items = $data->paginate($request->per_page ?? 15);
            
            return response()->json([
                'data'   => $items,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'error', 'data' => $e->getMessage()]);
        } 
    } 

    // view
    public function view($id)
    {
        $dataItem = Item::find($id);

        return response()->json([
            'data'   => $dataItem,
            'status' => 'success'
        ]);
    }

    // save
    public function save(Request $request)
    {
        DB::beginTransaction();

        try {

            $validasi = array(
                'item_name'   => 'required|max:225',
                'type'        => 'required|max:100',
                'price'       => 'required',
                'description' => 'required',
            );

            $validator = Validator::make($request->all(), $validasi);

            if ($validator->fails()) {
                return Response::Json(array('errors' => $validator->getMessageBag()->toArray()));
            } 
                $price  = preg_replace("/[^0-9]/", "", $request->price);

                $items = Item::create([
                    'item_name'   => $request->item_name,
                    'type'        => $request->type,
                    'price'       => $price,
                    'description' => $request->description,
                    'created_by'  => Auth::user()->id,
                ]);
            
            DB::commit();

            if ($items) {

                return response()->json([
                    'message' => 'Data items berhasil disimpan',
                    'status'  => 'success'
                ]);
            } else {
                return response()->json([
                    'message' => 'Something went wrong',
                    'status'  => 'error'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // update
    public function update(Request $request)
    {
        
        DB::beginTransaction();

        try { 

            $validasi = array( 
                'item_name'   => 'required|max:225',
                'type'        => 'required|max:100',
                'price'       => 'required',
                'description' => 'required',
            );


            $validator = Validator::make($request->all(), $validasi);

            if ($validator->fails()) {
                return Response::Json(array('errors' => $validator->getMessageBag()->toArray()));
            } 
                $price  = preg_replace("/[^0-9]/", "", $request->price);

                $item = Item::where('id', $request->id_item)->update([
                    'item_name'   => $request->item_name,
                    'type'        => $request->type,
                    'price'       => $price,
                    'description' => $request->description,
                    'updated_by'    => Auth::user()->id,
                ]);
           

            DB::commit();

            if ($item) {

                return response()->json([
                    'message' => 'Data item berhasil diupdate',
                    'status'  => 'success'
                ]);
            } else {

                return response()->json([
                    'message' => 'Something went wrong',
                    'status'  => 'error'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } 

    // delete   
    public function delete(Request $request)
    {
        $item = Item::where('id', $request->id_item)->delete();

        if ($item) {

            return response()->json([
                'message' => 'Data item berhasil dihapus',
                'status'  => 'success'
            ]);
            
        } else {

            return response()->json([
                'message' => 'Something went wrong',
                'status'  => 'error'
            ]);
        }
    }
}
