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

  autos;
  zonas;
  dataToConfirm;
  displayedColumns: string[] = ['Id', 'patente', 'color', 'zona'];
  agregar_auto_modal:boolean = false;
  operation: string;
  durationInSeconds: number = 1;

  constructor(private _snackBar: MatSnackBar, private autosService: AutosService) { }

  ngOnInit() {

    this.autosService.obtenerAutos().subscribe( (response: Response)=>{
      this.autos = response.data;
    });
    

  }

  onCustomModalClose() {
    this.customModal.onClose();
  }

  agregarAuto() {
    this.autosService.obtenerZonas().subscribe( (response: Response)=>{
      this.zonas = response.data;
      console.log(this.zonas);

      this.dataToConfirm = {
        'zonas': this.zonas
      };

    });

    this.operation = 'agregar_auto';
    this.customModal.open();
  }

  confirmModal() {
    console.log(this.dataToConfirm);

    this.autosService.crearAuto(this.dataToConfirm).subscribe( (response: Response) => {
      this.customModal.onClose();
      this._snackBar.openFromComponent(SnackbarComponent, {
        duration: this.durationInSeconds * 1100,
        data: response
      });
    });
  }

}
