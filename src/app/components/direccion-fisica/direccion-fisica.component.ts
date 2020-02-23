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
}
