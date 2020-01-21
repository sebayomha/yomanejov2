import { Component } from '@angular/core';
import { NgForm } from '@angular/forms';



@Component({
  selector: 'free-class-finder',
  templateUrl: './free-class-finder.component.html',
  styleUrls: ['./free-class-finder.component.scss','../../global-styles.scss']
})

export class FreeClassFinderComponent {

    search = {
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
                    option:[{
                      hour_start:null,
                      hour_finish:null
                    }]
                  }]
    }

    week = ["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]

    schedule = ["08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00"]

    constructor() { }

    guardar(forma:any){
        console.log('Forma',forma);

        console.log('Busqueda',this.search);
    }

    allDay(day,event){

      if (event.checked) {
        this.search.dates_times.push({name_day:day,
          all_day:true,
          option:[]
        });
      } else {
        this.search.dates_times.splice(this.search.dates_times.findIndex(element => {return element.name_day == day}), 1);
        console.log('LLAA',this.search.dates_times.splice(this.search.dates_times.findIndex(element => {return element.name_day == day}) , 1));
      }
      
      console.log('Busqueda',this.search);
    }

    addDateTime(day){
      let index = this.search.dates_times.findIndex(element => {return element.name_day == day});
      
      if (index == -1) {
        this.search.dates_times.push({name_day:day,
          all_day:false,
          option:[{ 
            hour_start:'08:00',
            hour_finish:'09:00'
          }]
        });
      } else {
        this.search.dates_times[index].option.push({hour_start:'08:00',hour_finish:'09:00'});
      }
      
    }

}
