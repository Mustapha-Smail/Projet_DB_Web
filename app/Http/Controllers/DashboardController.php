<?php

namespace App\Http\Controllers;

use App\Charts\HommesFemmesUsersChart;
use App\Charts\MonthlyUsersChart;
use App\Charts\UsersAgeChart;
use App\Charts\UsersByAgeChart;
use App\Models\Maison;
use App\Models\Appartement;
use App\Models\Locataire;
use App\Models\Proprietaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Khill\Lavacharts\Lavacharts as Lava; 
// use Khill\Lavacharts\Laravel\LavachartsFacade as lavaL

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
        $adresse_fixe = Locataire::where([['user_id', $user->id], ['fixe', true]])->orderBy('updated_at', 'desc')->first(); 
        return view('profile', compact('user', 'adresse_fixe'));
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

    public function admin(
        HommesFemmesUsersChart $chart, 
        UsersByAgeChart $usersbyage
    )
    {
        return view('admin.dashboard', [
            'chart' => $chart->build(),
            'usersbyage' => $usersbyage->build()
        ]); 
    }

    public function usersage(){
        return view('admin.usersage'); 
    }

    public function searchusersage(Request $request, UsersAgeChart $chart){
        // dd($request->date_naissance); 
        $chart = $chart->build($request->date_naissance); 
        return redirect()->route('admin.usersage')->with(['chart' => $chart]); 
    }
}
