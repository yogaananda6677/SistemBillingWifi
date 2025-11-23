<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;

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

    /**
     * STORE: Simpan user + sales
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required',
            'no_hp'   => 'required',
            'email'   => 'required|email|unique:users,email',
            'password'=> 'required|min:4',
            'id_area' => 'required',
            'komisi'  => 'nullable|numeric'
        ]);

        // Buat user
        $user = User::create([
            'name'     => $request->name,
            'no_hp'    => $request->no_hp,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role'     => 'sales'
        ]);

        // Buat sales
        Sales::create([
            'user_id' => $user->id,
            'id_area' => $request->id_area,
            'komisi'  => $request->komisi ?? 0
        ]);

        return redirect()->route('data-sales.index')->with('success', 'Sales berhasil ditambahkan');
    }

    /**
     * EDIT: Tampilkan form edit
     */
    public function edit($id)
    {
        $sales = Sales::with('user')->findOrFail($id);
        $area = Area::all();

        return view('sales.edit', compact('sales', 'area'));
    }

    /**
     * UPDATE: Update user + sales
     */
    public function update(Request $request, $id)
    {
        $sales = Sales::with('user')->findOrFail($id);

        $request->validate([
            'name'    => 'required',
            'no_hp'   => 'required',
            'email'   => 'required|email|unique:users,email,' . $sales->user->id,
            'id_area' => 'required',
            'komisi'  => 'nullable|numeric'
        ]);

        // Update user
        $sales->user->update([
            'name'     => $request->name,
            'no_hp'    => $request->no_hp,
            'email'    => $request->email,
            'password' => $request->password
                            ? bcrypt($request->password)
                            : $sales->user->password,
        ]);

        // Update sales
        $sales->update([
            'id_area' => $request->id_area,
            'komisi'  => $request->komisi ?? 0,
        ]);

        return redirect()->route('data-sales.index')->with('success', 'Sales berhasil diperbarui');
    }

    /**
     * DESTROY: Hapus sales + user
     */
    public function destroy($id)
    {
        $sales = Sales::with('user')->findOrFail($id);

        // Hapus sales & user
        $sales->delete();
        $sales->user->delete();

        return redirect()->route('data-sales.index')->with('success', 'Sales berhasil dihapus.');

    }
}
