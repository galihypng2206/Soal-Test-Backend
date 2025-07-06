<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Epresence;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EpresenceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:masuk,pulang',
            'waktu' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $absensi = Epresence::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'is_approve' => false,
            'waktu' => $request->waktu,
        ]);

        return response()->json([
            'message' => 'Absensi berhasil dicatat',
            'data' => $absensi
        ], 201);
    }


    public function index()
    {
        $user = Auth::user();

        $absensi = $user->epresences()
            ->orderBy('waktu')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->waktu)->toDateString();
            });

        $result = [];

        foreach ($absensi as $tanggal => $records) {
            $masuk = $records->firstWhere('type', 'masuk');
            $pulang = $records->firstWhere('type', 'pulang');

            $result[] = [
                'id_user' => $user->id,
                'nama_user' => $user->name,
                'tanggal' => $tanggal,
                'waktu_masuk' => $masuk ? \Carbon\Carbon::parse($masuk->waktu)->format('H:i:s') : null,
                'waktu_pulang' => $pulang ? \Carbon\Carbon::parse($pulang->waktu)->format('H:i:s') : null,
                'status_masuk' => $masuk ? ($masuk->is_approve ? 'APPROVE' : 'REJECT') : null,
                'status_pulang' => $pulang ? ($pulang->is_approve ? 'APPROVE' : 'REJECT') : null,
            ];
        }

        return response()->json([
            'message' => 'Success get data',
            'data' => $result
        ]);
    }


    public function approve($id)
    {
        $approver = Auth::user();
        $absensi = Epresence::findOrFail($id);

        // Ambil user dari absensi
        $pemilik = $absensi->user;

        // Pastikan hanya supervisor yang boleh meng-approve
        if ($pemilik->npp_supervisor !== $approver->npp) {
            return response()->json([
                'message' => 'Anda bukan supervisor dari user ini.'
            ], 403);
        }

        $absensi->is_approve = true;
        $absensi->save();

        return response()->json([
            'message' => 'Absensi berhasil di-approve.',
            'data' => $absensi
        ]);
    }
}
