<?php

namespace Modules\Banpdm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Banpdm\Models\Penugasan;
use Modules\Banpdm\Models\PenugasanDetail;
use Modules\Banpdm\Models\SatuanSekolah;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Validator;
use DB;
use Log;
use Illuminate\Support\MessageBag;
use Redirect;

class PenugasanController extends Controller
{
    private $tahun;
    public function __construct() {
    //tahun sekarang
        $this->tahun = Carbon::now()->year;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $listPenugasan = Penugasan::where('tahun', $this->tahun)->paginate();
        return Inertia::render('banpdm/Penugasan',[
            'listPenugasan' => $listPenugasan
        ]);
    }

    public function create() {
        return Inertia::render('banpdm/PenugasanFormTambah');
    }
    public function simpanPenugasan(Request $request) {
       
        try {
            $rules = [
                'id' => [
                    'required',
                ],
                'tanggal_mulai' => [
                    'required', 
                ],
                'tanggal_akhir' => [
                    'required',
                ],

            ];
            $customMessages = [
                'required' => ':attribute harus di isi.',
                'unique'=> 'field sudah terdaftar',
                'email'=> 'format field salah',
                'size'=> 'jumlah :attribute harus :size digit'
            ];

            $customAttributes = [
                'indikator_kinerja' => 'Indikator Kinerja',
                'detail.*.bulan' => 'Bulan',
                'detail.*.target' => 'Target',
            ];

            $validator = Validator::make($request->all(), $rules, $customMessages, $customAttributes);

            $validator->after(function ($validator) use ($request) {

                $tanggalAwal = Carbon::parse($request->input('tanggal_mulai'))->format('Y-m-d');
                $tanggalAkhir = Carbon::parse($request->input('tanggal_akhir'),)->format('Y-m-d');

                $exists = DB::table('tb_penugasan')
                    ->where('asesor_id', $request->input('id'),)
                    ->where('status', 0) // masih aktif
                    ->where(function ($q) use ($tanggalAwal, $tanggalAkhir) {
                        $q->whereBetween('tanggal_penugasan', [$tanggalAwal, $tanggalAkhir])
                        ->orWhereBetween('tanggal_penugasan_selesai', [$tanggalAwal, $tanggalAkhir])
                        ->orWhere(function ($sub) use ($tanggalAwal, $tanggalAkhir) {
                            $sub->where('tanggal_penugasan', '<=', $tanggalAwal)
                                ->where('tanggal_penugasan_selesai', '>=', $tanggalAkhir);
                        });
                    })
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('tanggal_penugasan', 'Penugasan asesor ini bentrok dengan jadwal lain.');
                }

                // $exists = DB::table('tb_penugasan')
                //     ->where('asesor_id', $request->id_asesor)
                //     ->where('status', 0) // masih aktif
                //     ->where(function ($q) use ($request) {
                //         $q->whereBetween('tanggal_penugasan', [$request->tanggal_awal, $request->tanggal_akhir])
                //         ->orWhereBetween('tanggal_penugasan_selesai', [$request->tanggal_awal, $request->tanggal_akhir])
                //         ->orWhere(function ($sub) use ($request) {
                //             $sub->where('tanggal_penugasan', '<=', $request->tanggal_awal)
                //                 ->where('tanggal_penugasan_selesai', '>=', $request->tanggal_akhir);
                                
                //         });
                //     })
                //     ->exists();

                // if ($exists) {
                //     $validator->errors()->add('tanggal_penugasan', 'Penugasan asesor ini bentrok dengan jadwal lain.');
                // }
            });
            $validator->validate();
            
            DB::beginTransaction();

            $master = Penugasan::create( [
                'asesor_id' => $request->input('id'),
                'tanggal_penugasan' => $request->input('tanggal_mulai'),
                'tanggal_penugasan_selesai' => $request->input('tanggal_akhir'),
                'keterangan' => $request->input('keterangan'),
                'status' => 0,
                'latitude_awal' => $request->input('lat'),
                'longitude_akhir' => $request->input('ltd'),
                'jarak' => $request->input('jarak'),
                'waktu' => $request->input('waktu'),
                'tahun'=> Carbon::now()->year
            ]);

            foreach ($request->input('sekolah') as $key => $value) {
                PenugasanDetail::updateOrCreate([
                    'penugasan_id' => $master->id,
                    'sekolah_id' => $value['id'],
                ]);                 
            }
        
            DB::commit();
            $isInertiaRequest =$request->header('X-Inertia') === 'true';
            if ($isInertiaRequest) {
                return back(303)->with('message', 'Data berhasil disimpan.');
            } else {
                return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan atau ditemukan.'
                ], 200);
            }
        }catch (QueryException $e) {
            DB::rollBack();
            Log::error('Query Error: ' . $e->getMessage());
            $isInertiaRequest =$request->header('X-Inertia') === 'true';
            if ($isInertiaRequest) {
                
                $errors = new MessageBag([
                'pesan'=> 'Terjadi Kesalahan saat menyimpan data (Query Error)',]);
                return Redirect::back()->withErrors($errors);
            } else {
                return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ], 500);
            
            }
        }
    }
    public function dataPenugasan(Request $request) {
        if ($request->has('search')) {
            // $data = FileModel::with('kategori')
            // ->where('description',  'like', "%{$request->search}%")  
            // ->orWhere('title_file',  'like', "%{$request->search}%")
            // ->orWhereHas('kategori', function ($query) use ($request) {
            //     $query->where('name', 'like', "%{$request->search}%");
            // })
            // ->paginate(5)->through(function($data) {
            //     return [
            //         'id' => $data->id,
            //         'title_file' => $data->title_file,
            //         'description' => $data->description,
            //         'size' => $this->formatBytes($data->size),
            //         'filepath' => $data->filepath,
            //         'downloads' => $data->downloads,
            //         'original_name' => $data->original_name,
            //         'kategori' => $data->kategori->name,
            //         'created_at' => $data->created_at,
            //         'updated_at' => $data->updated_at,
            //     ];
            // });
            // $data->appends ( array (
            //     'search' => $request->search
            // ) );
            // return response()->json([
            //     'status'=>true,
            //     'message'=>'Sukses Ambil Data',
            //     'data'=> $data
            // ], 200);
        }else{
            
            $data = Penugasan::with('asesor','detail')->paginate(5)->through(function($data) {
                return $data;
                // return [
                //     'id' => $data->id,
                //     'title_file' => $data->title_file,
                //     'description' => $data->description,
                //     'size' => $this->formatBytes($data->size),
                //     'filepath' => $data->filepath,
                //     'downloads' => $data->downloads,
                //     'original_name' => $data->original_name,
                //     'kategori' => $data->kategori->name,
                //     'created_at' => $data->created_at,
                //     'updated_at' => $data->updated_at,
                // ];
            });
            return response()->json([
                'status'=>true,
                'message'=>'Sukses Ambil Data',
                'data'=> $data
            ], 200);
        }
        
    }
    
}
