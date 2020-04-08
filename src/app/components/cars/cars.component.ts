import { Component, OnInit, ViewChild } from '@angular/core';
import { AutosService } from '../../services/autos/autos.service';
import { Response } from '../../models/response';

@Component({
  selector: 'app-cars',
  templateUrl: './cars.component.html',
  styleUrls: ['./cars.component.css', '../../global-styles.scss']
})
export class CarsComponent implements OnInit {

  @ViewChild('customModal') customModal;

  autos;
  displayedColumns: string[] = ['Id', 'patente', 'color', 'zona'];
  agregar_auto_modal:boolean = false;
  operation: string;


  constructor(private autosService: AutosService) { }

  ngOnInit() {

    this.autosService.obtenerAutos().subscribe( (response: Response)=>{
      this.autos = response.data;
    });
    

  }

  onCustomModalClose() {
    this.customModal.onClose();
  }

  agregarAuto() {
    this.operation = 'agregar_auto';
    this.customModal.open();
  }

  confirmModal() {
    
  }

}
