<?php

namespace App\Http\Controllers\Scale;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScaleController extends Controller
{
    /**
     * List scales
     */
    public function index()
    {
        $businessId = session('current_business_id');
        $scales = DB::table('scales')
            ->where('business_id', $businessId)
            ->get();

        return view('scale.index', compact('scales'));
    }

    /**
     * Add scale
     */
    public function create()
    {
        return view('scale.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'connection_type' => 'required|in:serial,tcpip,usb,bluetooth',
        ]);

        $businessId = session('current_business_id');

        DB::table('scales')->insert([
            'business_id' => $businessId,
            'name' => $request->name,
            'model' => $request->model,
            'connection_type' => $request->connection_type,
            'port' => $request->port,
            'baud_rate' => $request->baud_rate ?? 9600,
            'ip_address' => $request->ip_address,
            'port_number' => $request->port_number ?? 5000,
            'protocol' => $request->protocol,
            'is_label_printer' => $request->has('is_label_printer'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('scales.index')
            ->with('success', 'Scale added!');
    }

    public function destroy($id)
    {
        DB::table('scales')->where('id', $id)->delete();

        return redirect()->route('scales.index')
            ->with('success', 'Scale deleted!');
    }

    /**
     * PLU Management
     */
    public function pluIndex()
    {
        $businessId = session('current_business_id');
        $plu = DB::table('scale_plu')
            ->join('products', 'scale_plu.product_id', '=', 'products.id')
            ->leftJoin('variations', 'scale_plu.variation_id', '=', 'variations.id')
            ->select('scale_plu.*', 'products.name as product_name', 'variations.name as variation_name')
            ->where('scale_plu.business_id', $businessId)
            ->latest()
            ->paginate(20);

        $products = DB::table('products')
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->get();

        return view('scale.plu_index', compact('plu', 'products'));
    }

    public function pluStore(Request $request)
    {
        $request->validate([
            'plu_code' => 'required|string|max:20',
            'product_id' => 'required|exists:products,id',
            'price_per_kg' => 'required|numeric|min:0',
        ]);

        $businessId = session('current_business_id');

        DB::table('scale_plu')->insert([
            'business_id' => $businessId,
            'plu_code' => $request->plu_code,
            'product_id' => $request->product_id,
            'variation_id' => $request->variation_id,
            'department' => $request->department ?? 1,
            'price_per_kg' => $request->price_per_kg,
            'tare_weight' => $request->tare_weight ?? 0,
            'unit' => $request->unit ?? 'kg',
            'label_name' => $request->label_name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('scales.plu')
            ->with('success', 'PLU added!');
    }

    public function pluDestroy($id)
    {
        DB::table('scale_plu')->where('id', $id)->delete();

        return redirect()->route('scales.plu')
            ->with('success', 'PLU deleted!');
    }

    /**
     * Get weight from scale (called by Node.js bridge)
     */
    public function getWeight(Request $request)
    {
        $scaleId = $request->input('scale_id');
        $scale = DB::table('scales')->where('id', $scaleId)->first();

        if (!$scale) {
            return response()->json(['error' => 'Scale not found'], 404);
        }

        // In production, this would communicate with the Node.js bridge
        // via WebSocket to get real-time weight
        return response()->json([
            'scale_id' => $scaleId,
            'weight' => 0,
            'unit' => 'kg',
            'status' => 'connected',
        ]);
    }

    /**
     * Send PLU to scale
     */
    public function sendPlu(Request $request)
    {
        $pluId = $request->input('plu_id');
        $scaleId = $request->input('scale_id');

        $plu = DB::table('scale_plu')->where('id', $pluId)->first();
        $scale = DB::table('scales')->where('id', $scaleId)->first();

        if (!$plu || !$scale) {
            return response()->json(['error' => 'PLU or Scale not found'], 404);
        }

        // In production, this would send PLU data to scale via Node.js bridge
        return response()->json([
            'success' => true,
            'message' => 'PLU sent to scale',
        ]);
    }

    /**
     * Label Templates
     */
    public function labelTemplates()
    {
        $businessId = session('current_business_id');
        $templates = DB::table('scale_label_templates')
            ->where('business_id', $businessId)
            ->get();

        return view('scale.label_templates', compact('templates'));
    }

    public function storeLabelTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'width_mm' => 'required|integer|min:10|max:200',
            'height_mm' => 'required|integer|min:10|max:200',
        ]);

        $businessId = session('current_business_id');

        DB::table('scale_label_templates')->insert([
            'business_id' => $businessId,
            'name' => $request->name,
            'width_mm' => $request->width_mm,
            'height_mm' => $request->height_mm,
            'layout' => json_encode($request->layout ?? []),
            'is_default' => $request->has('is_default'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('scales.label-templates')
            ->with('success', 'Label template created!');
    }
}
