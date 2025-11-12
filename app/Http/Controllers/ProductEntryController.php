<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductEntryController extends Controller
{
    private $jsonFile = 'entries_product.json';
    private $xmlFile = 'entries_product.xml';

    public function index() {
        return view('products.index');
    }

    public function list() {
        $entries = $this->readEntries();
        $sum = collect($entries)->sum('total_value');
        return response()->json(['entries' => $entries, 'sum_total_value' => $sum]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity_in_stock' => 'required|integer|min:0',
            'price_per_item' => 'required|numeric|min:0'
        ]);

        $entry = [
            'id' => uniqid(),
            'product_name' => $data['product_name'],
            'quantity_in_stock' => (int) $data['quantity_in_stock'],
            'price_per_item' => (float) $data['price_per_item'],
            'datetime_submitted' => now()->toDateTimeString(),
            'total_value' => (int)$data['quantity_in_stock'] * (float)$data['price_per_item'],
        ];

        $entries = $this->readEntries();
        array_unshift($entries, $entry); 

        $this->writeEntries($entries);

        return response()->json(['ok' => true]);
    }

    public function update($id, Request $request) {
        $data = $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity_in_stock' => 'required|integer|min:0',
            'price_per_item' => 'required|numeric|min:0'
        ]);

        $entries = $this->readEntries();
        foreach($entries as &$e) {
            if($e['id'] === $id) {
                $e['product_name'] = $data['product_name'];
                $e['quantity_in_stock'] = (int)$data['quantity_in_stock'];
                $e['price_per_item'] = (float) $data['price_per_item'];
                $e['total_value'] = $e['quantity_in_stock'] * $e['price_per_item'];
            }
        }

        $this->writeEntries($entries);

        return response()->json(['ok' => true]);
    }

    public function destroy($id){
        $entries = $this->readEntries();

        // Filter out the entry with matching id
        $entries = array_filter($entries, fn($e) => $e['id'] !== $id);

        // Reindex array (array_filter preserves keys)
        $entries = array_values($entries);

        $this->writeEntries($entries);

        return response()->json(['ok' => true]);
    }

    private function readEntries() {
        if(!Storage::disk('local')->exists($this->jsonFile)) return [];
        return json_decode(Storage::disk('local')->get($this->jsonFile), true) ?? [];
    }

    private function writeEntries(array $entries) {
        Storage::disk('local')->put($this->jsonFile, json_encode($entries, JSON_PRETTY_PRINT));

        $xml = new \SimpleXMLElement('<entries />');
        foreach ($entries as $item) {
            $node = $xml->addChild('entry');
            foreach ($item as $k => $v) {
                $node->addChild($k, htmlspecialchars((string)$v));
            }
        }

        Storage::disk('local')->put($this->xmlFile, $xml->asXML());
    }
}
