<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankAccount;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bankAccounts = BankAccount::all();

        return view('bank-accounts.index', [
            'data' => $bankAccounts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bank-accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'no' => 'required|numeric|unique:bank_accounts',
            'name_customer' => 'required',
            'name_bank' => 'required',
        ]);

        $validatedData['created_by'] = auth()->id();
        BankAccount::create($validatedData);

        return redirect()->route('bank-accounts.index')->with('success', 'Data berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        return view('bank-accounts.edit', [
            'data' => $bankAccount,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'no' => 'required|numeric|unique:bank_accounts,no,' . $id,
            'name_customer' => 'required',
            'name_bank' => 'required',
        ]);

        $bankAccount = BankAccount::findOrFail($id);
        $bankAccount->fill($validatedData);
        $bankAccount->updated_by = auth()->id();
        $bankAccount->save();

        return redirect()->route('bank-accounts.index')->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $bankAccount->deleted_by = auth()->id();
        $bankAccount->save();
        $bankAccount->delete();

        return redirect()->route('bank-accounts.index')->with('success', 'Data berhasil dihapus');
    }
}