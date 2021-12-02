<?php

namespace App\Http\Controllers;

use App\Models\Maison;
use App\Models\Appartement;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(){
        $user = Auth::user(); 
        $maisons = DB::table('proprietaires')
                            ->join('maisons', 'proprietaires.maison_id', 'maisons.id')
                            ->join('villes', 'maisons.ville_id', 'villes.id')
                            ->select(
                                'maisons.*', 
                                'proprietaires.debut_possession', 
                                'proprietaires.fin_possession', 
                                'villes.nom AS nom_ville',
                                'villes.code_postal'
                            )
                            ->where('proprietaires.user_id', '=', $user->id)
                            ->get(); 

        // dd(gettype($maisons[0]));

        return view('dashboard', compact('user', 'maisons'));
    }

    public function profile(){
        $user = Auth::user(); 
        return view('profile', compact('user'));
    }

    public function appartement($maison_id){
        
        $maison = Maison::whereIn('id', array($maison_id))->first(); 

        if (! Gate::allows('get-appartements', $maison)) {
            abort(403); 
        }

        $appartements = $maison->appartements; 
        // dd($appartements); 


        return view('appartements', compact('appartements')); 
        

    }
    
    public function piece($appartement_id){

        $appartement = Appartement::find($appartement_id);

        if (! Gate::allows('get-pieces', $appartement)) {
            abort(403); 
        }

        $pieces = $appartement->pieces; 

        return view('pieces', compact(
            'pieces'
        )); 
    }
}