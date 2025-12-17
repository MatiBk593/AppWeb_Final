<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;

class ProductController extends Controller
{
    // 1. Ver todos (Público)
    public function index() {
        return DB::table('products')->orderBy('id', 'desc')->get();
    }

    // 2. Ver uno (Público)
    public function show($id) {
        return DB::table('products')->where('id', $id)->first();
    }

    // 3. Crear Producto (SOLO MATIAS)
    public function store(Request $request) {
        // SEGURIDAD: Solo Matias pasa
        if(auth()->user()->email !== 'matias2321160@gmail.com') {
            return response()->json(['message' => 'No autorizado. Solo Matias puede hacer esto.'], 403);
        }

        try {
            $request->validate([
                'name' => 'required',
                'price' => 'required',
                'image' => 'required|file'
            ]);

            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase_credentials.json'));
            
            $storage = $factory->createStorage();
            // ¡OJO! Asegúrate de que aquí siga el nombre de tu bucket que pusiste antes
            $bucket = $storage->getBucket('catalogomatias.appspot.com'); 

            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            
            $object = $bucket->upload(
                fopen($file->getPathname(), 'r'),
                ['name' => 'products/' . $name]
            );

            $url = $object->signedUrl(new \DateTime('2030-01-01'));

            DB::table('products')->insert([
                'name' => $request->name,
                'description' => $request->description ?? 'Sin descripción',
                'price' => $request->price,
                'image_url' => $url,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['message' => 'Producto creado con éxito']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // 4. Eliminar (SOLO MATIAS)
    public function destroy($id) {
        if(auth()->user()->email !== 'matias2321160@gmail.com') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        DB::table('products')->delete($id);
        return response()->json(['message' => 'Eliminado']);
    }

    // 5. Favoritos
    public function addFavorite(Request $r) {
        $exists = DB::table('favorites')->where('user_id', auth()->id())->where('product_id', $r->product_id)->exists();
        if(!$exists) DB::table('favorites')->insert(['user_id'=>auth()->id(), 'product_id'=>$r->product_id]);
        return response()->json(['message' => 'Agregado']);
    }

    public function getFavorites() {
        return DB::table('favorites')
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->where('favorites.user_id', auth()->id())
            ->select('products.*')
            ->get();
    }

    public function removeFavorite($id) {
        DB::table('favorites')->where('user_id', auth()->id())->where('product_id', $id)->delete();
        return response()->json(['message' => 'Borrado']);
    }

    // 6. Comentarios
    public function addComment(Request $r) {
        DB::table('comments')->insert(['user_id'=>auth()->id(), 'product_id'=>$r->product_id, 'content'=>$r->content, 'created_at'=>now()]);
        return response()->json(['message' => 'Comentado']);
    }

    public function getComments($id) {
        return DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->where('product_id', $id)
            ->select('comments.content', 'users.name as user')
            ->get();
    }
}