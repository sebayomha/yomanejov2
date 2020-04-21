import { Component, OnInit, ViewChild } from '@angular/core';
import { AutosService } from '../../services/autos/autos.service';
import { Response } from '../../models/response';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-cars',
  templateUrl: './cars.component.html',
  styleUrls: ['./cars.component.css', '../../global-styles.scss']
})
export class CarsComponent implements OnInit {

  @ViewChild('customModal') customModal;

  autos = [];
  zonas;
  dataToConfirm;
  displayedColumns: string[] = ['Id', 'patente', 'color', 'zona'];
  agregar_auto_modal:boolean = false;
  buttonAgregar:boolean = false;
  operation: string;
  durationInSeconds: number = 1;
  agregar = 'agregar_auto';
  modificar = 'modificar_auto';
  bajar = 'bajar_auto';

  idAuto;

  showSuccessBanner: boolean = false;

  constructor(private _snackBar: MatSnackBar, private autosService: AutosService) { }

  ngOnInit() {

    this.autosService.obtenerAutos().subscribe( (response: Response)=>{

      response.data.forEach(car => {
        if (car.idAuto) {
          car.cantidadDeClasesDia = 0;
          this.autos.push(car);
        } else if (car.auto) {
          let index = response.data.findIndex(x => x.idAuto === car.auto) 
          this.autos[index].cantidadDeClasesDia = car.cantidadDeClasesDia;
        }
      });
    });
    

  }

  onCustomModalClose($event) {
    this.customModal.onClose();
  }

  operacionesAuto(operacion,auto) {
    this.autosService.obtenerZonas().subscribe( (response: Response)=>{
      this.zonas = response.data;
      console.log(this.zonas);

      if (operacion == 'agregar_auto') {
        this.dataToConfirm = {
          'idDeAuto': '',
          'zonas': this.zonas,
          'dispoDeAuto':'A',
          'descripDeAuto':'',
          'modeloDeAuto' : '',
          'zonaDeAuto' : '',
          'patenteDeAuto' : '',
          'colorDeAuto' : ''
        };
      } else {
        this.dataToConfirm = {
          'idDeAuto': auto.idAuto ? auto.idAuto : '',
          'zonas': this.zonas,
          'dispoDeAuto':'A',
          'descripDeAuto':'',
          'modeloDeAuto' : auto.modelo,
          'zonaDeAuto' : auto.zonaMaster,
          'patenteDeAuto' : auto.patente,
          'colorDeAuto' : auto.color
        };
      }
    });

    this.operation = operacion;
    this.customModal.open();
  }

  confirmModal($event) {
    console.log(this.dataToConfirm);
    switch (this.operation) {
      case 'agregar_auto':
        this.autosService.crearAuto(this.dataToConfirm).subscribe( (response: Response) => {
          this.customModal.onClose();
          this._snackBar.openFromComponent(SnackbarComponent, {
            duration: this.durationInSeconds * 1100,
            data: response
          });
        });
        break;
      case 'modificar_auto':
        this.autosService.modificarAuto(this.dataToConfirm).subscribe( (response: Response) => {
          this.customModal.onClose();
          this._snackBar.openFromComponent(SnackbarComponent, {
            duration: this.durationInSeconds * 1100,
            data: response
          });
        });
        break;
      case 'bajar_auto':
        this.autosService.bajarAuto(this.dataToConfirm).subscribe( (response: Response) => {
          this.customModal.onClose();
          this._snackBar.openFromComponent(SnackbarComponent, {
            duration: this.durationInSeconds * 1100,
            data: response
          });
        });
        break;
    }
    this.ngOnInit();
  }

}
