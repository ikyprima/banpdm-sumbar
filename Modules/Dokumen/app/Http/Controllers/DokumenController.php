<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;
use Inertia\Inertia;
use Inertia\Response;
use DB;

use Modules\Dokumen\Models\FileModel;

class DokumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()  {
    
        //   $files = Storage::disk('minio')->files('File BPKAD');
        //   return $files;
        return Inertia::render('dokumen/Index');
    }

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

    public function insert_dari_db_lama()
    {
        // return view('dokumen::index');
        // $url = Storage::disk('minio')->url('nama_file.jpg');
        // $content = Storage::disk('minio')->get('nama_file.jpg');
        // $url = Storage::disk('minio')->temporaryUrl('Whats_App_Image_2025_06_11_at_12_25_25_99489541_68de81d8eb.jpg', now()->addMinutes(10));
        // return $url;
        $files = Storage::disk('minio')->files('File BPKAD');
        $kategori = DB::connection('dbportal')->table('_category')->get();
        $konten = DB::connection('dbportal')->table('_content')->get()->groupBy('image');
        $data = [];

        foreach ($files as $filePath) {
            
            $info = pathinfo($filePath);
            $datakonten = $konten[$info['basename']]??[];
            // ['title_file','description','name','original_name','filepath','extension','mime_type','size','user_id','categories_id','is_public','downloads'];
            FileModel::firstOrCreate([
                'original_name' => $info['basename'], // nama lengkap file
            ],[
                'categories_id' => $datakonten[0]->category_id??100,
                'title_file' => $datakonten[0]->title??'',
                'description' => $datakonten[0]->content??'',
                'name' => $info['filename'],     // nama tanpa ekstensi
                'extension' => $info['extension'] ?? '',
                'size' => Storage::disk('minio')->size($filePath), // byte
                // 'size_human' => $this->formatBytes(Storage::disk('minio')->size($filePath)), // opsional, versi readable
                'mime_type' => Storage::disk('minio')->mimeType($filePath), // << MIME type di sini
                'downloads' => 0,
                'filepath' => $filePath
                // 'url' =>  Storage::disk('minio')->temporaryUrl($filePath, now()->addMinutes(10)), // bisa dipakai untuk akses via browser
            ]);
        }
        return $data;

        // return Inertia::render('dokumen/Index');
    }
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    /**
     * Show the form for creating a new resource.
     */
    
}
