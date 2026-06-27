<?php

namespace App\Http\Controllers\BarcodeCenter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User;

class BarcodeCenterController extends Controller
{
    /**
     * Dashboard - Superadmin only
     */
    public function index(Request $request)
    {
        $stats = [
            'total_products' => DB::table('barcode_center')->count(),
            'pending_review' => DB::table('tenant_custom_products')->where('status', 'pending_review')->count(),
            'total_tenants' => DB::table('businesses')->count(),
            'total_imports' => DB::table('barcode_center_imports')->count(),
        ];

        $products = DB::table('barcode_center')
            ->leftJoin('barcode_center_categories', 'barcode_center.category_id', '=', 'barcode_center_categories.id')
            ->leftJoin('barcode_center_brands', 'barcode_center.brand_id', '=', 'barcode_center_brands.id')
            ->select('barcode_center.*', 'barcode_center_categories.name as category_name', 'barcode_center_brands.name as brand_name')
            ->latest()
            ->paginate(20);

        return view('barcode-center.index', compact('stats', 'products'));
    }

    /**
     * Add product to Barcode Center
     */
    public function create()
    {
        $categories = DB::table('barcode_center_categories')->where('is_active', true)->get();
        $brands = DB::table('barcode_center_brands')->where('is_active', true)->get();

        return view('barcode-center.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|unique:barcode_center,barcode',
            'barcode_type' => 'required|in:EAN13,EAN8,UPCA,UPCE,CODE128,CODE39',
            'category_id' => 'nullable|exists:barcode_center_categories,id',
            'brand_id' => 'nullable|exists:barcode_center_brands,id',
        ]);

