<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\MediaType;

class MediaTypeController extends Controller
{
    public function showmass()
    {
        return view("media-types.insert-mass");
    }
    public function storemass(Request $r)
    {
        //arreglo de MediaTypes repetidos en db
        $repetidos = [];

        //Reglas de validacion
        $reglas = [
            'media-types' => 'required|mimes:csv,txt'
        ];

        //Crear Validador
        $validador = Validator::make($r->all(), $reglas);

        //validadr
        if ($validador->fails()) {
            //return $validador->errors()->first('media-types');
            //enviar mensaje de error de validacion a la vista
            return redirect('media-types/insert')->withErrors($validador);
        } else {
            //return "tipo validado";

            // trasladaar el archivo a las seccion storage del proyecto
            $r->file("media-types")->storeAs('media-types', $r->file("media-types")->getClientOriginalName());

            //Ruta completa del archivo en Storage
            $ruta = base_path() . '\storage\app\media-types\\' . $r->file('media-types')->getClientOriginalName();

            //Abrir el archivo almacenado para lectura:
            if (($puntero = fopen($ruta, 'r')) !== false) {
                //recorre archivo
                //recorro cada linea del csv: fgetcsv, utilizando  el puntero que representa el archivo
                //variable a contar las veces que se inserten
                $contadora = 0;
                while (($linea = fgetcsv($puntero)) !== false) {

                    //Buscar el merda type en $linea[0]
                    $conteo = MediaType::where('Name', '=', $linea[0])->get()->count();

                    //si no hay registro en mediatype que se llamen igual
                    if ($conteo == 0) {
                        //Crear Objeto MediaType
                        $m = new MediaType();
                        //asigno el nombre del mediatype
                        $m->Name = $linea[0];
                        //grabo en sqlite el nuevo media-type
                        $m->save();
                        //aumentar en 1 el contadora  
                        $contadora++;
                    } else { //hay registros del mediatype
                        //agregar una casilla al areglo repetidos
                        $repetidos[] = $linea[0];
                    }
                }
                //Todo: poner mensaje de grabacion masiva
                //en la vista
                //si hubo repetidos
                if (count($repetidos) == 0) {
                    return redirect('media-types/insert')->with('exito', "Carga masiva de medios realizada,Registros ingresados: $contadora");
                } else {
                    return redirect('media-types/insert')->with('exito', "Carga masiva con las siguientes excepciones:")->with("repetidos", $repetidos);
                }
            }
        }
    }
}
