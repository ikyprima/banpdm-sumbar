<?php

namespace Modules\Banpdm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Banpdm\Models\Asesor;

class AsesorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('banpdm::index');
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
    public function getDataAsesor(Request $request) {
        if ($request->has('q')) {
            $query = $request->input('q');
            $data = Asesor::where('nama', 'like', '%' . $query . '%')
            ->orWhere('nomor_induk', 'like', '%' . $query . '%')
            ->orWhereHas('wilayah', function ($item) use ($query) {
                $item->where('nama', 'like', '%' . $query . '%');
            })
            ->with(['wilayah'=> function($item){
                return $item->select('kode', 'nama','lat','lng','path');
            }])->get();
            return response()->json($data);
        }else{
            $data = Asesor::with(['wilayah'=> function($item){
                return $item->select('kode', 'nama');
            }])->limit(10)->get();
            return response()->json($data);
        }
    }
}