        DB::table('barcode_center')->insert([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'barcode' => $request->barcode,
            'barcode_type' => $request->barcode_type,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'description' => $request->description,
            'weight' => $request->weight,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('barcode-center.index')
            ->with('success', 'Product added to Barcode Center!');
    }

    /**
     * Bulk import
     */
    public function import()
    {
        return view('barcode-center.import');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        $header = array_shift($rows);
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $data = array_combine($header, $row);

            $exists = DB::table('barcode_center')
                ->where('barcode', $data['barcode'] ?? '')
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            DB::table('barcode_center')->insert([
                'name' => $data['name'] ?? '',
                'slug' => \Illuminate\Support\Str::slug($data['name'] ?? ''),
                'barcode' => $data['barcode'] ?? '',
                'barcode_type' => $data['barcode_type'] ?? 'EAN13',
                'category_id' => null,
                'brand_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $imported++;
        }

        return redirect()->route('barcode-center.index')
            ->with('success', "Imported: {$imported} products, Skipped: {$skipped} duplicates");
    }

    /**
     * Tenant view - Search Barcode Center
     */
    public function search(Request $request)
    {
        $businessId = session('current_business_id');
        $query = $request->input('q');

        $products = DB::table('barcode_center')
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    /**
     * Tenant add product from Barcode Center
     */
    public function addToTenant(Request $request, $barcodeCenterId)
    {
        $businessId = session('current_business_id');

        $exists = DB::table('barcode_center_imports')
            ->where('business_id', $businessId)
            ->where('barcode_center_id', $barcodeCenterId)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Product already imported'], 400);
        }

        DB::table('barcode_center_imports')->insert([
            'business_id' => $businessId,
            'barcode_center_id' => $barcodeCenterId,
            'custom_price' => $request->input('custom_price'),
            'initial_stock' => $request->input('initial_stock', 0),
            'imported_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Tenant custom product (not in Barcode Center)
     */
    public function storeCustomProduct(Request $request)
    {
        $businessId = session('current_business_id');

        $productId = DB::table('tenant_custom_products')->insertGetId([
            'business_id' => $businessId,
            'name' => $request->name,
            'barcode' => $request->barcode,
            'category' => $request->category,
            'cost_price' => $request->cost_price,
            'selling_price' => $request->selling_price,
            'quantity' => $request->quantity ?? 0,
            'status' => 'pending_review',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notify superadmin
        DB::table('barcode_center_notifications')->insert([
            'business_id' => $businessId,
            'tenant_custom_product_id' => $productId,
            'notification_type' => 'new_product',
            'message' => "New product '{$request->name}' added by tenant and needs review.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Product submitted for review']);
    }

    /**
     * Superadmin - Review pending products
     */
    public function pendingProducts()
    {
        $products = DB::table('tenant_custom_products')
            ->join('businesses', 'tenant_custom_products.business_id', '=', 'businesses.id')
            ->select('tenant_custom_products.*', 'businesses.name as business_name')
            ->where('tenant_custom_products.status', 'pending_review')
            ->latest()
            ->paginate(20);

        return view('barcode-center.pending', compact('products'));
    }

    public function approveProduct($id)
    {
        // Move to barcode_center
        $product = DB::table('tenant_custom_products')->where('id', $id)->first();

        DB::table('barcode_center')->insert([
            'name' => $product->name,
            'slug' => \Illuminate\Support\Str::slug($product->name),
            'barcode' => $product->barcode,
            'barcode_type' => 'CODE128',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tenant_custom_products')
            ->where('id', $id)
            ->update(['status' => 'approved', 'updated_at' => now()]);

        return redirect()->back()->with('success', 'Product approved and added to Barcode Center!');
    }

    public function rejectProduct($id)
    {
        DB::table('tenant_custom_products')
            ->where('id', $id)
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        return redirect()->back()->with('success', 'Product rejected!');
    }

    /**
     * Category Manager
     */
    public function categories()
    {
        $categories = DB::table('barcode_center_categories')
            ->withCount('products')
            ->latest()
            ->get();

        return view('barcode-center.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        DB::table('barcode_center_categories')->insert([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Category created!');
    }

    /**
     * Brand Manager
     */
    public function brands()
    {
        $brands = DB::table('barcode_center_brands')->latest()->get();

        return view('barcode-center.brands', compact('brands'));
    }

    public function storeBrand(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        DB::table('barcode_center_brands')->insert([
            'name' => $request->name,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Brand created!');
    }

    public function generateBarcode($barcode, $type = 'CODE128')
    {
        $barcode = preg_replace('/[^A-Za-z0-9]/', '', $barcode);

        $svg = match (strtoupper($type)) {
            'EAN13' => $this->generateEAN13($barcode),
            'EAN8' => $this->generateEAN8($barcode),
            'CODE128' => $this->generateCode128($barcode),
            'CODE39' => $this->generateCode39($barcode),
            default => $this->generateCode128($barcode),
        };

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    public function printLabels(Request $request)
    {
        $request->validate([
            'barcodes' => 'required|array',
            'barcodes.*' => 'exists:barcode_center,id',
            'labels_per_row' => 'nullable|integer|min:1|max:5',
            'rows' => 'nullable|integer|min:1|max:20',
        ]);

        $products = DB::table('barcode_center')
            ->whereIn('id', $request->barcodes)
            ->get();

        $labelsPerRow = $request->labels_per_row ?? 3;
        $rows = $request->rows ?? 10;

        return view('barcode-center.print-labels', compact('products', 'labelsPerRow', 'rows'));
    }

    protected function generateCode128(string $code): string
    {
        $startB = chr(104);
        $stop = chr(106);
        $checksum = 104;

        $encoded = '';
        for ($i = 0; $i < strlen($code); $i++) {
            $char = ord($code[$i]) - 32;
            $checksum += $char * ($i + 1);
            $encoded .= chr($char);
        }
        $checksum = $checksum % 103;
        $encoded .= chr($checksum) . $stop;

        $bars = $this->code128Bars($startB . $encoded);

        $width = count($bars);
        $svgBarWidth = 2;
        $svgWidth = $width * $svgBarWidth;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $svgWidth . '" height="80">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        $x = 0;
        foreach ($bars as $bar) {
            if ($bar === '1') {
                $svg .= '<rect x="' . $x . '" y="10" width="' . $svgBarWidth . '" height="50" fill="black"/>';
            }
            $x += $svgBarWidth;
        }

        $svg .= '<text x="' . ($svgWidth / 2) . '" y="72" text-anchor="middle" font-family="monospace" font-size="12">' . $code . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    protected function code128Bars(string $code): array
    {
        $patterns = [
            ' ' => '11011001100', '!' => '11001101100', '"' => '11001100110', '#' => '10010011000',
            '$' => '10010001100', '%' => '10001001100', '&' => '10011001000', "'" => '10011000100',
            '(' => '10001100100', ')' => '11001001000', '*' => '11001000100', '+' => '11000100100',
            ',' => '10110011100', '-' => '10011011100', '.' => '10011001110', '/' => '10111001100',
            '0' => '10011101100', '1' => '10011100110', '2' => '11001110010', '3' => '11001011100',
            '4' => '11001001110', '5' => '11011100100', '6' => '11001110100', '7' => '11101101110',
            '8' => '11101001100', '9' => '11100101100', ':' => '11100100110', ';' => '11101100100',
            '<' => '11100110100', '=' => '11100110010', '>' => '11011011000', '?' => '11011000110',
            '@' => '11000110110', 'A' => '10100011000', 'B' => '10001011000', 'C' => '10001000110',
            'D' => '10110001000', 'E' => '10001101000', 'F' => '10001100010', 'G' => '11010001000',
            'H' => '11000101000', 'I' => '11000100010', 'J' => '10110111000', 'K' => '10110001110',
            'L' => '10001101110', 'M' => '10111011000', 'N' => '10111000110', 'O' => '10001110110',
            'P' => '11101110110', 'Q' => '11010001110', 'R' => '11000101110', 'S' => '11011101000',
            'T' => '11011100010', 'U' => '11011101110', 'V' => '11101011000', 'W' => '11101000110',
            'X' => '11100010110', 'Y' => '11101101000', 'Z' => '11101100010', '[' => '11100011010',
            '\\' => '11101111010', ']' => '11001000010', '^' => '11110001010', '_' => '10100110000',
            '`' => '10100001100', 'a' => '10010110000', 'b' => '10010000110', 'c' => '10000101100',
            'd' => '10000100110', 'e' => '10110010000', 'f' => '10110000100', 'g' => '10011010000',
            'h' => '10011000010', 'i' => '10000110100', 'j' => '10000110010', 'k' => '11000010010',
            'l' => '11001010000', 'm' => '11110111010', 'n' => '11000010100', 'o' => '10001111010',
            'p' => '10100111100', 'q' => '10010111100', 'r' => '10010011110', 's' => '10111100100',
            't' => '10011110100', 'u' => '10011110010', 'v' => '11110100100', 'w' => '11110010100',
            'x' => '11110010010', 'y' => '11011011110', 'z' => '11011110110', '{' => '11110110110',
            '|' => '10101111000', '}' => '10100011110', '~' => '10001011110',
        ];

        $bars = [];
        $code .= chr(106);

        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            if (isset($patterns[$char])) {
                foreach (str_split($patterns[$char]) as $bit) {
                    $bars[] = $bit;
                }
            }
        }

        return $bars;
    }

    protected function generateEAN13(string $code): string
    {
        $code = str_pad(substr($code, 0, 12), 12, '0');
        $checksum = $this->eanChecksum($code);
        $code .= $checksum;

        $patterns = [
            'L' => ['0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011'],
            'G' => ['0100111', '0110011', '0011011', '0100001', '0011101', '0111001', '0000101', '0010001', '0001001', '0010111'],
            'R' => ['1110010', '1100110', '1101100', '1000010', '1011100', '1001110', '1010000', '1000100', '1001000', '1110100'],
        ];

        $parity = ['LLLLLL', 'LLGLGG', 'LLGGLG', 'LLGGGL', 'LGLLGG', 'LGGLLG', 'LGGGLL', 'LGLGLG', 'LGLGGL', 'LGGLGL'];

        $bars = '101';
        $firstDigit = (int) $code[0];
        $parityPattern = $parity[$firstDigit];

        for ($i = 1; $i <= 6; $i++) {
            $digit = (int) $code[$i];
            $patternType = $parityPattern[$i - 1];
            $bars .= $patterns[$patternType][$digit];
        }

        $bars .= '01010';

        for ($i = 7; $i <= 12; $i++) {
            $digit = (int) $code[$i];
            $bars .= $patterns['R'][$digit];
        }

        $bars .= '101';

        $svgBarWidth = 2;
        $svgWidth = strlen($bars) * $svgBarWidth;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $svgWidth . '" height="80">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        $x = 0;
        foreach (str_split($bars) as $bar) {
            if ($bar === '1') {
                $svg .= '<rect x="' . $x . '" y="10" width="' . $svgBarWidth . '" height="50" fill="black"/>';
            }
            $x += $svgBarWidth;
        }

        $svg .= '<text x="' . ($svgWidth / 2) . '" y="72" text-anchor="middle" font-family="monospace" font-size="12">' . $code . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    protected function generateEAN8(string $code): string
    {
        $code = str_pad(substr($code, 0, 7), 7, '0');
        $checksum = $this->eanChecksum($code, 8);
        $code .= $checksum;

        $patterns = [
            'L' => ['0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011'],
            'R' => ['1110010', '1100110', '1101100', '1000010', '1011100', '1001110', '1010000', '1000100', '1001000', '1110100'],
        ];

        $bars = '101';
        for ($i = 0; $i < 4; $i++) {
            $digit = (int) $code[$i];
            $bars .= $patterns['L'][$digit];
        }
        $bars .= '01010';
        for ($i = 4; $i < 8; $i++) {
            $digit = (int) $code[$i];
            $bars .= $patterns['R'][$digit];
        }
        $bars .= '101';

        $svgBarWidth = 2;
        $svgWidth = strlen($bars) * $svgBarWidth;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $svgWidth . '" height="80">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        $x = 0;
        foreach (str_split($bars) as $bar) {
            if ($bar === '1') {
                $svg .= '<rect x="' . $x . '" y="10" width="' . $svgBarWidth . '" height="50" fill="black"/>';
            }
            $x += $svgBarWidth;
        }

        $svg .= '<text x="' . ($svgWidth / 2) . '" y="72" text-anchor="middle" font-family="monospace" font-size="12">' . $code . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    protected function generateCode39(string $code): string
    {
        $patterns = [
            '0' => '101001101101', '1' => '110100101011', '2' => '101100101011', '3' => '110110010101',
            '4' => '101001101011', '5' => '110100110101', '6' => '101100110101', '7' => '101001011011',
            '8' => '110100101101', '9' => '101100101101', 'A' => '110101001011', 'B' => '101101001011',
            'C' => '110110100101', 'D' => '101011001011', 'E' => '110101100101', 'F' => '101101100101',
            'G' => '101010011011', 'H' => '110101001101', 'I' => '101101001101', 'J' => '101011001101',
            'K' => '110101010011', 'L' => '101101010011', 'M' => '110110101001', 'N' => '101011010011',
            'O' => '110101101001', 'P' => '101101101001', 'Q' => '101010110011', 'R' => '110101011001',
            'S' => '101101011001', 'T' => '101011011001', 'U' => '110010101011', 'V' => '100110101011',
            'W' => '110011010101', 'X' => '100101101011', 'Y' => '110010110101', 'Z' => '100110110101',
            '-' => '100101011011', '.' => '110010101101', ' ' => '100110101101', '$' => '100100100101',
            '/' => '100100101001', '+' => '100101001001', '%' => '101001001001', '*' => '100101101101',
        ];

        $code = strtoupper($code);
        $bars = '';

        $wide = '111';
        $narrow = '1';

        foreach (str_split($code) as $char) {
            if (isset($patterns[$char])) {
                $pattern = $patterns[$char];
                foreach (str_split($pattern) as $i => $bit) {
                    $bars .= $bit === '1' ? ($i % 2 === 0 ? $wide : $narrow) : '';
                }
                $bars .= '0';
            }
        }

        $bars = '*' . $code . '*';

        $barPattern = '';
        foreach (str_split($bars) as $char) {
            if (isset($patterns[$char])) {
                $barPattern .= $patterns[$char] . '0';
            }
        }

        $svgBarWidth = 2;
        $svgWidth = strlen($barPattern) * $svgBarWidth;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $svgWidth . '" height="80">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        $x = 0;
        foreach (str_split($barPattern) as $bar) {
            if ($bar === '1') {
                $svg .= '<rect x="' . $x . '" y="10" width="' . $svgBarWidth . '" height="50" fill="black"/>';
            }
            $x += $svgBarWidth;
        }

        $svg .= '<text x="' . ($svgWidth / 2) . '" y="72" text-anchor="middle" font-family="monospace" font-size="12">' . $code . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    protected function eanChecksum(string $code, int $length = 13): string
    {
        $sum = 0;
        for ($i = 0; $i < strlen($code); $i++) {
            $digit = (int) $code[$i];
            $sum += $digit * ($i % 2 === 0 ? 1 : 3);
        }
        return (string) ((10 - ($sum % 10)) % 10);
    }
}
