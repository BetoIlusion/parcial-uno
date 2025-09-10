<?php

namespace App\Http\Controllers;

use App\Models\Diagrama;

abstract class Controller
{
    public function index(){
        $diagrama = Diagrama::all();
        return view('dashboard1',
        [
            'diagrama' => $diagrama
        ]);
    
    }
}
