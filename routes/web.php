<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Test;
use Illuminate\Support\Facades\Route;

Route::get('/', [BaseController::class, 'dashbord'])->middleware(['auth', 'verified']);

Route::get('/dashboard', [BaseController::class, 'dashbord'])->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });
Route::get('/symlink', function () {
    return view('symlink');
})->name('generate_symlink');
Route::post('/upload-video', function (Request $request) {
    if ($request->hasFile('video')) {
        $file = $request->file('video');
        $path = $file->store('videos', 'public'); // ou sur S3 selon ta config
        return response()->json(['path' => $path]);
    }
    return response()->json(['error' => 'Fichier manquant'], 400);
});


Route::post('/upload-video-chunk', [Test::class, 'uploadChunk'])->name('video.chunk.upload');
Route::post('/finalize-video-upload', [Test::class, 'finalizeUpload'])->name('video.chunk.finalize');

Route::post('/delete-uploaded-video', function (Request $request) {
    $s3Key = $request->input('s3_key');
    if (Storage::disk('s3')->exists($s3Key)) {
        Storage::disk('s3')->delete($s3Key);
        return response()->json(['deleted' => true]);
    }
    return response()->json(['deleted' => false, 'message' => 'Fichier non trouvé']);
})->name('video.chunk.delete');
// routes/web.php
Route::get('/upload/progress', [Test::class, 'progress'])->name('video.chunk.progress');