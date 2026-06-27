<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction\Transaction;
use App\Utils\TransactionUtil;

class ExpenseController extends Controller
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }

    public function index(Request $request)
    {
        $businessId = session('current_business_id');
        $expenses = Transaction::with(['contact'])
            ->where('business_id', $businessId)
            ->where('type', 'expense')
            ->latest()
            ->paginate(20);

        return view('expense.index', compact('expenses'));
    }

    public function create()
    {
        $businessId = session('current_business_id');
        $categories = DB::table('expense_categories')->where('business_id', $businessId)->get();
        $locations = \App\Models\Business\BusinessLocation::where('business_id', $businessId)->get();

        return view('expense.create', compact('categories', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
        ]);

        $transaction = $this->transactionUtil->createExpense($request, session('current_business_id'));

        if ($request->input('paid_amount', 0) > 0) {
            \App\Models\Transaction\TransactionPayment::create([
                'transaction_id' => $transaction->id,
                'amount' => $request->paid_amount,
                'method' => $request->payment_method ?? 'cash',
            ]);
        }

        return redirect()->route('expenses.index')->with('success', 'Expense recorded!');
    }

    public function show($id)
    {
        $expense = Transaction::findOrFail($id);
        return view('expense.show', compact('expense'));
    }

    public function destroy($id)
    {
        Transaction::findOrFail($id)->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted!');
    }
}
