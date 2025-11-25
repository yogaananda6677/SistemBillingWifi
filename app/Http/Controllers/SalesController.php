<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;



class SalesController extends Controller
{
    /**
     * INDEX: List sales + AJAX search & pagination
     */
    public function index(Request $request)
    {
        $search = $request->search;

        // Ambil data sales + relasi user & area
        $data = Sales::with(['user', 'area', 'pelanggan'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            })
            ->orderBy('id_sales', 'DESC')
            ->paginate(10);

        // Request AJAX → kirimkan HTML partial
        if ($request->ajax()) {
            $html = view('sales.partials.table_rows', compact('data'))->render();
            $pagination = $data->links()->render();

            return response()->json([
                'html' => $html,
                'pagination' => $pagination
            ]);
        }

        return view('sales.index', compact('data'));
    }

    /**
     * CREATE: Form tambah sales
     */
    public function create()
    {
        $area = Area::all();
        return view('sales.create', compact('area'));
    }


public function store(Request $request)
{
    $validated = $request->validate(
        [
            'name'      => ['required', 'string', 'min:3', 'max:255'],
            'no_hp'     => ['required', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
            'email'     => ['required', 'email:rfc,dns', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:6'],
            'komisi'    => ['nullable', 'numeric', 'min:0'],

            // AREA: array, minimal 1, tiap item wajib, tidak boleh duplikat, dan harus ada di tabel area
            'id_area'   => ['required', 'array', 'min:1'],
            'id_area.*' => ['required', 'distinct', 'exists:area,id_area'],
        ],
        [
            'name.required'     => 'Nama sales wajib diisi.',
            'name.min'          => 'Nama sales minimal 3 karakter.',

            'no_hp.required'    => 'No. HP wajib diisi.',
            'no_hp.regex'       => 'No. HP hanya boleh berisi angka.',
            'no_hp.min'         => 'No. HP minimal 10 digit.',
            'no_hp.max'         => 'No. HP maksimal 15 digit.',

            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah digunakan.',

            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',

            'komisi.numeric'    => 'Komisi harus berupa angka.',
            'komisi.min'        => 'Komisi tidak boleh bernilai negatif.',

            'id_area.required'  => 'Minimal pilih satu area.',
            'id_area.array'     => 'Data area tidak valid.',
            'id_area.min'       => 'Minimal pilih satu area.',

            'id_area.*.required'=> 'Area wajib dipilih.',
            'id_area.*.distinct'=> 'Area tidak boleh dipilih lebih dari satu kali.',
            'id_area.*.exists'  => 'Area yang dipilih tidak ditemukan.',
        ]
    );

    // 1. Buat user
    $user = User::create([
        'name'     => $validated['name'],
        'no_hp'    => $validated['no_hp'],
        'email'    => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role'     => 'sales', // sesuaikan dengan sistem kamu
    ]);

    // 2. Ambil area pertama untuk isi kolom legacy sales.id_area
    $firstAreaId = $validated['id_area'][0] ?? null;

    // 3. Buat record sales
    $sales = Sales::create([
        'user_id' => $user->id,
        'id_area' => $firstAreaId, // tetap isi untuk kompatibilitas lama
        'komisi'  => $validated['komisi'] ?? null,
    ]);

    // 4. Simpan semua area ke pivot many-to-many (area_sales)
    // pastikan di model Sales ada relasi:
    // public function areas() { return $this->belongsToMany(Area::class, 'area_sales', 'id_sales', 'id_area'); }
    $sales->areas()->sync($validated['id_area']);

    return redirect()
        ->route('data-sales.index')
        ->with('success', 'Sales berhasil ditambahkan.');
}


    public function edit($id)
    {
        $sales = Sales::with(['user', 'areas'])->findOrFail($id);
        $area  = Area::all();

        return view('sales.edit', compact('sales', 'area'));
        // sesuaikan path view-nya dengan struktur projekmu
    }

    public function update(Request $request, $id)
    {
        $sales = Sales::with(['user', 'areas'])->findOrFail($id);
        $user  = $sales->user;

        $validated = $request->validate(
            [
                'name'      => ['required', 'string', 'min:3', 'max:255'],
                'no_hp'     => ['required', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
                'email'     => ['required', 'email:rfc,dns', 'unique:users,email,' . $user->id],
                // password boleh kosong saat edit
                'password'  => ['nullable', 'string', 'min:6'],
                'komisi'    => ['nullable', 'numeric', 'min:0'],

                // AREA: array, minimal 1, tiap item wajib, tidak boleh duplikat, dan harus ada di tabel area
                'id_area'   => ['required', 'array', 'min:1'],
                'id_area.*' => ['required', 'distinct', 'exists:area,id_area'],
            ],
            [
                'name.required'     => 'Nama sales wajib diisi.',
                'name.min'          => 'Nama sales minimal 3 karakter.',

                'no_hp.required'    => 'No. HP wajib diisi.',
                'no_hp.regex'       => 'No. HP hanya boleh berisi angka.',
                'no_hp.min'         => 'No. HP minimal 10 digit.',
                'no_hp.max'         => 'No. HP maksimal 15 digit.',

                'email.required'    => 'Email wajib diisi.',
                'email.email'       => 'Format email tidak valid.',
                'email.unique'      => 'Email sudah digunakan.',

                'password.min'      => 'Password minimal 6 karakter.',

                'komisi.numeric'    => 'Komisi harus berupa angka.',
                'komisi.min'        => 'Komisi tidak boleh bernilai negatif.',

                'id_area.required'  => 'Minimal pilih satu area.',
                'id_area.array'     => 'Data area tidak valid.',
                'id_area.min'       => 'Minimal pilih satu area.',

                'id_area.*.required'=> 'Area wajib dipilih.',
                'id_area.*.distinct'=> 'Area tidak boleh dipilih lebih dari satu kali.',
                'id_area.*.exists'  => 'Area yang dipilih tidak ditemukan.',
            ]
        );

        // 1. Update user
        $user->name  = $validated['name'];
        $user->no_hp = $validated['no_hp'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // 2. Ambil area pertama untuk isi kolom legacy sales.id_area
        $firstAreaId = $validated['id_area'][0] ?? null;

        // 3. Update record sales
        $sales->id_area = $firstAreaId; // tetap isi untuk kompatibilitas lama
        $sales->komisi  = $validated['komisi'] ?? null;
        $sales->save();

        // 4. Sync pivot many-to-many
        $sales->areas()->sync($validated['id_area']);

        return redirect()
            ->route('data-sales.index')
            ->with('success', 'Sales berhasil diperbarui.');
    }



    /**
     * DESTROY: Hapus sales + user
     */
    public function destroy($id)
    {
        $sales = Sales::withCount('pelanggan')->with('user')->findOrFail($id);

        // 🔥 CEK: Jika masih punya pelanggan, TIDAK BOLEH DIHAPUS
        if ($sales->pelanggan_count > 0) {
            return redirect()->route('data-sales.index')
                ->with('error', 'Sales ini masih memiliki pelanggan, sehingga tidak dapat dihapus.');
        }

        // Aman → Hapus sales & user
        $sales->delete();
        $sales->user->delete();

        return redirect()->route('data-sales.index')
            ->with('success', 'Sales berhasil dihapus.');
    }


public function getSalesByArea($id_area)
{
    $sales = Sales::with('user')
        ->where(function ($q) use ($id_area) {
            // 1) data lama: pakai kolom id_area di tabel sales
            $q->where('id_area', $id_area)

            // 2) data baru: pakai pivot area_sales
              ->orWhereHas('areas', function ($q2) use ($id_area) {
                  $q2->where('area.id_area', $id_area); // nama tabel: area
              });
        })
        ->get();

    return response()->json($sales);
}


}
