<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function detail(){
        if(Auth::check()){
            $data = Toko::where('user_id',Auth::user()->id)->first();
            $user = Auth::user();
            if($data){
                $nama = explode(" ",strval(Auth::user()->nama));
                return view('info_toko_admin', compact('nama', 'data'));
            }elseif($user->role == '1'){
                $nama = explode(" ",strval(Auth::user()->nama));
                return view('info_toko_admin', compact('nama'));
            }
            return redirect()->back();
        }
    }
    public function storeToko(Request $request){
        $cektoko = Toko::where('user_id',Auth::user()->id)->first();
        if($cektoko){
            if($request->file('gambar_toko')){
                $gambar = $request->file('gambar_toko');
                $name_picture= time(). "_". $gambar->getClientOriginalName();
                $gambar->storeAs('public/images/toko/',$name_picture);

                Storage::delete('public/images/toko/'.$cektoko->logo_toko);
                Toko::where('user_id',Auth::user()->id)->update([
                    'nama_toko' => $request->nama_toko,
                    'logo_toko' => $name_picture,
                    'kotakabupaten' => $request->kotakabupaten,
                    'alamat' => $request->alamat,
                    'kontak' => $request->kontak,
                    'url_gmaps' => $request->url_gmaps
                ]);
                return response()->json(['data' => $cektoko,'message'=>'Update Succesfully','status' => true]);
            } else {
                Toko::where('user_id',Auth::user()->id)->update([
                    'nama_toko' => $request->nama_toko,
                    'kotakabupaten' => $request->kotakabupaten,
                    'alamat' => $request->alamat,
                    'kontak' => $request->kontak,
                    'url_gmaps' => $request->url_gmaps
                ]);
                return response()->json(['data' => $cektoko,'message'=>'Update Succesfully','status' => true]);
            }
        } else {
            if($request->file('gambar_toko')){
                $gambar = $request->file('gambar_toko');
                $name_picture= time(). "_". $gambar->getClientOriginalName();
                $gambar->storeAs('public/images/toko/',$name_picture);
                Toko::create([
                    'user_id' => Auth::user()->id,
                    'nama_toko' => $request->nama_toko,
                    'logo_toko' => $name_picture,
                    'kotakabupaten' => $request->kotakabupaten,
                    'alamat' => $request->alamat,
                    'kontak' => $request->kontak,
                    'url_gmaps' => $request->url_gmaps,
                    'rating' => 0,
                    'follower' => 0
                ]);
                return response()->json(['data' => $cektoko,'message'=>'Update Succesfully','status' => true]);
            }
            $user = Auth::user();
            if($user->role == '1'){
                $update_status = User::where('id',$user->id)->update([
                    'role' => '2'
                ]);
            }
        }
    }
    public function followUnfollow(Request $request){
        if(Auth::check()){
            $user = Auth::user();
            $cekfollow = Follower::where('user_id',$user->id)->where('toko_id',$request->toko_id)->first();
            if($cekfollow){
                $delete = Follower::where('user_id',$user->id)->where('toko_id',$request->toko_id)->delete();

                $jumlah_followers = Follower::where('toko_id',$request->toko_id)->get();

                $addfollower = Toko::where('id',$request->toko_id)->update([
                    'follower' => $jumlah_followers->count()
                ]);

                return response()->json(['data' => $jumlah_followers->count(),'html'=>'<i class="fas fa-plus"></i> Ikuti','message'=>'Unfollowed','status' => true]);
            }else{
                $addfollower = Follower::create([
                    'user_id' => $user->id,
                    'toko_id' => $request->toko_id
                ]);

                $jumlah_followers = Follower::where('toko_id',$request->toko_id)->get();
                $addfollower = Toko::where('id',$request->toko_id)->update([
                    'follower' => $jumlah_followers->count()
                ]);

                return response()->json(['data' => $jumlah_followers->count(),'html' => '<i class="fas fa-user-check"></i> Mengikuti','message'=>'Followed','status' => true]);
            }
        }else{
            return redirect()->back();
        }
    }

    public function destroyToko($id){
        $data = Toko::where('id', $id)->first();
        if($data){
            $user = Auth::user();
            Storage::delete('public/images/toko/'.$data->logo_toko);
            $delete = Toko::where('id', $id)->delete();
            $update_status = User::where('id',$user->id)->update([
                'role'=>'1',
            ]);
            if($delete && $update_status){
                return response()->json(['message'=>'Delete Succesfully','status' => true]);
            }else{
                return response()->json(['error'=>'Delete Failed','status' => false]);
            }
        }else{
            return response()->json(['error'=>'Data not found!','status' => false]);
        }
    }
}
