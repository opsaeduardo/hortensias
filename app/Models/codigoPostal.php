<?php

namespace App\Models;

use CodeIgniter\Model;

class codigoPostal extends Model
{

    // Consultar colonias
    public function colonias($cp)
    {
        $listado = $this->db->query("SELECT cp.colonia FROM cp inner join municipios on cp.idmunicipio = municipios.idmunicipio inner join estados on estados.idestado = municipios.idestado WHERE cp.cp = $cp and estados.estado = 'Chiapas'");
        $listadoColonias =  $listado->getResult();

        if ($listadoColonias != null) {
            return $listadoColonias;
        } else {
            return '0';
        }
    }
}
