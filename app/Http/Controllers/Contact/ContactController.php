<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact\Contact;
use App\Utils\ContactUtil;

class ContactController extends Controller
{
    protected $contactUtil;

    public function __construct(ContactUtil $contactUtil)
    {
        $this->contactUtil = $contactUtil;
    }

    public function index(Request $request)
    {
        $businessId = session('current_business_id');

        $query = Contact::where('business_id', $businessId);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('mobile', 'like', "%{$request->search}%");
            });
        }

        $contacts = $query->latest()->paginate(20);

        return view('contact.index', compact('contacts'));
    }

    public function create()
    {
        $businessId = session('current_business_id');
        $customerGroups = \App\Models\Settings\CustomerGroup::where('business_id', $businessId)->get();

        return view('contact.create', compact('customerGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,supplier,both',
            'mobile' => 'required|string|max:20',
        ]);

        $contact = $this->contactUtil->createNewContact($request, session('current_business_id'));

        return redirect()->route('contacts.index')
            ->with('success', 'Contact created successfully!');
    }

    public function show($id)
    {
        $contact = Contact::with(['transactions'])->findOrFail($id);

        return view('contact.show', compact('contact'));
    }

    public function edit($id)
    {
        $contact = Contact::findOrFail($id);
        $customerGroups = \App\Models\Settings\CustomerGroup::where('business_id', session('current_business_id'))->get();

        return view('contact.edit', compact('contact', 'customerGroups'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
        ]);

        $contact = Contact::findOrFail($id);
        $contact->update($request->only([
            'name', 'email', 'mobile', 'phone', 'tax_number',
            'address', 'city', 'state', 'country', 'zip_code',
            'shipping_address', 'credit_limit', 'pay_term_number',
            'pay_term_type', 'customer_group_id', 'type',
        ]));

        return redirect()->route('contacts.index')
            ->with('success', 'Contact updated successfully!');
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contact deleted successfully!');
    }

    public function customers()
    {
        $businessId = session('current_business_id');
        $customers = Contact::where('business_id', $businessId)
            ->customers()
            ->latest()
            ->paginate(20);

        return view('contact.customers', compact('customers'));
    }

    public function suppliers()
    {
        $businessId = session('current_business_id');
        $suppliers = Contact::where('business_id', $businessId)
            ->suppliers()
            ->latest()
            ->paginate(20);

        return view('contact.suppliers', compact('suppliers'));
    }
}
