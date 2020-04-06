import { Component, OnInit } from '@angular/core';
import { AutosService } from '../../services/autos/autos.service';

@Component({
  selector: 'app-cars',
  templateUrl: './cars.component.html',
  styleUrls: ['./cars.component.css']
})
export class CarsComponent implements OnInit {

  autos;

  constructor(private autosService: AutosService) { }

  ngOnInit() {

    this.autos = this.autosService.obtenerAutos().subscribe( (response)=>{
      console.log(this.autos);  
    });
    

  }

}
