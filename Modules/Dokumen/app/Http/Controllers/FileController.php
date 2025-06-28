<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Dokumen\Models\FileModel;
use Storage;
class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('dokumen/File');
    }
    public function dataFile(Request $request) {
          if ($request->has('search')) {
            $data = FileModel::with('kategori')
            ->where('description',  'like', "%{$request->search}%")  
            ->orWhere('title_file',  'like', "%{$request->search}%")
            ->orWhereHas('kategori', function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->paginate(5)->through(function($data) {
                return [
                    'id' => $data->id,
                    'title_file' => $data->title_file,
                    'description' => $data->description,
                    'size' => $this->formatBytes($data->size),
                    'filepath' => $data->filepath,
                    'downloads' => $data->downloads,
                    'original_name' => $data->original_name,
                    'kategori' => $data->kategori->name,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                ];
            });
            $data->appends ( array (
                'search' => $request->search
            ) );
            return response()->json([
                'status'=>true,
                'message'=>'Sukses Ambil Data',
                'data'=> $data
            ], 200);
        }else{
            
            $data = FileModel::with('kategori')->paginate(5)->through(function($data) {
                return [
                    'id' => $data->id,
                    'title_file' => $data->title_file,
                    'description' => $data->description,
                    'size' => $this->formatBytes($data->size),
                    'filepath' => $data->filepath,
                    'downloads' => $data->downloads,
                    'original_name' => $data->original_name,
                    'kategori' => $data->kategori->name,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                ];
            });
            return response()->json([
                'status'=>true,
                'message'=>'Sukses Ambil Data',
                'data'=> $data
            ], 200);
        }
        
    }
    public function previewFile(Request $request){
            
            $pathFile = $request->path_file;
            
            // Pastikan file ada
            if (Storage::disk('minio')->exists($pathFile)) {
                //  return Storage::disk('minio')->response($pathFile);   //
                $url = Storage::disk('minio')
                ->temporaryUrl($pathFile, now()->addMinutes(5));
                $contentType = Storage::disk('minio')->mimeType($pathFile);

                return response()->json([
                        'url' => $url,
                        'content_type' => $contentType,
                    ]); 
                // redirect langsung ke file tanpa download (generate signature url)
                // return redirect($url); 
                
                // response file hanya bisa file lokal
                // return response()->file($url, [
                //     'Content-Type' =>$contentType,
                //     'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                //     'Pragma' => 'no-cache',
                //     'Expires' => '0',
                // ]);

                //solusi jika tetap ingin gunakan stream file / laravel sebagai proxy file
                // $stream = Storage::disk('minio')->readStream($pathFile);

                // return response()->stream(function () use ($stream) {
                //     fpassthru($stream);
                // }, 200, [
                //     'Content-Type' => $contentType,
                //     'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                //     'Pragma' => 'no-cache',
                //     'Expires' => '0',
                // ]);
            }else{
                return response()->json(['error' => 'File not fousnd.'], 404);
            }

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
