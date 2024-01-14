<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    //Index
    public function index(Request $request)
    {
        try {
            $data = Customer::select('name','email','phone','address','id')->OrderBy('id', 'DESC');
            if ($request->pencarian != '') {
                $data->when($request->pencarian, function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->pencarian}%");
                    $q->orwhere('email', 'like', "%{$request->pencarian}%");
                    $q->orwhere('phone', 'like', "%{$request->pencarian}%"); 
                    $q->orwhere('address', 'like', "%{$request->pencarian}%"); 
                });
            }
            
            if (!empty($request->input('field'))) {
                $data->orderBy($request->input('field'), $request->input('sort'));
            } else {
                $data->orderBy('id', 'DESC');
            }
            
            $customer = $data->paginate($request->per_page ?? 15);
            return response()->json([
                'data'   => $customer,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'error', 'data' => $e->getMessage()]);
        } 
    }

    // view
    public function view($id)
    {
        $dataCustomer = Customer::find($id);

        return response()->json([
            'data'   => $dataCustomer,
            'status' => 'success'
        ]);
    }

    // all customer
    public function allCustomer(){
        try {
            $data = Customer::select('name','email','phone','address','id')->OrderBy('id','desc')->get();

            return response()->json([
                'data'   => $data,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'error', 'data' => $e->getMessage()]);
        } 
    }

    // save
    public function save(Request $request)
    {
        DB::beginTransaction();

        try {

            $validasi = array(
                'name'    => 'required|max:150|',
                'email'   => 'required|max:100|email|unique:customer',
                'phone'   => 'required|max:15',
                'address' => 'required',
            );

            $validator = Validator::make($request->all(), $validasi);

            if ($validator->fails()) {
                return Response::Json(array('errors' => $validator->getMessageBag()->toArray()));
            } 
 
                $customer = Customer::create([
                    'name'          => $request->name,
                    'email'         => $request->email,
                    'phone'         => $request->phone,
                    'address'       => $request->address,
                    'created_by'    => Auth::user()->id,
                ]);
            
            DB::commit();

            if ($customer) {

                return response()->json([
                    'message' => 'Data customer berhasil disimpan',
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
                'name'    => 'required|max:150|',
                'email'   => 'required|max:100|email|unique:customer,email,'.$request->id_customer,
                'phone'   => 'required|max:15',
                'address' => 'required',
            );


            $validator = Validator::make($request->all(), $validasi);

            if ($validator->fails()) {
                return Response::Json(array('errors' => $validator->getMessageBag()->toArray()));
            } 
 
                $customer = Customer::where('id', $request->id_customer)->update([
                    'name'          => $request->name,
                    'email'         => $request->email,
                    'phone'         => $request->phone,
                    'address'       => $request->address,
                    'updated_by'    => Auth::user()->id,
                ]);
           

            DB::commit();

            if ($customer) {

                return response()->json([
                    'message' => 'Data customer berhasil diupdate',
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
            return response()->json(['status' => 'error', 'message' => $e]);
        }
    } 

    // delete   
    public function delete(Request $request)
    {
        $customer = Customer::where('id', $request->id_customer)->delete();

        if ($customer) {

            return response()->json([
                'message' => 'Data customer berhasil dihapus',
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
