<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;

class ProductController extends Controller
{
    // 1. VER TODOS (Versión Segura y Rápida)
    public function index() {
       
        $products = DB::table('products')->orderBy('id', 'desc')->get();
        
        
        foreach($products as $p) {
            $p->comments_count = DB::table('comments')->where('product_id', $p->id)->count();
            $p->favorites_count = DB::table('favorites')->where('product_id', $p->id)->count();
        }

        return $products;
    }

    // 2. VER UNO
    public function show($id) {
        return DB::table('products')->where('id', $id)->first();
    }

    // 3. CREAR PRODUCTO
    public function store(Request $request) {
        
        if(auth()->user()->email !== 'matias2321160@gmail.com') {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        try {
            $request->validate([
                'name' => 'required',
                'price' => 'required',
                'image' => 'required|file',
                'category' => 'required'
            ]);

            $factory = (new Factory)->withServiceAccount(storage_path('app/firebase_credentials.json'));
           
            $bucket = $factory->createStorage()->getBucket('catalogomatias.firebasestorage.app'); 

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
                'category' => $request->category,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['message' => 'Producto creado con éxito']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // 4. ACTUALIZAR (EDICIÓN) - ¡NUEVO!
    public function update(Request $request, $id) {
        // SEGURIDAD
        if(auth()->user()->email !== 'matias2321160@gmail.com') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        try {
            // Preparamos los datos a actualizar
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category' => $request->category,
                'updated_at' => now()
            ];

            
            if ($request->hasFile('image')) {
                $factory = (new Factory)->withServiceAccount(storage_path('app/firebase_credentials.json'));
                $bucket = $factory->createStorage()->getBucket('catalogomatias.firebasestorage.app');
                
                $file = $request->file('image');
                $name = time() . '_' . $file->getClientOriginalName();
                $object = $bucket->upload(fopen($file->getPathname(), 'r'), ['name' => 'products/' . $name]);
                
                // Agregamos la nueva URL a los datos
                $data['image_url'] = $object->signedUrl(new \DateTime('2030-01-01'));
            }

            // Actualizamos en la base de datos
            DB::table('products')->where('id', $id)->update($data);

            return response()->json(['message' => 'Producto actualizado']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // 5. ELIMINAR
    public function destroy($id) {
        if(auth()->user()->email !== 'matias2321160@gmail.com') return response()->json(['message' => 'No autorizado'], 403);
        DB::table('products')->delete($id);
        return response()->json(['message' => 'Eliminado']);
    }

    // 6. FAVORITOS
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

    // 7. COMENTARIOS
    public function addComment(Request $r) {
        DB::table('comments')->insert(['user_id'=>auth()->id(), 'product_id'=>$r->product_id, 'content'=>$r->content, 'created_at'=>now()]);
        return response()->json(['message' => 'Comentado']);
    }

    public function getComments($id) {
        return DB::table('comments')->join('users', 'comments.user_id', '=', 'users.id')->where('product_id', $id)->select('comments.content', 'users.name as user')->get();
    }
}