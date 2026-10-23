<?php

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Models\Article;
use App\Models\CashFlow;
use App\Models\Program;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| RUTE HALAMAN PUBLIK (FRONT-END)
|--------------------------------------------------------------------------
*/

// Halaman Beranda / Welcome
Route::get('/', function () {
    $articles = Article::where('is_published', true)->latest()->take(3)->get();
    $programs = Program::latest()->take(3)->get();
    
    // Caching saldo calculation (5 minutes) to avoid heavy sum queries
    $saldo = Cache::remember('total_saldo', 300, function () {
        $pemasukan = CashFlow::where('type', 'pemasukan')->sum('amount');
        $pengeluaran = CashFlow::where('type', 'pengeluaran')->sum('amount');
        return $pemasukan - $pengeluaran;
    });

    return view('welcome', compact('articles', 'programs', 'saldo'));
});

// Halaman Layanan
Route::get('/layanan', function () {
    return redirect('/#layanan');
});

// Halaman Kegiatan (Kajian & Program)
Route::get('/kegiatan', function () {
    // Gunakan paginate untuk optimalisasi
    $kegiatan = Article::where('is_published', true)->latest()->paginate(9);
    $programs = Program::latest()->paginate(9); 
    return view('kegiatan', compact('kegiatan', 'programs'));
});

// Halaman Detail Program Pembangunan
Route::get('/program/{id}', function ($id) {
    $program = Program::findOrFail($id);
    return view('program_detail', compact('program'));
});

// Halaman Ringkasan Keuangan (Hanya 5 Transaksi Terbaru)
Route::get('/laporan-keuangan', function () {
    $saldo = Cache::remember('total_saldo', 300, function () {
        $pemasukan = CashFlow::where('type', 'pemasukan')->sum('amount');
        $pengeluaran = CashFlow::where('type', 'pengeluaran')->sum('amount');
        return $pemasukan - $pengeluaran;
    });
    // Jika butuh breakdown untuk view
    $pemasukan = CashFlow::where('type', 'pemasukan')->sum('amount');
    $pengeluaran = CashFlow::where('type', 'pengeluaran')->sum('amount');
    
    $transactions = CashFlow::latest()->take(5)->get();
    return view('laporan_keuangan', compact('pemasukan', 'pengeluaran', 'saldo', 'transactions'));
});

// Halaman Buku Besar Keuangan (Seluruh Transaksi)
Route::get('/laporan-keuangan/rincian', function () {
    $saldo = Cache::remember('total_saldo', 300, function () {
        $pemasukan = CashFlow::where('type', 'pemasukan')->sum('amount');
        $pengeluaran = CashFlow::where('type', 'pengeluaran')->sum('amount');
        return $pemasukan - $pengeluaran;
    });
    $pemasukan = CashFlow::where('type', 'pemasukan')->sum('amount');
    $pengeluaran = CashFlow::where('type', 'pengeluaran')->sum('amount');
    
    // Pagination pada rincian transaksi
    $transactions = CashFlow::latest()->paginate(20);
    return view('rincian_keuangan', compact('pemasukan', 'pengeluaran', 'saldo', 'transactions'));
});

// Halaman Donasi (Manual Transfer)
Route::get('/donasi', function () {
    return view('donasi');
});

// Halaman Detail Artikel (Kajian)
Route::get('/artikel/{slug}', function ($slug) {
    $article = Article::where('slug', $slug)->firstOrFail();
    return view('artikel_detail', compact('article'));
})->name('artikel.show');

Route::get('/pengurus', function () { 
    return view('pengurus'); 
});

Route::get('/kontak', function () { 
    return redirect('/#kontak');
});

/*
|--------------------------------------------------------------------------
| RUTE HALAMAN ADMIN / DASHBOARD (MEMBUTUHKAN LOGIN & OBFUSCATED PREFIX)
|--------------------------------------------------------------------------
*/

$adminPrefix = config('app.admin_prefix', env('ADMIN_PREFIX', 'masjid-portal-mgmt'));

