import { Component, OnInit, Input, ViewChild } from '@angular/core';

@Component({
  selector: 'app-direccion-fisica',
  templateUrl: './direccion-fisica.component.html',
  styleUrls: ['./direccion-fisica.component.css']
})
export class DireccionFisicaComponent implements OnInit {

  @Input() data: any;
  @ViewChild('newDireccionForm') newDireccionForm;

  locations: Array<string> = ["La Plata", "Berisso", "Ensenada"];

  direccion: any = {
    'street': '',
    'diag': false,
    'street_a': '',
    'diag_a': false,
    'street_b': '',
    'diag_b': false,
    'city': 'La Plata',
    'altitud': '',
    'department': '',
    'floor': '',
    'observations': ''
  };

  nuevaDireccion: boolean;
  idDireccionSeleccionada: Number = null;
 
  constructor() { }

  ngOnInit() { }

  ngOnChanges() {
    this.nuevaDireccion = false;
  }

  onAddressSelection(event) {
    if (event.option.selected) {
      event.source.deselectAll();
      event.option._setSelected(true);
      this.idDireccionSeleccionada = event.option.value.idDireccion;
    } else {
      this.idDireccionSeleccionada = null;
      event.source.deselectAll();
    }
    this.nuevaDireccion = false;
  }

  getData() {
    const selectedData = {
      'nuevaDireccion': this.nuevaDireccion,
      'direccion': this.direccion,
      'idDireccionSeleccionada': this.idDireccionSeleccionada
    }

    return selectedData;
  }

  validateForm() {
    if(!this.nuevaDireccion) {
      if (this.idDireccionSeleccionada != null) {
        return true;
      } else {
        return false;
      }
    } else {
      if (this.newDireccionForm.valid) {
        return true;
      } else {
        return false;
      }
    }
  }

  setDireccionFisicaDefault() {
    setTimeout( () => {
      if (this.data.id_DirFisica == this.data.id_DirPrincipal) { //la direccion fisica es la principal
        this.data.addressesAlumno[0].selected = true;
      } else {
        if (this.data.id_DirFisica == this.data.id_DirAlternativa) { //la direccion fisica es la alternativa
          this.data.addressesAlumno[1].selected = true;
        } else { //la direccion fisica es una distinta
          this.nuevaDireccion = true;
          this.direccion.street = this.data.calle_DirFisica;
          this.direccion.diag = (this.data.calle_diag_DirFisica == 'true');
          this.direccion.street_a = this.data.calle_a_DirFisica;
          this.direccion.diag_a = (this.data.calle_a_diag_DirFisica == 'true');
          this.direccion.street_b = this.data.calle_b_DirFisica;
          this.direccion.diag_b = (this.data.calle_b_diag_DirFisica == 'true');
          this.direccion.city = this.data.ciudad_DirFisica;
          this.direccion.altitud = this.data.numero_DirFisica;
          this.direccion.floor = this.data.floor_DirFisica;
          this.direccion.observations = this.data.observaciones_DirFisica;
        }
      }
    },0)
    
  }
}
