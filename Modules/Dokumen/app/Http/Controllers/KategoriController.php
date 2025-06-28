<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dokumen\Models\KategoriModel;
use Inertia\Inertia;
use Inertia\Response;
use DB;
class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = KategoriModel::paginate(2);
        return Inertia::render('dokumen/Kategori',['data' => $data]);
    }
    public function dataKategori(Request $request)
    {

        if ($request->has('search')) {
            $data = KategoriModel::where('name',  'like', "%{$request->search}%")  
            ->paginate(5);
            $data->appends ( array (
                'search' => $request->search
            ) );
            return response()->json([
                'status'=>true,
                'message'=>'Sukses Ambil Data',
                'data'=> $data
            ], 200);
        }else{
            
            $data = KategoriModel::paginate(5);
            return response()->json([
                'status'=>true,
                'message'=>'Sukses Ambil Data',
                'data'=> $data
            ], 200);
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dokumen::create');
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
        return view('dokumen::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('dokumen::edit');
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