Route::middleware(['auth', 'verified'])->group(function () use ($adminPrefix) {
    
    // Dashboard Admin
    Route::get('/dashboard', function () {
        $totalArtikel = Article::count();
        $totalAktivitas = \App\Models\SystemLog::count();
        $saldo = Cache::remember('total_saldo', 300, function () {
            $pemasukan = CashFlow::where('type', 'pemasukan')->sum('amount');
            $pengeluaran = CashFlow::where('type', 'pengeluaran')->sum('amount');
            return $pemasukan - $pengeluaran;
        });

        // Chart Data
        $chartData = [
            'harian' => CashFlow::select(DB::raw('DATE(date) as date'), DB::raw('SUM(CASE WHEN type="pemasukan" THEN amount ELSE 0 END) as income'), DB::raw('SUM(CASE WHEN type="pengeluaran" THEN amount ELSE 0 END) as expense'))->groupBy('date')->orderBy('date', 'desc')->take(7)->get()->reverse()->values(),
            'mingguan' => CashFlow::select(DB::raw('YEARWEEK(date) as week'), DB::raw('SUM(CASE WHEN type="pemasukan" THEN amount ELSE 0 END) as income'), DB::raw('SUM(CASE WHEN type="pengeluaran" THEN amount ELSE 0 END) as expense'))->groupBy('week')->orderBy('week', 'desc')->take(5)->get()->reverse()->values(),
            'tahunan' => CashFlow::select(DB::raw('YEAR(date) as year'), DB::raw('SUM(CASE WHEN type="pemasukan" THEN amount ELSE 0 END) as income'), DB::raw('SUM(CASE WHEN type="pengeluaran" THEN amount ELSE 0 END) as expense'))->groupBy('year')->orderBy('year', 'desc')->take(5)->get()->reverse()->values(),
        ];

        return view('dashboard', compact('totalArtikel', 'saldo', 'totalAktivitas', 'chartData'));
    })->name('dashboard');

    Route::prefix($adminPrefix)->group(function () {
        // --- MANAJEMEN ARTIKEL ---
        Route::get('/artikel', function (Request $request) {
            $query = Article::query();
            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }
            $articles = $query->latest()->paginate(10)->withQueryString()->fragment('tabel-data');
            return view('admin.artikel.index', compact('articles'));
        })->name('admin.artikel.index');

        Route::get('/artikel/create', function () {
            return view('admin.artikel.create');
        })->name('admin.artikel.create');

        Route::post('/artikel', function (Request $request) {
            $request->validate(['title' => 'required|max:255', 'content' => 'required', 'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048']);
            $imagePath = $request->hasFile('image') ? $request->file('image')->store('artikel', 'public') : null;
            Article::create([
                'title' => $request->title, 'slug' => Str::slug($request->title) . '-' . time(),
                'image' => $imagePath, 'content' => $request->content, 'is_published' => $request->has('is_published'),
            ]);
            return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil ditambahkan.');
        })->name('admin.artikel.store');

        Route::get('/artikel/{id}/edit', function ($id) {
            $article = Article::findOrFail($id);
            return view('admin.artikel.edit', compact('article'));
        })->name('admin.artikel.edit');

        Route::put('/artikel/{id}', function (Request $request, $id) {
            $request->validate(['title' => 'required|max:255', 'content' => 'required', 'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048']);
            $article = Article::findOrFail($id);
            $imagePath = $article->image;
            if ($request->hasFile('image')) {
                if ($imagePath) Storage::disk('public')->delete($imagePath);
                $imagePath = $request->file('image')->store('artikel', 'public');
            }
            $article->update(['title' => $request->title, 'image' => $imagePath, 'content' => $request->content, 'is_published' => $request->has('is_published')]);
            return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil diupdate.');
        })->name('admin.artikel.update');

        Route::delete('/artikel/{id}', function ($id) {
            $article = Article::findOrFail($id);
            if ($article->image) Storage::disk('public')->delete($article->image);
            $article->delete();
            \App\Models\SystemLog::log('Hapus Data', 'Menghapus artikel ID: ' . $id);
            return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil dihapus.');
        })->name('admin.artikel.destroy');


        // --- MANAJEMEN PROGRAM ---
        Route::get('/program', function (Request $request) {
            $query = Program::query();
            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }
            $programs = $query->latest()->paginate(10)->withQueryString()->fragment('tabel-data');
            return view('admin.program.index', compact('programs'));
        })->name('admin.program.index');

        Route::get('/program/create', function () {
            return view('admin.program.create');
        })->name('admin.program.create');

        Route::post('/program', function (Request $request) {
            $request->validate(['title' => 'required', 'description' => 'required', 'image' => 'image|mimes:jpeg,png,jpg|max:2048']);
            $data = $request->all();
            if ($request->hasFile('image')) $data['image'] = $request->file('image')->store('programs', 'public');
            Program::create($data);
            return redirect()->route('admin.program.index')->with('success', 'Program berhasil ditambahkan.');
        })->name('admin.program.store');

        Route::get('/program/{id}/edit', function ($id) {
            $program = Program::findOrFail($id);
            return view('admin.program.edit', compact('program'));
        })->name('admin.program.edit');

        Route::put('/program/{id}', function (Request $request, $id) {
            $request->validate(['title' => 'required', 'description' => 'required', 'image' => 'image|mimes:jpeg,png,jpg|max:2048']);
            $program = Program::findOrFail($id);
            $data = $request->all();
            if ($request->hasFile('image')) {
                if ($program->image) Storage::disk('public')->delete($program->image);
                $data['image'] = $request->file('image')->store('programs', 'public');
            }
            $program->update($data);
            return redirect()->route('admin.program.index')->with('success', 'Program berhasil diupdate.');
        })->name('admin.program.update');

        Route::delete('/program/{id}', function ($id) {
            $program = Program::findOrFail($id);
            if ($program->image) Storage::disk('public')->delete($program->image);
            $program->delete();
            \App\Models\SystemLog::log('Hapus Data', 'Menghapus program ID: ' . $id);
            return redirect()->route('admin.program.index')->with('success', 'Program berhasil dihapus.');
        })->name('admin.program.destroy');


        // --- MANAJEMEN KEUANGAN ---
        Route::get('/keuangan', function (Request $request) {
            $query = CashFlow::query();
            if ($request->has('search')) {
                $query->where('description', 'like', '%' . $request->search . '%')
                      ->orWhere('category', 'like', '%' . $request->search . '%');
            }

            // Sorting
            $sort = $request->get('sort', 'date');
            $dir = $request->get('dir', 'desc');
            $query->orderBy($sort, $dir);

            $transactions = $query->paginate(10)->withQueryString()->fragment('tabel-data');
            return view('admin.keuangan.index', compact('transactions', 'sort', 'dir'));
        })->name('admin.keuangan.index');

        Route::get('/keuangan/create', function () {
            return view('admin.keuangan.create');
        })->name('admin.keuangan.create');

        Route::post('/keuangan', function (Request $request) {
            $request->validate(['date' => 'required|date', 'type' => 'required|in:pemasukan,pengeluaran', 'category' => 'required|string|max:255', 'item_details' => 'required|string|max:255', 'amount' => 'required|numeric|min:0']);
            CashFlow::create($request->all());
            Cache::forget('total_saldo'); // Bersihkan cache
            return redirect()->route('admin.keuangan.index')->with('success', 'Data keuangan ditambahkan.');
        })->name('admin.keuangan.store');

        Route::get('/keuangan/{id}/edit', function ($id) {
            $cashflow = CashFlow::findOrFail($id);
            return view('admin.keuangan.edit', compact('cashflow'));
        })->name('admin.keuangan.edit');

        Route::put('/keuangan/{id}', function (Request $request, $id) {
            $request->validate(['date' => 'required|date', 'type' => 'required|in:pemasukan,pengeluaran', 'category' => 'required|string|max:255', 'item_details' => 'required|string|max:255', 'amount' => 'required|numeric|min:0']);
            CashFlow::findOrFail($id)->update($request->all());
            Cache::forget('total_saldo'); // Bersihkan cache
            return redirect()->route('admin.keuangan.index')->with('success', 'Data keuangan diupdate.');
        })->name('admin.keuangan.update');

        Route::delete('/keuangan/{id}', function ($id) {
            CashFlow::findOrFail($id)->delete();
            Cache::forget('total_saldo'); // Bersihkan cache
            \App\Models\SystemLog::log('Hapus Data', 'Menghapus transaksi keuangan ID: ' . $id);
            return redirect()->route('admin.keuangan.index')->with('success', 'Data keuangan dihapus.');
        })->name('admin.keuangan.destroy');
        
        // IMPORT CSV
        Route::post('/keuangan/import', function (Request $request) {
            $request->validate(['file' => 'required|mimes:csv,txt']);
            $file = $request->file('file');
            if ($file) {
                $path = $file->getRealPath();
                $data = array_map('str_getcsv', file($path));
                $header = array_shift($data);
                foreach ($data as $row) {
                    if (count($row) >= 5) { 
                        CashFlow::create([
                            'date' => $row[0] ?? date('Y-m-d'),
                            'type' => $row[1] ?? 'pemasukan',
                            'category' => $row[2] ?? 'Umum',
                            'item_details' => $row[3] ?? '',
                            'amount' => $row[4] ?? 0,
                        ]);
                    }
                }
                Cache::forget('total_saldo');
                \App\Models\SystemLog::log('Import Data', 'Melakukan bulk import CSV Keuangan');
                return redirect()->back()->with('success', 'Data keuangan berhasil diimport.');
            }
            return redirect()->back()->with('error', 'Gagal membaca file.');
        })->name('admin.keuangan.import');

        // --- MANAJEMEN LOG AKTIVITAS ---
        Route::get('/aktivitas', function (Request $request) {
            $query = \App\Models\SystemLog::query();
            if ($request->has('search')) {
                $query->where('action', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%')
                      ->orWhere('ip_address', 'like', '%' . $request->search . '%');
            }
            $logs = $query->latest()->paginate(15)->withQueryString()->fragment('tabel-data');
            return view('admin.aktivitas.index', compact('logs'));
        })->name('admin.aktivitas.index');
    });
});

// Pengaturan Profil (Bawaan Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';