<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Billing;
use App\Http\Resources\Api\V1\BillingResource;
use Validator;
use Carbon\Carbon;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $start_date = $request->start_date ?? Carbon::now()->startOfMonth();
        $end_date = $request->end_date ?? Carbon::now();

        $user = auth()->user();

        if ($request->start_date && $request->end_date) {
            $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
            $end_date = Carbon::parse($request->end_date)->format('Y-m-d');
        }

        $billings = Billing::whereBetween('date', [$start_date, $end_date])
            ->where('user_id', $user->id)
            ->where('destination', 'visit')
            ->orWhere('destination', 'promise')
            ->orWhere('destination', 'pay')
            ->orderBy('destination', 'asc')
            ->orderBy('date', 'asc')
            ->get();

        if ($search) {
            $billings = Billing::with('customer', 'user')
            ->whereBetween('date', [$start_date, $end_date])
            ->where('user_id', $user->id)
            ->where('destination', 'like', '%' . $search . '%')
            ->orWhere('no_billing', 'like', '%' . $search . '%')
            ->orWhereHas('customer', function($q) use($search, $user, $start_date, $end_date) {
                $q->where('name_customer', 'like', '%' . $search . '%')
                    ->orWhere('no', 'like', '%' . $search . '%')
                    ->whereHas('billing', function($q) use($user, $start_date, $end_date) {
                        $q->whereBetween('date', [$start_date, $end_date])
                            ->where('user_id', $user->id);
                    });
            })
            ->orWhere('destination', 'like', '%' . $search . '%')
            ->orderBy('destination', 'asc')
            ->orderBy('date', 'asc')
            ->get();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully.',
            'data' => BillingResource::collection($billings)
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $billing = Billing::where('user_id', $user->id)->where('id', $id)->first();

        if (!$billing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully.',
            'data' => new BillingResource($billing)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        $billing = Billing::where('user_id', $user->id)->where('id', $id)->first();

        if (!$billing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'no_billing' => 'required|unique:billings,no_billing,' . $id,
            'date' => 'required|date',
            // 'bank_account_id' => 'required|exists:bank_accounts,id',
            // 'user_id' => 'required|exists:users,id',
            'destination' => 'required|in:visit,promise,pay',
            // 'image_visit' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_visit' => 'nullable',
            'description_visit' => 'nullable',
            'promise_date' => 'nullable',
            // 'image_promise' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_promise' => 'nullable',
            'description_promise' => 'nullable',
            'amount' => 'nullable',
            // 'image_amount' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_amount' => 'nullable',
            'description_amount' => 'nullable',
            // 'signature_officer' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'signature_customer' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'signature_officer' => 'nullable',
            'signature_customer' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        $validatedData['updated_by'] = $user->id;

        // save image to public/images/billings and change name to timestamp
        if ($request->hasFile('image_visit')) {
            // remove old image
            if ($billing->image_visit != null && file_exists(public_path('images/billings/' . $billing->image_visit))) {
                unlink(public_path('images/billings/' . $billing->image_visit));
            }

            // save image to public/images/billings and change name file to name user-timestamp
            $file = $request->file('image_visit');
            $fileName = $validatedData['no_billing'] . '-' . 'visit' . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/billings'), $fileName);
            $validatedData['image_visit'] = $fileName;
        }

        // save image to public/images/billings and change name to timestamp
        if ($request->hasFile('image_promise')) {
            // remove old image
            if ($billing->image_promise != null && file_exists(public_path('images/billings/' . $billing->image_promise))) {
                unlink(public_path('images/billings/' . $billing->image_promise));
            }

            // save image to public/images/billings and change name file to name user-timestamp
            $file = $request->file('image_promise');
            $fileName = $validatedData['no_billing'] . '-' . 'promise' . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/billings'), $fileName);
            $validatedData['image_promise'] = $fileName;
        }

        // save image to public/images/billings and change name to timestamp
        if ($request->hasFile('image_amount')) {
            // remove old image
            if ($billing->image_amount != null && file_exists(public_path('images/billings/' . $billing->image_amount))) {
                unlink(public_path('images/billings/' . $billing->image_amount));
            }

            // save image to public/images/billings and change name file to name user-timestamp
            $file = $request->file('image_amount');
            $fileName = $validatedData['no_billing'] . '-' . 'amount' . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/billings'), $fileName);
            $validatedData['image_amount'] = $fileName;
        }

        // save image to public/images/billings and change name to timestamp
        if ($request->hasFile('signature_officer')) {
            // remove old image
            if ($billing->signature_officer != null && file_exists(public_path('images/billings/' . $billing->signature_officer))) {
                unlink(public_path('images/billings/' . $billing->signature_officer));
            }

            // save image to public/images/billings and change name file to name user-timestamp
            $file = $request->file('signature_officer');
            $fileName = $validatedData['no_billing'] . '-' . 'signature_officer' . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/billings'), $fileName);
            $validatedData['signature_officer'] = $fileName;
        }

        // save image to public/images/billings and change name to timestamp
        if ($request->hasFile('signature_customer')) {
            // remove old image
            if ($billing->signature_customer != null && file_exists(public_path('images/billings/' . $billing->signature_customer))) {
                unlink(public_path('images/billings/' . $billing->signature_customer));
            }

            // save image to public/images/billings and change name file to name user-timestamp
            $file = $request->file('signature_customer');
            $fileName = $validatedData['no_billing'] . '-' . 'signature_customer' . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/billings'), $fileName);
            $validatedData['signature_customer'] = $fileName;
        }

        $validatedData['updated_by'] = auth()->id();
        // if ($request->destination == 'visit') {
        //     $validatedData['description_visit'] = null;
        //     $validatedData['promise_date'] = null;
        //     $validatedData['amount'] = null;
        //     if ($billing->image_amount != null && file_exists(public_path('images/billings/' . $billing->image_amount))) {
        //         unlink(public_path('images/billings/' . $billing->image_amount));
        //     }
        //     $validatedData['image_amount'] = null;
        //     if ($billing->siganture_officer != null && file_exists(public_path('images/billings/' . $billing->signature_officer))) {
        //         unlink(public_path('images/billings/' . $billing->signature_officer));
        //     }
        //     $validatedData['signature_officer'] = null;
        //     if ($billing->siganture_customer != null && file_exists(public_path('images/billings/' . $billing->signature_customer))) {
        //         unlink(public_path('images/billings/' . $billing->signature_customer));
        //     }
        //     $validatedData['signature_customer'] = null;
        // }

        $billing->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Data updated successfully.',
            'data' => new BillingResource($billing)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
