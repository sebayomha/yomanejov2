import { Component, OnInit } from '@angular/core';
import { AutosService } from '../../services/autos/autos.service';
import { MatTableDataSource } from '@angular/material/table';

@Component({
  selector: 'app-cars',
  templateUrl: './cars.component.html',
  styleUrls: ['./cars.component.css']
})
export class CarsComponent implements OnInit {

  autos;
  displayedColumns: string[] = ['Id', 'patente', 'color', 'zona'];

  constructor(private autosService: AutosService) { }

  ngOnInit() {

    this.autosService.obtenerAutos().subscribe( (response: Response)=>{
      this.autos = response.data;
    });
    

  }

}
