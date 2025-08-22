<?php

namespace Modules\Banpdm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Banpdm\Models\SatuanSekolah;
use DB;
use Modules\Banpdm\Models\Wilayah;
class BanpdmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $seluruhwilayah = Wilayah::select('kode','nama','ibukota','path')->where('kode', 'like','13%')
        ->with('satuanSekolah','asesor')    
        ->get();
        $daerah =  DB::table('wilayah_level_1_2')
        ->where('kode', 13)
        ->first();
        
        $seluruhwilayah->map(function ($item) {
            if ($item->kode == 13.05) {
            $raw = $item->path; // string dari DB

            // 1️⃣ Perbaiki key biar valid JSON
            $fixed = preg_replace('/(\w+):/', '"$1":', $raw);

            // 2️⃣ Decode ke array PHP
            $points = json_decode($fixed, true);

            // 3️⃣ Ubah jadi format Leaflet [lat, lng]
            $converted = collect($points)
                ->map(fn($point) => [(float)$point['lat'], (float)$point['lng']])
                ->toArray();
            
             $item->path = $converted;
           }else{
            $item->path = json_decode($item->path);
           }
            
            return $item;
        });
        // return json_decode($daerah->path);
        $satuansekolah = SatuanSekolah::all();
        return Inertia::render('banpdm/Index',[
            "sekolahList"=>$satuansekolah,
            "polygons"=> json_decode($daerah->path),
            "seluruhwilayah"=>$seluruhwilayah
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('banpdm::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('banpdm::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('banpdm::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
