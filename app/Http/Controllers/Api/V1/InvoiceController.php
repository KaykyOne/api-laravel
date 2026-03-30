<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\InvoiceResource;
use App\Models\Invoice;
use App\Traits\HttpResponses;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class InvoiceController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // return InvoiceResource::collection(Invoice::where([
        //     ['value', '>', '5000'],
        //     ['paid', '=', '1']
        // ])->with('user')->get());
        //return InvoiceResource::collection(Invoice::with('user')->get());

        return (new Invoice())->filter($request);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'type' => 'required',
                'paid' => 'required | numeric | between:0,1',
                'payment_date' => 'nullable',
                'value' => 'required | numeric | between:1,999999.99',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation Error', $validator->errors(), 422);
            }
            $created = Invoice::create($validator->validated());

            if (!$created) {
                return $this->error('Failed to create invoice', ['error' => 'Failed to create invoice'], 400);
            }

            return $this->response('Invoice created successfully', new InvoiceResource($created->load('user')), 201);

        } catch (\Exception $e) {
            return $this->error('An error occurred while creating the invoice', ['error' => $e->getMessage()], 500);
        }


    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'type' => 'required | max:1',
                'paid' => 'required | numeric | between:0,1',
                'value' => 'required | numeric',
                'payment_date' => 'nullable',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation Error', $validator->errors(), 422);
            }

            if (!$invoice) {
                return $this->error('Invoice not found', ['error' => 'Invoice not found'], 404);
            }

            $updated = $invoice->update($validator->validated());

            if (!$updated) {
                return $this->error('Failed to update invoice', ['error' => 'Failed to update invoice'], 400);
            }

            return $this->response('Invoice updated successfully', new InvoiceResource($invoice->fresh()->load('user')), 200);
        } catch (\Exception $e) {
            return $this->error('An error occurred while validating the invoice data', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        try {
            if (!$invoice) {
                return $this->error('Invoice not found', ['error' => 'Invoice not found'], 404);
            }

            $deleted = $invoice->delete();

            if (!$deleted) {
                return $this->error('Failed to delete invoice', ['error' => 'Failed to delete invoice'], 400);
            }

            return $this->response('Invoice deleted successfully', [], 200);
        } catch (\Exception $e) {
            return $this->error('An error occurred while deleting the invoice', ['error' => $e->getMessage()], 500);
        }
    }
}
