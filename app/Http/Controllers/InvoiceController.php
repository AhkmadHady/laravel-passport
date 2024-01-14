<?php

namespace App\Http\Controllers;

use App\Models\InvoiceDetail;
use App\Models\InvoiceHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
class InvoiceController extends Controller
{
    //index
    public function index(Request $request)
    {
        try {
            $data = InvoiceHeader::with(['InvoiceDetail' => function ($query) {
                $query->select('id','invoice_id','id_items','price','quntity','total');
            }]);

            $data->with(['InvoiceDetail.Item' =>function ($query){
                $query->select('id','item_name','type','price','description');
            }]);

            $data->with(['Customer' => function ($query){
                $query->select('id','name','email','phone','address');
            }]);

            if ($request->invoice_id != '') {
                $data->where('invoice_id', $request->invoice_id);
            }

            if ($request->id_customer != '') {
                $data->where('id_customer', $request->id_customer);
            }

            if ($request->issue_date != '') {
                $data->where('issue_date', $request->issue_date);
            }

            if ($request->due_date != '') {
                $data->where('due_date', $request->due_date);
            }

            if ($request->subject != '') {
                $data->where('subject', $request->subject);
            }

            if ($request->id_item != '') {
                $data->whereHas('InvoiceDetail', function ($query) use ($request) {
                    $query->where('id_items', $request->id_item);
                });
            }

            $data->OrderBy('id','desc');

            if ($request->per_page !='') {
                $invoice = $data->paginate($request->per_page);
            }else{

                $invoice = $data->paginate(15);
            }

            return response()->json([
                'data'   => $invoice,
                'status' => 'success'
            ]);

            
        } catch (\Exception $e) {
            return response()->json(['message' => 'error', 'data' => $e->getMessage()]);
        } 
    }

    // view
    public function view($id) {
        try {
            $getData = InvoiceHeader::find($id);
            if ($getData) {
                $data = InvoiceHeader::with(['InvoiceDetail' => function ($query) {
                    $query->select('id','invoice_id','id_items','price','quntity','total');
                }]);
    
                $data->with(['InvoiceDetail.Item' =>function ($query){
                    $query->select('id','item_name','type','price','description');
                }]);
    
                $data->with(['Customer' => function ($query){
                    $query->select('id','name','email','phone','address');
                }]);
                
                $data->where('id', $id); 
                $data->first();
     
                return response()->json([
                    'data'   => $data,
                    'status' => 'success'
                ]);

            }else{
                
                return response()->json([
                    'data'   => '',
                    'status' => 'success'
                ]);
            }
             
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
                'invoice_id'    => 'required|max:50',
                'issue_date'    => 'required',
                'due_date'      => 'required',
                'subject'       => 'required',
                'total_item'    => 'required',
                'sub_total'     => 'required',
                'grand_total'   => 'required',
                'id_customer'   => 'required',
            );

            $validator = Validator::make($request->all(), $validasi);

            if ($validator->fails()) {
                return Response::Json(array('errors' => $validator->getMessageBag()->toArray()));
            } 
                $total_item   = preg_replace("/[^0-9]/", "", $request->total_item);
                $sub_total    = preg_replace("/[^0-9]/", "", $request->sub_total);
                $grand_total  = preg_replace("/[^0-9]/", "", $request->grand_total);

                $invoice = InvoiceHeader::create([
                    'invoice_id'    => $request->invoice_id,
                    'issue_date'    => $request->issue_date,
                    'due_date'      => $request->due_date,
                    'subject'       => $request->subject,
                    'total_item'    => $total_item,
                    'total_item'    => $sub_total,
                    'grand_total'   => $grand_total,
                    'id_customer'   => $request->id_customer,
                    'created_by'    => Auth::user()->id,
                ]);

                foreach ($request->id_item as $key => $value) {
                     
                    $price    = preg_replace("/[^0-9]/", "", $request->price[$key]);
                    $quntity  = preg_replace("/[^0-9]/", "", $request->quntity[$key]);
                    $total    = preg_replace("/[^0-9]/", "", $request->total[$key]); 
                    
                    InvoiceDetail::create([
                        'invoice_id'    => $request->invoice_id,
                        'id_items'      => $request->id_items[$key],
                        'price'         => $price,
                        'quntity'       => $quntity,
                        'total'         => $total,
                        'created_by'    => Auth::user()->id,
                    ]);
                }

            
            DB::commit();

            if ($invoice) {

                return response()->json([
                    'message' => 'Data invoice berhasil disimpan',
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
                'invoice_id'    => 'required|max:50',
                'issue_date'    => 'required',
                'due_date'      => 'required',
                'subject'       => 'required',
                'total_item'    => 'required',
                'sub_total'     => 'required',
                'grand_total'   => 'required',
                'id_customer'   => 'required',
            );

            $validator = Validator::make($request->all(), $validasi);

            if ($validator->fails()) {
                return Response::Json(array('errors' => $validator->getMessageBag()->toArray()));
            } 
                $total_item   = preg_replace("/[^0-9]/", "", $request->total_item);
                $sub_total    = preg_replace("/[^0-9]/", "", $request->sub_total);
                $grand_total  = preg_replace("/[^0-9]/", "", $request->grand_total);

                $invoice = InvoiceHeader::where('invoice_id', $request->invoice_id)->update([
                    'issue_date'    => $request->issue_date,
                    'due_date'      => $request->due_date,
                    'subject'       => $request->subject,
                    'total_item'    => $total_item,
                    'sub_total'     => $sub_total,
                    'grand_total'   => $grand_total,
                    'id_customer'   => $request->id_customer,
                    'created_by'    => Auth::user()->id,
                ]);

                foreach ($request->id_item as $key => $value) {

                    // hitung total
                    $price    = preg_replace("/[^0-9]/", "", $request->price[$key]);
                    $quntity  = preg_replace("/[^0-9]/", "", $request->quntity[$key]);
                    $total    = preg_replace("/[^0-9]/", "", $request->total[$key]); 

                    InvoiceDetail::where('invoice_id', $request->invoice_id)->update([
                        'id_items'      => $request->id_items[$key],
                        'price'         => $price,
                        'quntity'       => $quntity,
                        'total'         => $total,
                        'created_by'    => Auth::user()->id,
                    ]);
                }
  
            DB::commit();

            if ($invoice) {

                return response()->json([
                    'message' => 'Data invoice berhasil diupdate',
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
        DB::beginTransaction();
        try {
            
            $dataInvoice = InvoiceHeader::find($request->id_invoice);
            if ($dataInvoice) {
                $invoice = InvoiceHeader::where('id', $request->id_invoice)->delete();
                $invoice_detail = InvoiceDetail::where('invoice_id', $dataInvoice->invoice_id)->delete();

                DB::commit();
                if ($invoice) {

                    return response()->json([
                        'message' => 'Data invoice berhasil dihapus',
                        'status'  => 'success'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Something went wrong',
                        'status'  => 'error'
                    ]);
                }
            }else{
                return response()->json([
                    'message' => 'Data invoice gagal dihapus',
                    'status'  => 'error'
                ]);
            }
             
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
