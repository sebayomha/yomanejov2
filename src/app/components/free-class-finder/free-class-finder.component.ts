import { Component } from '@angular/core';
import { NgForm } from '@angular/forms';


@Component({
  selector: 'free-class-finder',
  templateUrl: './free-class-finder.component.html',
  styleUrls: ['./free-class-finder.component.scss']
})

export class FreeClassFinderComponent {

    search:Object = {
      lessons: null,
      date: null,
      address: [
        {'street': null, 'diag':null},
        {'street_a': null, 'diag':null},
        {'street_b': null, 'diag':null},
        {'altitud': null}
      ],
      dates_times:[{ 
                    name_day:null,
                    all_day:null,
                    opcion:[]
                  }]
    }

    week = ["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]

    constructor() { }

    guardar(forma:any){
        console.log('Forma',forma);

        console.log('Busqueda',this.search);
    }

    allDay(day,event){

      if (event.checked) {
        this.search.dates_times.push({name_day:day,
          all_day:true,
          opcion:[]
        });
      } else {
        this.search.dates_times.splice(this.search.dates_times.findIndex(element => {return element.name_day == day}), 1);
        console.log('LLAA',this.search.dates_times.splice(this.search.dates_times.findIndex(element => {return element.name_day == day}) , 1));
      }
      
      console.log('Busqueda',this.search);
    }

}
